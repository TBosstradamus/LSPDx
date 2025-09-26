<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Vehicle {
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

    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE organization_id = ? ORDER BY category, name");
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching vehicles: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = ? AND organization_id = ?");
            $stmt->execute([$id, $this->organization_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding vehicle by ID: " . $e->getMessage());
            return false;
        }
    }

    public function create($data) {
        if (empty($data['name']) || empty($data['category']) || empty($data['capacity']) || empty($data['licensePlate'])) {
            return false;
        }
        try {
            $sql = "INSERT INTO vehicles (organization_id, name, category, capacity, licensePlate, mileage, lastCheckup, nextCheckup)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->organization_id,
                $data['name'],
                $data['category'],
                $data['capacity'],
                $data['licensePlate'],
                $data['mileage'],
                !empty($data['lastCheckup']) ? $data['lastCheckup'] : null,
                !empty($data['nextCheckup']) ? $data['nextCheckup'] : null
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        if (empty($id) || empty($data['name']) || empty($data['category']) || empty($data['capacity']) || empty($data['licensePlate'])) {
            return false;
        }
        try {
            $sql = "UPDATE vehicles SET
                        name = ?, category = ?, capacity = ?, licensePlate = ?,
                        mileage = ?, lastCheckup = ?, nextCheckup = ?
                    WHERE id = ? AND organization_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['category'],
                $data['capacity'],
                $data['licensePlate'],
                $data['mileage'],
                !empty($data['lastCheckup']) ? $data['lastCheckup'] : null,
                !empty($data['nextCheckup']) ? $data['nextCheckup'] : null,
                $id,
                $this->organization_id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$id, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($vehicleId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET current_status = ? WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$status, $vehicleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle status: " . $e->getMessage());
            return false;
        }
    }

    public function updateFunkChannel($vehicleId, $funk) {
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET current_funk = ? WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$funk, $vehicleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle funk: " . $e->getMessage());
            return false;
        }
    }

    public function updateCallsign($vehicleId, $callsign) {
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET current_callsign = ? WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$callsign, $vehicleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle callsign: " . $e->getMessage());
            return false;
        }
    }

    public function toggleOnDutyStatus($vehicleId) {
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET on_duty = NOT on_duty WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$vehicleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error toggling on-duty status: " . $e->getMessage());
            return false;
        }
    }
}
?>