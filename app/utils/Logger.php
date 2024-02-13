<?php

namespace App\Utils;


class Logger {
    const LOG_FILE = __DIR__ . '/../../logs/server.log';

    // ANSI color codes
    const RED = "\033[31m";
    const RESET = "\033[0m";

    public static function info($message) {
        self::writeLog("INFO: " . $message);
    }

    public static function error($message) {
        self::writeLog("ERROR: " . $message);
    }

    public static function warning($message) {
        self::writeLog("WARNING: " . $message);
    }

    public static function security($message) {
        self::writeLog(self::RED . "[SECURITY] " . $message . self::RESET);
    }

    private static function writeLog($message) {
        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[{$date}] {$message}\n";
        file_put_contents(self::LOG_FILE, $formattedMessage, FILE_APPEND | LOCK_EX);
    }
}
