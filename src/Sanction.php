<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Sanction {
    private $db;
    private $organization_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            die("Fehler: Organisations-ID nicht gefunden.");
        }
    }

    /**
     * Fetches all sanctions from the current user's organization.
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
                    WHERE s.organization_id = ?
                    ORDER BY s.timestamp DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching sanctions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Creates a new sanction in the current user's organization.
     * @param array $data The sanction's data from the form.
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        if (empty($data['officer_id']) || empty($data['issued_by_officer_id']) || empty($data['sanctionType']) || empty($data['reason'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO sanctions (organization_id, officer_id, issued_by_officer_id, sanctionType, reason)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->organization_id,
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