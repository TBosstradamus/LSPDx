<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Checklist {
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

    public function getAllChecklistsWithOfficerData() {
        try {
            $sql = "SELECT
                        o.id as officer_id, o.firstName, o.lastName, o.rank,
                        c.content,
                        fto.id as fto_id, fto.firstName as ftoFirstName, fto.lastName as ftoLastName
                    FROM officers o
                    LEFT JOIN officer_checklists c ON o.id = c.officer_id
                    LEFT JOIN officers fto ON c.assigned_fto_id = fto.id
                    WHERE o.organization_id = ?
                    ORDER BY o.lastName, o.firstName";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching checklists: " . $e->getMessage());
            return [];
        }
    }

    public function getForOfficer($officerId) {
        try {
            // First, ensure the officer belongs to the current organization
            $officerCheckStmt = $this->db->prepare("SELECT id FROM officers WHERE id = ? AND organization_id = ?");
            $officerCheckStmt->execute([$officerId, $this->organization_id]);
            if ($officerCheckStmt->fetch() === false) {
                return false; // Officer not found in this organization
            }

            $stmt = $this->db->prepare("SELECT * FROM officer_checklists WHERE officer_id = ?");
            $stmt->execute([$officerId]);
            $checklist = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$checklist) {
                $template = $this->getTemplate();
                $sql = "INSERT INTO officer_checklists (officer_id, content) VALUES (?, ?)";
                $createStmt = $this->db->prepare($sql);
                $createStmt->execute([$officerId, $template]);
                $stmt->execute([$officerId]);
                $checklist = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return $checklist;
        } catch (PDOException $e) {
            error_log("Error getting checklist for officer: " . $e->getMessage());
            return false;
        }
    }

    public function getTemplate() {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'checklist_template'");
            $stmt->execute();
            return $stmt->fetchColumn() ?: '';
        } catch (PDOException $e) {
            error_log("Error getting checklist template: " . $e->getMessage());
            return '';
        }
    }

    public function update($officerId, $content, $notes, $ftoId) {
        try {
            // Ensure the officer to be updated is in the correct organization
            $officerCheckStmt = $this->db->prepare("SELECT id FROM officers WHERE id = ? AND organization_id = ?");
            $officerCheckStmt->execute([$officerId, $this->organization_id]);
            if ($officerCheckStmt->fetch() === false) {
                return false;
            }

            $sql = "INSERT INTO officer_checklists (officer_id, content, notes, assigned_fto_id)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE content = VALUES(content), notes = VALUES(notes), assigned_fto_id = VALUES(assigned_fto_id)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$officerId, $content, $notes, $ftoId]);
        } catch (PDOException $e) {
            error_log("Error updating checklist: " . $e->getMessage());
            return false;
        }
    }
}
?>