<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($username, $password) {
        try {
            $sql = "SELECT u.*, o.organization_id
                    FROM users u
                    LEFT JOIN officers o ON u.officer_id = o.id
                    WHERE u.username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Fetch roles
                $rolesStmt = $this->db->prepare(
                    "SELECT r.name FROM roles r
                     JOIN user_roles ur ON r.id = ur.role_id
                     WHERE ur.officer_id = ?"
                );
                $rolesStmt->execute([$user['officer_id']]);
                $user['roles'] = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

                // Fetch permissions
                $permissionsStmt = $this->db->prepare(
                    "SELECT DISTINCT p.name
                     FROM permissions p
                     JOIN role_permissions rp ON p.id = rp.permission_id
                     JOIN user_roles ur ON rp.role_id = ur.role_id
                     WHERE ur.officer_id = ?"
                );
                $permissionsStmt->execute([$user['officer_id']]);
                $user['permissions'] = $permissionsStmt->fetchAll(PDO::FETCH_COLUMN);

                unset($user['password_hash']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login failed: " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function hasRole($role) {
        return isset($_SESSION['roles']) && is_array($_SESSION['roles']) && in_array($role, $_SESSION['roles']);
    }

    public static function hasPermission($permission) {
        if (self::hasRole('Admin')) {
            return true; // Admins have all permissions
        }
        return isset($_SESSION['permissions']) && is_array($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
    }

    public static function requirePermission($permission) {
        if (!self::hasPermission($permission)) {
            header('Location: index.php?page=dashboard&error=permission_denied');
            exit;
        }
    }
}
?>