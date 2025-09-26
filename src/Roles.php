<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Roles {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets all available roles.
     * @return array
     */
    public function getAllRoles() {
        try {
            $stmt = $this->db->query("SELECT * FROM roles ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching roles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all roles for a specific user.
     * @param int $officerId
     * @return array
     */
    public function getRolesForUser($officerId) {
        try {
            $sql = "SELECT r.* FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.officer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$officerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user roles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the roles for a specific user.
     * @param int $officerId
     * @param array $roleIds
     * @return bool
     */
    public function updateUserRoles($officerId, $roleIds) {
        $this->db->beginTransaction();
        try {
            // First, delete all existing roles for the user
            $deleteStmt = $this->db->prepare("DELETE FROM user_roles WHERE officer_id = ?");
            $deleteStmt->execute([$officerId]);

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
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating user roles: " . $e->getMessage());
            return false;
        }
    }
}
?>