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
            // Prepare a statement to find the user by username
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
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
            // In a real app, log this error.
            // For now, we'll just return false.
            // error_log("Login failed: " . $e->getMessage());
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
     * Useful for creating user accounts manually or via a script.
     *
     * @param string $password The plain-text password.
     * @return string The hashed password.
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Fetches all users with their associated officer details.
     * @return array
     */
    public function getAllUsersWithOfficerDetails() {
        try {
            $sql = "SELECT
                        u.id as user_id,
                        u.username,
                        u.createdAt,
                        o.id as officer_id,
                        o.firstName,
                        o.lastName,
                        o.badgeNumber
                    FROM users u
                    JOIN officers o ON u.officer_id = o.id
                    ORDER BY o.lastName, o.firstName";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Regenerates a password for a given user ID.
     * @param int $userId
     * @return string|false The new plain-text password on success, false on failure.
     */
    public function regeneratePassword($userId) {
        // Generate a new random password
        $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
        $newPasswordHash = self::hashPassword($newPassword);

        try {
            $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password_hash', $newPasswordHash);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $newPassword; // Return the plain-text password
            }
            return false;

        } catch (PDOException $e) {
            error_log("Error regenerating password: " . $e->getMessage());
            return false;
        }
    }
}
?>