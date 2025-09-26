<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Sanction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all sanctions from the database, joining with officer names.
     *
     * @return array An array of all sanctions.
     */
    public function getAll() {
        try {
            $sql = "SELECT
                        s.*,
                        o.firstName as officerFirstName,
                        o.lastName as officerLastName,
                        i.firstName as issuerFirstName,
                        i.lastName as issuerLastName
                    FROM sanctions s
                    JOIN officers o ON s.officer_id = o.id
                    JOIN officers i ON s.issued_by_officer_id = i.id
                    ORDER BY s.timestamp DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching sanctions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new sanction in the database.
     *
     * @param array $data The sanction's data from the form.
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        if (empty($data['officer_id']) || empty($data['issued_by_officer_id']) || empty($data['sanctionType']) || empty($data['reason'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO sanctions (officer_id, issued_by_officer_id, sanctionType, reason)
                    VALUES (?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['officer_id'],
                $data['issued_by_officer_id'],
                $data['sanctionType'],
                $data['reason']
            ]);
        } catch (PDOException $e) {
            error_log("Error creating sanction: " . $e->getMessage());
            return false;
        }
    }
}
?>