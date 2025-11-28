<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

/**
 * Centralized logging system
 * 
 * Provides structured logging with levels and context
 */
final class Logger
{
    private const LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4,
    ];

    private static string $logLevel = 'INFO';
    private static ?string $logFile = null;

    /**
     * Initialize logger
     */
    public static function initialize(?string $logFile = null, string $logLevel = 'INFO'): void
    {
        self::$logFile = $logFile ?? self::getDefaultLogFile();
        self::$logLevel = $logLevel;
    }

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Log critical message
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log('CRITICAL', $message, $context);
    }

    /**
     * Log message with level
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        // Check if we should log this level
        if (!self::shouldLog($level)) {
            return;
        }

        $logEntry = self::formatLogEntry($level, $message, $context);
        
        // Write to file if configured
        if (self::$logFile) {
            self::writeToFile($logEntry);
        }

        // Also use error_log for compatibility
        error_log($logEntry);
    }

    /**
     * Check if we should log at this level
     */
    private static function shouldLog(string $level): bool
    {
        $currentLevel = self::LEVELS[self::$logLevel] ?? self::LEVELS['INFO'];
        $messageLevel = self::LEVELS[$level] ?? self::LEVELS['INFO'];
        
        return $messageLevel >= $currentLevel;
    }

    /**
     * Format log entry
     */
    private static function formatLogEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        
        return sprintf(
            '[%s] %s: %s%s',
            $timestamp,
            $level,
            $message,
            $contextStr
        );
    }

    /**
     * Write log entry to file
     */
    private static function writeToFile(string $logEntry): void
    {
        if (!self::$logFile) {
            return;
        }

        // Ensure directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // Append to log file
        @file_put_contents(self::$logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get default log file path
     */
    private static function getDefaultLogFile(): string
    {
        $logDir = defined('BASE_PATH') ? BASE_PATH . '/storage/logs' : __DIR__ . '/../../../storage/logs';
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        return $logDir . '/app-' . date('Y-m-d') . '.log';
    }
}

