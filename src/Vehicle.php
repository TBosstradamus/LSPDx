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
     *
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
     *
     * @param int $id The ID of the vehicle.
     * @return array|false The vehicle's data or false if not found.
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding vehicle by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the status for a specific vehicle.
     *
     * @param int $vehicleId The ID of the vehicle.
     * @param int $status The new status code.
     * @return bool True on success, false on failure.
     */
    public function updateStatus($vehicleId, $status) {
        try {
            $sql = "UPDATE vehicles SET current_status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating vehicle status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the funk channel for a specific vehicle.
     * @param int $vehicleId
     * @param string $funk
     * @return bool
     */
    public function updateFunkChannel($vehicleId, $funk) {
        try {
            $sql = "UPDATE vehicles SET current_funk = :funk WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':funk', $funk, PDO::PARAM_STR);
            $stmt->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating vehicle funk: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the callsign for a specific vehicle.
     * @param int $vehicleId
     * @param string $callsign
     * @return bool
     */
    public function updateCallsign($vehicleId, $callsign) {
        try {
            $sql = "UPDATE vehicles SET current_callsign = :callsign WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':callsign', $callsign, PDO::PARAM_STR);
            $stmt->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating vehicle callsign: " . $e->getMessage());
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
            $sql = "INSERT INTO vehicles (name, category, capacity, licensePlate, mileage, lastCheckup, nextCheckup)
                    VALUES (:name, :category, :capacity, :licensePlate, :mileage, :lastCheckup, :nextCheckup)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':capacity', $data['capacity'], PDO::PARAM_INT);
            $stmt->bindParam(':licensePlate', $data['licensePlate']);
            $stmt->bindParam(':mileage', $data['mileage'], PDO::PARAM_INT);
            $stmt->bindParam(':lastCheckup', $data['lastCheckup'] ?: null);
            $stmt->bindParam(':nextCheckup', $data['nextCheckup'] ?: null);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
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
                        name = :name,
                        category = :category,
                        capacity = :capacity,
                        licensePlate = :licensePlate,
                        mileage = :mileage,
                        lastCheckup = :lastCheckup,
                        nextCheckup = :nextCheckup
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':capacity', $data['capacity'], PDO::PARAM_INT);
            $stmt->bindParam(':licensePlate', $data['licensePlate']);
            $stmt->bindParam(':mileage', $data['mileage'], PDO::PARAM_INT);
            $stmt->bindParam(':lastCheckup', $data['lastCheckup'] ?: null);
            $stmt->bindParam(':nextCheckup', $data['nextCheckup'] ?: null);

            return $stmt->execute();
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
            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            return false;
        }
    }
}
?>