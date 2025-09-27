<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Role {
    private $db;
    private $organization_id;

    public function __construct($organization_id) {
        $this->db = Database::getInstance()->getConnection();
        if (empty($organization_id)) {
            throw new InvalidArgumentException("Organization ID must be provided for Role model.");
        }
        $this->organization_id = $organization_id;
    }

    /**
     * Gets all roles for the organization.
     * @return array
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM roles WHERE organization_id = ? ORDER BY name");
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching roles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all roles assigned to a specific officer.
     * @param int $officerId
     * @return array
     */
    public function getRolesForOfficer($officerId) {
        try {
            $sql = "SELECT r.* FROM roles r
                    JOIN user_roles ur ON r.id = ur.role_id
                    WHERE ur.officer_id = ? AND r.organization_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$officerId, $this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching roles for officer: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the roles for a specific officer.
     * @param int $officerId
     * @param array $roleIds
     * @return bool
     */
    public function updateRolesForOfficer($officerId, $roleIds) {
        $this->db->beginTransaction();
        try {
            // First, delete all existing roles for the officer in the organization
            $deleteStmt = $this->db->prepare(
                "DELETE ur FROM user_roles ur
                 JOIN roles r ON ur.role_id = r.id
                 WHERE ur.officer_id = ? AND r.organization_id = ?"
            );
            $deleteStmt->execute([$officerId, $this->organization_id]);

            // Then, insert the new roles
            if (!empty($roleIds)) {
                $insertSql = "INSERT INTO user_roles (officer_id, role_id) VALUES (?, ?)";
                $insertStmt = $this->db->prepare($insertSql);
                foreach ($roleIds as $roleId) {
                    $insertStmt->execute([$officerId, $roleId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating roles for officer: " . $e->getMessage());
            return false;
        }
    }
}
?>