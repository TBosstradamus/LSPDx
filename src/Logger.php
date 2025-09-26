<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Logger {

    /**
     * Logs an event to the database.
     *
     * @param string $eventType The type of event (e.g., 'officer_created', 'login_failed').
     * @param string $details A human-readable description of the event.
     * @param int|null $actorId The ID of the officer who performed the action. Defaults to the current session user.
     * @param array|null $meta Optional JSON metadata.
     */
    public static function log($eventType, $details, $actorId = null, $meta = null) {
        try {
            $db = Database::getInstance()->getConnection();

            // If actorId is not provided, try to get it from the session.
            if ($actorId === null && isset($_SESSION['officer_id'])) {
                $actorId = $_SESSION['officer_id'];
            }

            $sql = "INSERT INTO it_logs (eventType, details, actor_id, meta) VALUES (:eventType, :details, :actor_id, :meta)";
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':eventType', $eventType);
            $stmt->bindValue(':details', $details);
            $stmt->bindValue(':actor_id', $actorId, PDO::PARAM_INT);
            $stmt->bindValue(':meta', $meta ? json_encode($meta) : null);

            $stmt->execute();

        } catch (Exception $e) {
            // Log to the server's error log if the database logging fails.
            error_log("Failed to write to IT log: " . $e->getMessage());
        }
    }

    /**
     * Fetches all logs from the database.
     * @return array
     */
    public static function getAll() {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT l.*, o.firstName, o.lastName
                    FROM it_logs l
                    LEFT JOIN officers o ON l.actor_id = o.id
                    ORDER BY l.timestamp DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching logs: " . $e->getMessage());
            return [];
        }
    }
}
?>