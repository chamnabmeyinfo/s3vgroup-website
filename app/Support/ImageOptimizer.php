<?php

declare(strict_types=1);

namespace App\Support;

/**
 * ImageOptimizer
 *
 * Reduces oversized uploads by resizing/compressing them so we only store the
 * pixels we actually need for the project. This keeps disk usage low and
 * prevents 50MB images from slowing the site to a crawl.
 */
final class ImageOptimizer
{
    /**
     * Resize an image in place so that it fits inside the requested bounds.
     * Maintains aspect ratio; only processes raster formats supported by GD.
     *
     * @param string $path      Absolute path to the stored file.
     * @param string $mimeType  Original MIME type detected on upload.
     * @param int    $maxWidth  Maximum width we want to keep.
     * @param int    $maxHeight Maximum height we want to keep.
     * @param int    $quality   JPEG/WebP quality (0-100).
     */
    public static function resize(string $path, string $mimeType, int $maxWidth = 1920, int $maxHeight = 1200, int $quality = 80): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        // Skip SVG/GIF/unsupported formats – they are either vector or animated.
        if (in_array($mimeType, ['image/svg+xml', 'image/gif'], true)) {
            return;
        }

        [$width, $height] = getimagesize($path) ?: [0, 0];
        if ($width === 0 || $height === 0) {
            return;
        }

        // Already within bounds? Skip additional processing.
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }

        $src = self::createResource($path, $mimeType);
        if ($src === null) {
            return;
        }

        // Maintain aspect ratio by fitting inside the bounding box.
        $scale = min($maxWidth / $width, $maxHeight / $height);
        if ($scale > 1) {
            $scale = 1;
        }

        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        $dst = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($mimeType, ['image/png', 'image/webp'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($dst, $path, $quality);
                break;
            case 'image/png':
                // PNG quality is inverse (0 = best). Convert 0-100 range.
                $pngQuality = (int) round(9 - (9 * $quality / 100));
                imagepng($dst, $path, $pngQuality);
                break;
            case 'image/webp':
                imagewebp($dst, $path, $quality);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    private static function createResource(string $path, string $mimeType): ?\GdImage
    {
        return match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };
    }
}


