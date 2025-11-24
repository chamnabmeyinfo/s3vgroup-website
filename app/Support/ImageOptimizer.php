<?php

declare(strict_types=1);

namespace App\Support;

/**
 * ImageOptimizer
 *
 * Aggressively optimizes images by resizing, cropping, and compressing them
 * to ensure fast loading times. Targets file sizes under 1MB while maintaining
 * visual quality suitable for web display.
 */
final class ImageOptimizer
{
    // Target maximum file size (1MB)
    private const TARGET_MAX_SIZE = 1024 * 1024; // 1MB
    
    // Maximum dimensions for different use cases
    private const MAX_WIDTH_PRODUCT = 1200;
    private const MAX_HEIGHT_PRODUCT = 1200;
    private const MAX_WIDTH_HERO = 1920;
    private const MAX_HEIGHT_HERO = 1080;
    
    // Quality settings (aggressive compression for web)
    private const JPEG_QUALITY_START = 85;
    private const JPEG_QUALITY_MIN = 60;
    private const PNG_QUALITY_START = 8; // 0-9, lower = better compression
    private const PNG_QUALITY_MIN = 6;
    private const WEBP_QUALITY_START = 85;
    private const WEBP_QUALITY_MIN = 60;

    /**
     * Optimize an image with smart cropping and aggressive compression.
     * Targets file size under 1MB while maintaining good visual quality.
     *
     * @param string $path      Absolute path to the stored file.
     * @param string $mimeType  Original MIME type detected on upload.
     * @param int    $maxWidth  Maximum width (default: 1200 for products).
     * @param int    $maxHeight Maximum height (default: 1200 for products).
     * @param bool   $crop      Whether to crop to exact dimensions (default: false, maintains aspect ratio).
     * @param int    $targetSize Target maximum file size in bytes (default: 1MB).
     */
    public static function resize(
        string $path, 
        string $mimeType, 
        int $maxWidth = self::MAX_WIDTH_PRODUCT, 
        int $maxHeight = self::MAX_HEIGHT_PRODUCT,
        bool $crop = false,
        int $targetSize = self::TARGET_MAX_SIZE
    ): void {
        if (!extension_loaded('gd')) {
            return;
        }

        // Skip SVG/GIF/unsupported formats
        if (in_array($mimeType, ['image/svg+xml', 'image/gif'], true)) {
            return;
        }

        [$width, $height] = getimagesize($path) ?: [0, 0];
        if ($width === 0 || $height === 0) {
            return;
        }

        $fileSize = filesize($path);
        $needsResize = $width > $maxWidth || $height > $maxHeight;
        $needsCompression = $fileSize > $targetSize;

        // If already optimized, skip
        if (!$needsResize && !$needsCompression) {
            return;
        }

        $src = self::createResource($path, $mimeType);
        if ($src === null) {
            return;
        }

        // Calculate new dimensions
        if ($crop) {
            // Smart center crop: crop to exact dimensions maintaining aspect ratio
            $targetAspect = $maxWidth / $maxHeight;
            $sourceAspect = $width / $height;
            
            if ($sourceAspect > $targetAspect) {
                // Source is wider - crop width
                $cropWidth = (int) round($height * $targetAspect);
                $cropHeight = $height;
                $cropX = (int) round(($width - $cropWidth) / 2);
                $cropY = 0;
            } else {
                // Source is taller - crop height
                $cropWidth = $width;
                $cropHeight = (int) round($width / $targetAspect);
                $cropX = 0;
                $cropY = (int) round(($height - $cropHeight) / 2);
            }
            
            $newWidth = $maxWidth;
            $newHeight = $maxHeight;
        } else {
            // Maintain aspect ratio - fit inside bounding box
            $scale = min($maxWidth / $width, $maxHeight / $height);
            if ($scale > 1) {
                $scale = 1; // Don't upscale
            }
            $newWidth = (int) round($width * $scale);
            $newHeight = (int) round($height * $scale);
            $cropX = 0;
            $cropY = 0;
            $cropWidth = $width;
            $cropHeight = $height;
        }

        // Create destination image
        $dst = imagecreatetruecolor($newWidth, $newHeight);

        // Handle transparency for PNG/WebP
        if (in_array($mimeType, ['image/png', 'image/webp'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
        } else {
            // For JPEG, use white background
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
        }

        // Resample with high-quality resampling
        imagecopyresampled(
            $dst, $src, 
            0, 0, 
            $cropX, $cropY, 
            $newWidth, $newHeight, 
            $cropWidth, $cropHeight
        );

        // Try to achieve target file size with quality adjustment
        $finalPath = $path;
        $finalMimeType = $mimeType;
        
        // Try WebP conversion for better compression (if supported)
        $useWebP = function_exists('imagewebp') && 
                   ($mimeType === 'image/jpeg' || $mimeType === 'image/png');
        
        if ($useWebP && $fileSize > $targetSize) {
            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);
            if ($webpPath !== $path) {
                $finalPath = $webpPath;
                $finalMimeType = 'image/webp';
            }
        }

        // Optimize with quality adjustment to meet target size
        $quality = self::getInitialQuality($finalMimeType);
        $minQuality = self::getMinQuality($finalMimeType);
        $attempts = 0;
        $maxAttempts = 10;

        do {
            self::saveImage($dst, $finalPath, $finalMimeType, $quality);
            
            $newFileSize = file_exists($finalPath) ? filesize($finalPath) : 0;
            
            // If we hit target size or minimum quality, stop
            if ($newFileSize <= $targetSize || $quality <= $minQuality || $attempts >= $maxAttempts) {
                break;
            }
            
            // Reduce quality for next attempt
            $quality = max($minQuality, $quality - 5);
            $attempts++;
        } while ($newFileSize > $targetSize && $quality > $minQuality);

        // If we converted to WebP and original still exists, remove original
        if ($finalPath !== $path && file_exists($finalPath) && file_exists($path)) {
            // Only remove if WebP is significantly smaller
            if (filesize($finalPath) < filesize($path) * 0.9) {
                @unlink($path);
            } else {
                // WebP wasn't better, keep original and remove WebP
                @unlink($finalPath);
                $finalPath = $path;
            }
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Get initial quality setting based on MIME type
     */
    private static function getInitialQuality(string $mimeType): int
    {
        return match ($mimeType) {
            'image/jpeg' => self::JPEG_QUALITY_START,
            'image/png' => self::PNG_QUALITY_START,
            'image/webp' => self::WEBP_QUALITY_START,
            default => 80,
        };
    }

    /**
     * Get minimum quality setting based on MIME type
     */
    private static function getMinQuality(string $mimeType): int
    {
        return match ($mimeType) {
            'image/jpeg' => self::JPEG_QUALITY_MIN,
            'image/png' => self::PNG_QUALITY_MIN,
            'image/webp' => self::WEBP_QUALITY_MIN,
            default => 60,
        };
    }

    /**
     * Save image with appropriate format and quality
     */
    private static function saveImage(\GdImage $image, string $path, string $mimeType, int $quality): void
    {
        switch ($mimeType) {
            case 'image/jpeg':
                // Use progressive JPEG for better perceived performance
                imageinterlace($image, 1);
                imagejpeg($image, $path, $quality);
                break;
                
            case 'image/png':
                // PNG quality is inverse (0-9, where 0 = best compression)
                imagepng($image, $path, $quality);
                break;
                
            case 'image/webp':
                imagewebp($image, $path, $quality);
                break;
        }
    }

    private static function createResource(string $path, string $mimeType): ?\GdImage
    {
        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => null,
        };
    }
}


