<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Email {
    private $db;
    private $organization_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            die("Fehler: Organisations-ID nicht gefunden.");
        }
    }

    public function send($senderId, $recipientIds, $ccIds, $subject, $body) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO emails (organization_id, sender_id, subject, body, status) VALUES (?, ?, ?, ?, 'sent')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->organization_id,
                $senderId,
                $subject,
                $body
            ]);
            $emailId = $this->db->lastInsertId();

            $recipientSql = "INSERT INTO email_recipients (email_id, recipient_id, type) VALUES (?, ?, ?)";
            $recipientStmt = $this->db->prepare($recipientSql);

            $processedRecipients = [];
            foreach ($recipientIds as $recipientId) {
                if (!isset($processedRecipients[$recipientId])) {
                    $recipientStmt->execute([$emailId, $recipientId, 'to']);
                    $processedRecipients[$recipientId] = true;
                }
            }
            foreach ($ccIds as $ccId) {
                if (!isset($processedRecipients[$ccId])) {
                    $recipientStmt->execute([$emailId, $ccId, 'cc']);
                    $processedRecipients[$ccId] = true;
                }
            }

            $this->db->commit();
            return $emailId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error sending email: " . $e->getMessage());
            return false;
        }
    }

    public function getInboxForOfficer($officerId) {
        try {
            $sql = "SELECT
                        e.*,
                        er.is_read,
                        er.is_starred,
                        sender.firstName as senderFirstName,
                        sender.lastName as senderLastName
                    FROM emails e
                    JOIN email_recipients er ON e.id = er.email_id
                    LEFT JOIN officers sender ON e.sender_id = sender.id
                    WHERE er.recipient_id = ? AND e.organization_id = ? AND er.is_deleted = FALSE AND e.status = 'sent'
                    ORDER BY e.timestamp DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$officerId, $this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching inbox: " . $e->getMessage());
            return [];
        }
    }

    public function findById($emailId) {
        try {
            $emailSql = "SELECT e.*, sender.firstName as senderFirstName, sender.lastName as senderLastName
                         FROM emails e
                         LEFT JOIN officers sender ON e.sender_id = sender.id
                         WHERE e.id = ? AND e.organization_id = ?";
            $stmt = $this->db->prepare($emailSql);
            $stmt->execute([$emailId, $this->organization_id]);
            $email = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$email) return false;

            $recipientsSql = "SELECT o.firstName, o.lastName, er.type
                              FROM email_recipients er
                              JOIN officers o ON er.recipient_id = o.id
                              WHERE er.email_id = ?";
            $recipientsStmt = $this->db->prepare($recipientsSql);
            $recipientsStmt->execute([$emailId]);
            $recipients = $recipientsStmt->fetchAll(PDO::FETCH_ASSOC);

            $email['to'] = [];
            $email['cc'] = [];
            foreach ($recipients as $recipient) {
                $fullName = $recipient['firstName'] . ' ' . $recipient['lastName'];
                if ($recipient['type'] === 'to') {
                    $email['to'][] = $fullName;
                } else {
                    $email['cc'][] = $fullName;
                }
            }

            return $email;
        } catch (PDOException $e) {
            error_log("Error finding email by ID: " . $e->getMessage());
            return false;
        }
    }

    public function markAsRead($emailId, $officerId) {
        try {
            // We don't need org_id here as the recipient is unique across orgs
            $sql = "UPDATE email_recipients SET is_read = TRUE WHERE email_id = ? AND recipient_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$emailId, $officerId]);
        } catch (PDOException $e) {
            error_log("Error marking email as read: " . $e->getMessage());
            return false;
        }
    }
}
?>