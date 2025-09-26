<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Roles {
    private $db;
    private $organization_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            // This should not happen for a logged-in user.
        }
    }

    /**
     * Gets all permissions for a specific user. This does not need an org_id check
     * as it's based on the officer_id which is unique.
     * @param int $officerId
     * @return array An array of permission names.
     */
    public function getPermissionsForUser($officerId) {
        try {
            $sql = "SELECT DISTINCT p.name
                    FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    JOIN user_roles ur ON rp.role_id = ur.role_id
                    WHERE ur.officer_id = :officer_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':officer_id' => $officerId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching user permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all available roles for the current organization.
     * @return array
     */
    public function getAllRoles() {
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

    /**
     * Gets all available permissions, grouped by category.
     * @return array
     */
    public function getAllPermissions() {
        try {
            $stmt = $this->db->query("SELECT * FROM permissions ORDER BY category, name");
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Group by category
            $grouped = [];
            foreach ($permissions as $p) {
                $grouped[$p['category']][] = $p;
            }
            return $grouped;
        } catch (PDOException $e) {
            error_log("Error fetching all permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets all permission IDs for a specific role.
     * @param int $roleId
     * @return array
     */
    public function getPermissionIdsForRole($roleId) {
        try {
            $stmt = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching permissions for role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the permissions for a specific role.
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function updateRolePermissions($roleId, $permissionIds) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);

            if (!empty($permissionIds)) {
                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                foreach ($permissionIds as $pId) {
                    $stmt->execute([$roleId, $pId]);
                }
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating role permissions: " . $e->getMessage());
            return false;
        }
    }
}
?>