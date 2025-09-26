<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Vehicle {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all vehicles from the master fleet.
     * @return array An array of all vehicles.
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM vehicles ORDER BY category, name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching vehicles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single vehicle by its ID.
     * @param int $id The ID of the vehicle.
     * @return array|false The vehicle's data or false if not found.
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding vehicle by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new vehicle in the master fleet.
     * @param array $data
     * @return int|false The ID of the new vehicle or false on failure.
     */
    public function create($data) {
        if (empty($data['name']) || empty($data['category']) || empty($data['capacity']) || empty($data['licensePlate'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO vehicles (name, category, capacity, licensePlate, mileage)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['category'],
                $data['capacity'],
                $data['licensePlate'],
                $data['mileage']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating vehicle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a vehicle's master data.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        if (empty($id) || empty($data['name']) || empty($data['category']) || empty($data['capacity']) || empty($data['licensePlate'])) {
            return false;
        }

        try {
            $sql = "UPDATE vehicles SET
                        name = ?, category = ?, capacity = ?, licensePlate = ?, mileage = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['category'],
                $data['capacity'],
                $data['licensePlate'],
                $data['mileage'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a vehicle from the master fleet.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            return false;
        }
    }
}
?>