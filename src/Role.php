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
    public function findById($roleId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ? AND organization_id = ?");
            $stmt->execute([$roleId, $this->organization_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding role by ID: " . $e->getMessage());
            return false;
        }
    }

    public function create($name, $description) {
        try {
            $sql = "INSERT INTO roles (organization_id, name, description) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id, $name, $description]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating role: " . $e->getMessage());
            return false;
        }
    }

    public function update($roleId, $name, $description) {
        try {
            $sql = "UPDATE roles SET name = ?, description = ? WHERE id = ? AND organization_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $description, $roleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error updating role: " . $e->getMessage());
            return false;
        }
    }

    public function getAllPermissions() {
        try {
            // Permissions are global, not per-organization
            $stmt = $this->db->prepare("SELECT * FROM permissions ORDER BY category, name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all permissions: " . $e->getMessage());
            return [];
        }
    }

    public function getPermissionsForRole($roleId) {
        try {
            $sql = "SELECT p.id, p.name FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Return just the permission IDs
        } catch (PDOException $e) {
            error_log("Error fetching permissions for role: " . $e->getMessage());
            return [];
        }
    }

    public function updatePermissionsForRole($roleId, $permissionIds) {
        $this->db->beginTransaction();
        try {
            // First, delete all existing permissions for the role
            $deleteStmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $deleteStmt->execute([$roleId]);

            // Then, insert the new permissions
            if (!empty($permissionIds)) {
                $insertSql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $insertStmt = $this->db->prepare($insertSql);
                foreach ($permissionIds as $permissionId) {
                    $insertStmt->execute([$roleId, $permissionId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating permissions for role: " . $e->getMessage());
            return false;
        }
    }
}
?>