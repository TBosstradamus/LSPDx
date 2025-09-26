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

    // Allow creating the model with a specific org_id, or use the session's default.
    public function __construct($organization_id = null) {
        $this->db = Database::getInstance()->getConnection();
        if ($organization_id) {
            $this->organization_id = $organization_id;
        } elseif (isset($_SESSION['organization_id'])) {
            $this->organization_id = $_SESSION['organization_id'];
        } else {
            // Don't die, but log an error. The calling code should handle this.
            error_log("Officer class instantiated without organization_id.");
            // We can't throw an exception here as it might break pages that don't need org context yet.
            // This is a transitional state.
        }
    }

    private function checkOrgId() {
        if (!$this->organization_id) {
            throw new Exception("Organization ID is not set for Officer model.");
        }
    }

    public function getAll() {
        $this->checkOrgId();
        try {
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE organization_id = ? ORDER BY lastName, firstName");
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching officers: " . $e->getMessage());
            return [];
        }
    }

    // findById should NOT be restricted by organization, as we might need to look up users across orgs (e.g. for system admins)
    // However, for most operations within a tenant, we need to find by ID *and* org_id.
    public function findById($id) {
        try {
            // This now returns the organization_id, which is crucial.
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding officer by ID: " . $e->getMessage());
            return false;
        }
    }

    // A secure version of findById for tenant-specific operations.
    public function findByIdInOrg($id) {
        $this->checkOrgId();
         try {
            $stmt = $this->db->prepare("SELECT * FROM officers WHERE id = ? AND organization_id = ?");
            $stmt->execute([$id, $this->organization_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding officer by ID in org: " . $e->getMessage());
            return false;
        }
    }


    public function create($data) {
        $this->checkOrgId();
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

    public function update($id, $data) {
        $this->checkOrgId();
        if (empty($id) || empty($data['firstName']) || empty($data['lastName']) || empty($data['badgeNumber']) || empty($data['rank'])) {
            return false;
        }

        try {
            $sql = "UPDATE officers SET
                        firstName = ?, lastName = ?, badgeNumber = ?, phoneNumber = ?,
                        gender = ?, rank = ?, isActive = ?
                    WHERE id = ? AND organization_id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['firstName'],
                $data['lastName'],
                $data['badgeNumber'],
                $data['phoneNumber'],
                $data['gender'],
                $data['rank'],
                filter_var($data['isActive'], FILTER_VALIDATE_BOOLEAN),
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