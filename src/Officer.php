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
     *
     * @return array An array of all officers.
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM officers ORDER BY lastName, firstName");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // In a real app, log this error.
            error_log("Error fetching officers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single officer by their ID.
     *
     * @param int $id The ID of the officer.
     * @return array|false The officer's data or false if not found.
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding officer by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new officer in the database.
     *
     * @param array $data The officer's data from the form.
     * @return int|false The ID of the new officer or false on failure.
     */
    public function create($data) {
        // Basic validation
        if (empty($data['firstName']) || empty($data['lastName']) || empty($data['badgeNumber']) || empty($data['rank'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO officers (firstName, lastName, badgeNumber, phoneNumber, gender, rank, isActive)
                    VALUES (:firstName, :lastName, :badgeNumber, :phoneNumber, :gender, :rank, TRUE)";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':firstName', $data['firstName']);
            $stmt->bindParam(':lastName', $data['lastName']);
            $stmt->bindParam(':badgeNumber', $data['badgeNumber']);
            $stmt->bindParam(':phoneNumber', $data['phoneNumber']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':rank', $data['rank']);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            // Check for duplicate entry on badgeNumber
            if ($e->getCode() == 23000) { // Integrity constraint violation
                // You could set a specific error message here for the user
            }
            error_log("Error creating officer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an officer's data in the database.
     *
     * @param int $id The ID of the officer to update.
     * @param array $data The new data for the officer.
     * @return bool True on success, false on failure.
     */
    public function update($id, $data) {
        // Basic validation
        if (empty($id) || empty($data['firstName']) || empty($data['lastName']) || empty($data['badgeNumber']) || empty($data['rank'])) {
            return false;
        }

        try {
            $sql = "UPDATE officers SET
                        firstName = :firstName,
                        lastName = :lastName,
                        badgeNumber = :badgeNumber,
                        phoneNumber = :phoneNumber,
                        gender = :gender,
                        rank = :rank,
                        isActive = :isActive
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':firstName', $data['firstName']);
            $stmt->bindParam(':lastName', $data['lastName']);
            $stmt->bindParam(':badgeNumber', $data['badgeNumber']);
            $stmt->bindParam(':phoneNumber', $data['phoneNumber']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':rank', $data['rank']);

            // Bind isActive as a boolean
            $isActive = filter_var($data['isActive'], FILTER_VALIDATE_BOOLEAN);
            $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error updating officer: " . $e->getMessage());
            return false;
        }
    }
}
?>