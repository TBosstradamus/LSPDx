<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Officer {
    private $db;
    private $organization_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // Store the organization ID from the session upon instantiation
        if (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            // This should not happen for a logged-in user, but as a safeguard:
            die("Fehler: Organisations-ID nicht gefunden. Bitte neu anmelden.");
        }
    }

    /**
     * Fetches all officers from the current user's organization.
     * @return array An array of all officers.
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE organization_id = ? ORDER BY lastName, firstName");
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching officers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single officer by their ID, ensuring they belong to the correct organization.
     * @param int $id The ID of the officer.
     * @return array|false The officer's data or false if not found.
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE id = ? AND organization_id = ?");
            $stmt->execute([$id, $this->organization_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding officer by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new officer in the current user's organization.
     * @param array $data The officer's data from the form.
     * @return int|false The ID of the new officer or false on failure.
     */
    public function create($data) {
        if (empty($data['firstName']) || empty($data['lastName']) || empty($data['badgeNumber']) || empty($data['rank'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO officers (organization_id, firstName, lastName, badgeNumber, phoneNumber, gender, rank, isActive)
                    VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->organization_id,
                $data['firstName'],
                $data['lastName'],
                $data['badgeNumber'],
                $data['phoneNumber'],
                $data['gender'],
                $data['rank']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating officer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an officer's data, ensuring they belong to the correct organization.
     * @param int $id The ID of the officer to update.
     * @param array $data The new data for the officer.
     * @return bool True on success, false on failure.
     */
    public function update($id, $data) {
        if (empty($id) || empty($data['firstName']) || empty($data['lastName']) || empty($data['badgeNumber']) || empty($data['rank'])) {
            return false;
        }

        try {
            $sql = "UPDATE officers SET
                        firstName = ?, lastName = ?, badgeNumber = ?, phoneNumber = ?,
                        gender = ?, rank = ?, isActive = ?, display_name = ?
                    WHERE id = ? AND organization_id = ?";

            $stmt = $this->db->prepare($sql);
            // Use null coalescing operator for display_name
            $displayName = !empty($data['display_name']) ? $data['display_name'] : null;
            return $stmt->execute([
                $data['firstName'],
                $data['lastName'],
                $data['badgeNumber'],
                $data['phoneNumber'],
                $data['gender'],
                $data['rank'],
                filter_var($data['isActive'], FILTER_VALIDATE_BOOLEAN),
                $displayName,
                $id,
                $this->organization_id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating officer: " . $e->getMessage());
            return false;
        }
    }
}
?>