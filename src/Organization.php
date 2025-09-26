<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Organization {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets all organizations from the database.
     * @return array
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM organizations ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching organizations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets the current sharing settings between all organizations.
     * @return array
     */
    public function getSharingSettings() {
        try {
            $stmt = $this->db->query("SELECT * FROM organization_sharing");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching sharing settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the sharing settings.
     * @param array $settings An array of settings to update.
     * @return bool
     */
    public function updateSharingSettings($settings) {
        $sql = "INSERT INTO organization_sharing (source_org_id, target_org_id, data_type, can_access)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE can_access = VALUES(can_access)";

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($settings as $setting) {
                $stmt->execute([
                    $setting['source_org_id'],
                    $setting['target_org_id'],
                    $setting['data_type'],
                    $setting['can_access']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating sharing settings: " . $e->getMessage());
            return false;
        }
    }
}
?>