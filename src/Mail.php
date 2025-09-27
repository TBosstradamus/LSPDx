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
        try {
            $sql = "SELECT e.id, e.subject, e.timestamp, GROUP_CONCAT(o.display_name SEPARATOR ', ') as recipients
                    FROM emails e
                    JOIN email_recipients er ON e.id = er.email_id
                    JOIN officers o ON er.recipient_id = o.id
                    WHERE e.sender_id = ? AND e.organization_id = ?
                    GROUP BY e.id
                    ORDER BY e.timestamp DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$officerId, $this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching sent items: " . $e->getMessage());
            return [];
        }
    }

    public function getDraftsForOfficer($officerId) {
        // Placeholder for draft functionality
        return [];
    }

    public function create($senderId, $recipientIds, $subject, $body) {
        if (empty($recipientIds) || empty($subject) || empty($body)) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            // 1. Insert the main email record
            $sql = "INSERT INTO emails (organization_id, sender_id, subject, body) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id, $senderId, $subject, $body]);
            $emailId = $this->db->lastInsertId();

            if (!$emailId) {
                throw new Exception("Failed to get last insert ID for email.");
            }

            // 2. Insert each recipient
            $recipientSql = "INSERT INTO email_recipients (email_id, recipient_id) VALUES (?, ?)";
            $recipientStmt = $this->db->prepare($recipientSql);
            foreach ($recipientIds as $recipientId) {
                $recipientStmt->execute([$emailId, $recipientId]);
            }

            $this->db->commit();
            return $emailId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating email: " . $e->getMessage());
            return false;
        }
    }
}
?>