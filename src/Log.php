<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

class Log {
    private static $logFilePath;

    private static function ensureLogFileExists() {
        self::$logFilePath = BASE_PATH . '/logs/actions.log';
        $logDir = dirname(self::$logFilePath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Adds a new log entry.
     *
     * @param string $eventType The type of event (e.g., 'login', 'officer_created').
     * @param string $details A human-readable description of the event.
     * @param array $meta Optional metadata to store as JSON.
     */
    public static function add($eventType, $details, $meta = []) {
        self::ensureLogFileExists();

        $actor = 'System';
        if (isset($_SESSION['username'])) {
            $actor = $_SESSION['username'];
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event_type' => $eventType,
            'actor' => $actor,
            'details' => $details,
            'meta' => $meta
        ];

        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents(self::$logFilePath, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Retrieves all log entries.
     * @return array
     */
    public static function getAll() {
        self::ensureLogFileExists();
        if (!file_exists(self::$logFilePath)) {
            return [];
        }

        $lines = file(self::$logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_map('json_decode', $lines, array_fill(0, count($lines), true));

        // Return in reverse chronological order
        return array_reverse($logs);
    }
}
?>