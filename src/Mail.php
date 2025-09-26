<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Mail {
    private $db;
    private $organization_id;

    public function __construct($organization_id) {
        $this->db = Database::getInstance()->getConnection();
        if (empty($organization_id)) {
            throw new InvalidArgumentException("Organization ID must be provided for Mail model.");
        }
        $this->organization_id = $organization_id;
    }

    public function getInboxForOfficer($officerId) {
        try {
            $sql = "SELECT
                        e.id,
                        e.subject,
                        e.timestamp,
                        er.is_read,
                        sender.display_name AS sender_name,
                        sender.rank AS sender_rank
                    FROM emails e
                    JOIN email_recipients er ON e.id = er.email_id
                    LEFT JOIN officers sender ON e.sender_id = sender.id
                    WHERE er.recipient_id = ?
                    AND e.organization_id = ?
                    AND er.is_deleted = 0
                    ORDER BY e.timestamp DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$officerId, $this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching inbox: " . $e->getMessage());
            return [];
        }
    }

    public function getSentForOfficer($officerId) {
        // Basic sent items functionality
        try {
            $sql = "SELECT id, subject, timestamp FROM emails WHERE sender_id = ? AND organization_id = ? ORDER BY timestamp DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$officerId, $this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching sent items: " . $e->getMessage());
            return [];
        }
    }
}
?>