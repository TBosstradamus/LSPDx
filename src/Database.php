<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Include the configuration file
require_once __DIR__ . '/../config.php';

class Database {
    // Hold the class instance.
    private static $instance = null;
    private $conn;

    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;

    // The db connection is established in the private constructor.
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // In a real application, you would log this error and show a generic error message.
            // For development, we can show the actual error.
            // IMPORTANT: Do not expose detailed errors in a production environment.
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the single instance of the Database class.
     *
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object.
     *
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    // Prevent cloning and unserialization of the instance
    private function __clone() {}
    public function __wakeup() {}
}
?>