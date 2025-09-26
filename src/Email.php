<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Email {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Sends an email by creating the main email record and recipient records.
     * @param int $senderId
     * @param array $recipientIds
     * @param array $ccIds
     * @param string $subject
     * @param string $body
     * @return int|false The new email's ID or false on failure.
     */
    public function send($senderId, $recipientIds, $ccIds, $subject, $body) {
        $this->db->beginTransaction();
        try {
            // Step 1: Insert the main email record
            $sql = "INSERT INTO emails (sender_id, subject, body, status) VALUES (:sender_id, :subject, :body, 'sent')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':sender_id' => $senderId,
                ':subject' => $subject,
                ':body' => $body
            ]);
            $emailId = $this->db->lastInsertId();

            // Step 2: Insert recipients, ensuring uniqueness
            $recipientSql = "INSERT INTO email_recipients (email_id, recipient_id, type) VALUES (:email_id, :recipient_id, :type)";
            $recipientStmt = $this->db->prepare($recipientSql);

            $processedRecipients = [];

            // Process 'to' recipients
            foreach ($recipientIds as $recipientId) {
                if (!isset($processedRecipients[$recipientId])) {
                    $recipientStmt->execute([
                        ':email_id' => $emailId,
                        ':recipient_id' => $recipientId,
                        ':type' => 'to'
                    ]);
                    $processedRecipients[$recipientId] = true;
                }
            }

            // Process 'cc' recipients
            foreach ($ccIds as $ccId) {
                // Only add if not already a 'to' recipient
                if (!isset($processedRecipients[$ccId])) {
                    $recipientStmt->execute([
                        ':email_id' => $emailId,
                        ':recipient_id' => $ccId,
                        ':type' => 'cc'
                    ]);
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

    /**
     * Gets all emails for an officer's inbox (where they are a 'to' or 'cc' recipient).
     * @param int $officerId
     * @return array
     */
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
                    WHERE er.recipient_id = :officer_id AND er.is_deleted = FALSE AND e.status = 'sent'
                    ORDER BY e.timestamp DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':officer_id' => $officerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error fetching inbox: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets a single email by its ID, including all its recipients.
     * @param int $emailId
     * @return array|false
     */
    public function findById($emailId) {
        try {
            $emailSql = "SELECT e.*, sender.firstName as senderFirstName, sender.lastName as senderLastName
                         FROM emails e
                         LEFT JOIN officers sender ON e.sender_id = sender.id
                         WHERE e.id = :id";
            $stmt = $this->db->prepare($emailSql);
            $stmt->execute([':id' => $emailId]);
            $email = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$email) return false;

            // Get recipients
            $recipientsSql = "SELECT o.firstName, o.lastName, er.type
                              FROM email_recipients er
                              JOIN officers o ON er.recipient_id = o.id
                              WHERE er.email_id = :email_id";
            $recipientsStmt = $this->db->prepare($recipientsSql);
            $recipientsStmt->execute([':email_id' => $emailId]);
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

    /**
     * Marks an email as read for a specific user.
     * @param int $emailId
     * @param int $officerId
     * @return bool
     */
    public function markAsRead($emailId, $officerId) {
        try {
            $sql = "UPDATE email_recipients SET is_read = TRUE WHERE email_id = :email_id AND recipient_id = :officer_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':email_id' => $emailId, ':officer_id' => $officerId]);
        } catch (PDOException $e) {
            error_log("Error marking email as read: " . $e->getMessage());
            return false;
        }
    }
}
?>