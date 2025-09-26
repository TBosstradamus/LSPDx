<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Officer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all officers from the database.
     * @return array An array of all officers.
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM officers ORDER BY lastName, firstName");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching officers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single officer by their ID.
     * @param int $id The ID of the officer.
     * @return array|false The officer's data or false if not found.
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding officer by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new officer.
     * @param array $data The officer's data from the form.
     * @return int|false The ID of the new officer or false on failure.
     */
    public function create($data) {
        if (empty($data['firstName']) || empty($data['lastName']) || empty($data['badgeNumber']) || empty($data['rank'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO officers (firstName, lastName, badgeNumber, phoneNumber, gender, rank, isActive)
                    VALUES (?, ?, ?, ?, ?, ?, TRUE)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
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
     * Updates an officer's data.
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
                        gender = ?, rank = ?, isActive = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['firstName'],
                $data['lastName'],
                $data['badgeNumber'],
                $data['phoneNumber'],
                $data['gender'],
                $data['rank'],
                filter_var($data['isActive'], FILTER_VALIDATE_BOOLEAN),
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating officer: " . $e->getMessage());
            return false;
        }
    }
}
?>