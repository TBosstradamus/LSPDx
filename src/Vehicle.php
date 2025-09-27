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

    public function __construct($organization_id) {
        $this->db = Database::getInstance()->getConnection();
        if (empty($organization_id)) {
            throw new InvalidArgumentException("Organization ID must be provided for Vehicle model.");
        }
        $this->organization_id = $organization_id;
    }

    private function checkOrgId() {
        if (!$this->organization_id) {
            throw new Exception("Organization ID is not set for Vehicle model.");
        }
    }

    public function getAll() {
        $this->checkOrgId();
        try {
            $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE organization_id = ? ORDER BY name");
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching vehicles: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        $this->checkOrgId();
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
        $this->checkOrgId();
        if (empty($data['name']) || empty($data['category']) || empty($data['licensePlate'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO vehicles (organization_id, name, category, capacity, licensePlate, mileage)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->organization_id,
                $data['name'],
                $data['category'],
                (int)$data['capacity'],
                $data['licensePlate'],
                (int)$data['mileage']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $this->checkOrgId();
        if (empty($id) || empty($data['name']) || empty($data['category']) || empty($data['licensePlate'])) {
            return false;
        }

        try {
            $sql = "UPDATE vehicles SET
                        name = ?, category = ?, capacity = ?, licensePlate = ?, mileage = ?
                    WHERE id = ? AND organization_id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['category'],
                (int)$data['capacity'],
                $data['licensePlate'],
                (int)$data['mileage'],
                $id,
                $this->organization_id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        $this->checkOrgId();
        try {
            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$id, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function updateFunk($vehicleId, $funk) {
        $this->checkOrgId();
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET current_funk = ? WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$funk, $vehicleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle funk: " . $e->getMessage());
            return false;
        }
    }

    public function updateCallsign($vehicleId, $callsign) {
        $this->checkOrgId();
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET current_callsign = ? WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$callsign, $vehicleId, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error updating vehicle callsign: " . $e->getMessage());
            return false;
        }
    }
}
?>