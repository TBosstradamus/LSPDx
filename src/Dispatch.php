<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Dispatch {
    private $db;
    private $organization_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            die("Fehler: Organisations-ID nicht gefunden. Bitte neu anmelden.");
        }
    }

    public function getState() {
        $allAssignments = $this->getAllAssignments();

        $vehicles = $this->getOnDutyVehiclesWithAssignments($allAssignments);
        $headerRoles = $this->getHeaderAssignments($allAssignments);
        $onActivityOfficers = $this->getOnActivityOfficers($allAssignments);

        $assignedOfficerIds = array_column($allAssignments, 'officer_id');
        $availableOfficers = $this->getAvailableOfficers($assignedOfficerIds);

        return [
            'vehicles' => $vehicles,
            'header_roles' => $headerRoles,
            'on_activity_officers' => $onActivityOfficers,
            'available_officers' => $availableOfficers
        ];
    }

    private function getAllAssignments() {
        $sql = "SELECT da.*, o.id, o.firstName, o.lastName, o.badgeNumber, o.rank
                FROM dispatch_assignments da
                JOIN officers o ON da.officer_id = o.id
                WHERE da.organization_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->organization_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getOnDutyVehiclesWithAssignments($allAssignments) {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE organization_id = ? AND on_duty = TRUE ORDER BY name");
        $stmt->execute([$this->organization_id]);
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $vehicleAssignments = array_filter($allAssignments, function($a) {
            return $a['assignment_type'] === 'vehicle';
        });

        foreach ($vehicles as &$vehicle) {
            $seats = array_fill(0, $vehicle['capacity'], null);
            foreach ($vehicleAssignments as $assignment) {
                if ($assignment['assignment_id'] == $vehicle['id']) {
                    $seats[$assignment['seat_index']] = $assignment;
                }
            }
            $vehicle['seats'] = $seats;
        }
        return $vehicles;
    }

    private function getHeaderAssignments($allAssignments) {
        $allRoles = ['dispatch' => null, 'co-dispatch' => null, 'air1' => null, 'air2' => null];
        $headerAssignments = array_filter($allAssignments, function($a) {
            return $a['assignment_type'] === 'header';
        });

        foreach ($headerAssignments as $assignment) {
            if (isset($allRoles[$assignment['assignment_id']])) {
                $allRoles[$assignment['assignment_id']] = $assignment;
            }
        }
        return $allRoles;
    }

    private function getOnActivityOfficers($allAssignments) {
        return array_filter($allAssignments, function($a) {
            return $a['assignment_type'] === 'activity';
        });
    }

    private function getAvailableOfficers($assignedOfficerIds) {
        $sql = "SELECT DISTINCT o.id, o.firstName, o.lastName, o.badgeNumber, o.rank
                FROM officers o
                JOIN time_tracking tt ON o.id = tt.officer_id
                WHERE o.organization_id = ? AND o.isActive = TRUE AND tt.clockOutTime IS NULL";
        $params = [$this->organization_id];

        if (!empty($assignedOfficerIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedOfficerIds), '?'));
            $sql .= " AND o.id NOT IN ($placeholders)";
            $params = array_merge($params, $assignedOfficerIds);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function performTransactionalAssignment(callable $assignmentLogic) {
        $this->db->beginTransaction();
        try {
            $assignmentLogic();
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Transactional assignment failed: " . $e->getMessage());
            return false;
        }
    }

    private function _unassignOfficer($officerId) {
        // Unassign from the dispatch table
        $this->db->prepare("DELETE FROM dispatch_assignments WHERE organization_id = ? AND officer_id = ?")
             ->execute([$this->organization_id, $officerId]);

        // Update the officer's last assignment timestamp to NOW(), effectively starting their "idle" timer
        $this->db->prepare("UPDATE officers SET last_assignment_time = NOW() WHERE id = ?")
             ->execute([$officerId]);
    }

    public function assignOfficerToVehicle($officerId, $vehicleId, $seatIndex) {
        return $this->performTransactionalAssignment(function() use ($officerId, $vehicleId, $seatIndex) {
            $this->_unassignOfficer($officerId);
            $sql = "INSERT INTO dispatch_assignments (organization_id, officer_id, assignment_type, assignment_id, seat_index) VALUES (?, ?, 'vehicle', ?, ?)";
            $this->db->prepare($sql)->execute([$this->organization_id, $officerId, $vehicleId, $seatIndex]);
        });
    }

    public function assignOfficerToHeader($officerId, $roleName) {
        return $this->performTransactionalAssignment(function() use ($officerId, $roleName) {
            $this->_unassignOfficer($officerId);
            $sql = "INSERT INTO dispatch_assignments (organization_id, officer_id, assignment_type, assignment_id) VALUES (?, ?, 'header', ?)";
            $this->db->prepare($sql)->execute([$this->organization_id, $officerId, $roleName]);
        });
    }

    public function assignOfficerToActivity($officerId, $activityName) {
        return $this->performTransactionalAssignment(function() use ($officerId, $activityName) {
            $this->_unassignOfficer($officerId);
            $sql = "INSERT INTO dispatch_assignments (organization_id, officer_id, assignment_type, assignment_id) VALUES (?, ?, 'activity', ?)";
            $this->db->prepare($sql)->execute([$this->organization_id, $officerId, $activityName]);
        });
    }

    public function unassignOfficerFromAll($officerId) {
        return $this->performTransactionalAssignment(function() use ($officerId) {
            $this->_unassignOfficer($officerId);
        });
    }
}
?>