<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Checklist {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets all officers with their checklist status and assigned FTO.
     * @return array
     */
    public function getAllChecklistsWithOfficerData() {
        try {
            $sql = "SELECT
                        o.id as officer_id, o.firstName, o.lastName, o.rank,
                        c.content,
                        fto.id as fto_id, fto.firstName as ftoFirstName, fto.lastName as ftoLastName
                    FROM officers o
                    LEFT JOIN officer_checklists c ON o.id = c.officer_id
                    LEFT JOIN officers fto ON c.assigned_fto_id = fto.id
                    ORDER BY o.lastName, o.firstName";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching checklists: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets a single checklist for a specific officer.
     * If it doesn't exist, it creates one from the template.
     * @param int $officerId
     * @return array|false
     */
    public function getForOfficer($officerId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM officer_checklists WHERE officer_id = :officer_id");
            $stmt->execute([':officer_id' => $officerId]);
            $checklist = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$checklist) {
                // Checklist does not exist, create one from template
                $template = $this->getTemplate();
                $sql = "INSERT INTO officer_checklists (officer_id, content) VALUES (:officer_id, :content)";
                $createStmt = $this->db->prepare($sql);
                $createStmt->execute([':officer_id' => $officerId, ':content' => $template]);
                // Fetch the newly created checklist
                $stmt->execute([':officer_id' => $officerId]);
                $checklist = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return $checklist;

        } catch (PDOException $e) {
            error_log("Error getting checklist for officer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the global checklist template from the settings table.
     * @return string
     */
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

    /**
     * Updates the content and notes for a specific officer's checklist.
     * @param int $officerId
     * @param string $content
     * @param string $notes
     * @param int $ftoId
     * @return bool
     */
    public function update($officerId, $content, $notes, $ftoId) {
        try {
            $sql = "INSERT INTO officer_checklists (officer_id, content, notes, assigned_fto_id)
                    VALUES (:officer_id, :content, :notes, :assigned_fto_id)
                    ON DUPLICATE KEY UPDATE content = :content, notes = :notes, assigned_fto_id = :assigned_fto_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
            $stmt->bindParam(':assigned_fto_id', $ftoId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating checklist: " . $e->getMessage());
            return false;
        }
    }
}
?>