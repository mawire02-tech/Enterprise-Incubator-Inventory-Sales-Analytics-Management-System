<?php

namespace App\Core;

/**
 * File-based Logger
 */
class Logger
{
    private static array $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];

    public static function log(string $level, string $message, array $context = []): void
    {
        $configured = self::$levels[LOG_LEVEL] ?? 1;
        $current    = self::$levels[$level] ?? 0;

        if ($current < $configured) {
            return;
        }

        $date    = date('Y-m-d H:i:s');
        $file    = LOG_PATH . '/' . date('Y-m-d') . '.log';
        $ctx     = $context ? ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';
        $line    = "[{$date}] [{$level}] {$message}{$ctx}" . PHP_EOL;

        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function debug(string $msg, array $ctx = []): void   { self::log('debug',    $msg, $ctx); }
    public static function info(string $msg, array $ctx = []): void    { self::log('info',     $msg, $ctx); }
    public static function warning(string $msg, array $ctx = []): void { self::log('warning',  $msg, $ctx); }
    public static function error(string $msg, array $ctx = []): void   { self::log('error',    $msg, $ctx); }
    public static function critical(string $msg, array $ctx = []): void{ self::log('critical', $msg, $ctx); }

    public static function exception(\Throwable $e): void
    {
        self::error($e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
