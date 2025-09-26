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

    /**
     * Attempts to log in a user with the given credentials.
     *
     * @param string $username The username to check.
     * @param string $password The password to check.
     * @return array|false The user's data on success, false on failure.
     */
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

            // Check if user exists and if the password is correct
            if ($user && password_verify($password, $user['password_hash'])) {
                // Password is correct. Return user data (without the password hash).
                unset($user['password_hash']);
                return $user;
            }

            // If user not found or password incorrect
            return false;

        } catch (PDOException $e) {
            error_log("Login failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a user is currently logged in.
     *
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Logs the current user out.
     */
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

    /**
     * A utility function to hash a password.
     *
     * @param string $password The plain-text password.
     * @return string The hashed password.
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>