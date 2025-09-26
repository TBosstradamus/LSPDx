<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Settings {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets a specific setting from the database.
     * @param string $key The key of the setting (e.g., 'callsign_data').
     * @return string|false The value of the setting or false if not found.
     */
    public function getSetting($key) {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
            $stmt->execute([':key' => $key]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting setting '{$key}': " . $e->getMessage());
            return false;
        }
    }
}
?>