<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class TrainingModule {
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
     * Fetches all training modules from the current user's organization.
     * @return array
     */
    public function getAll() {
        try {
            $sql = "SELECT m.*, o.firstName, o.lastName
                    FROM training_modules m
                    LEFT JOIN officers o ON m.created_by_id = o.id
                    WHERE m.organization_id = ?
                    ORDER BY m.title ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching training modules: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single module by its ID, scoped to the organization.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM training_modules WHERE id = ? AND organization_id = ?");
            $stmt->execute([$id, $this->organization_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding module by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new module for the current organization.
     * @param array $data
     * @return int|false The new module's ID or false on failure.
     */
    public function create($data) {
        if (empty($data['title']) || empty($data['content']) || empty($data['created_by_id'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO training_modules (organization_id, title, content, created_by_id) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->organization_id,
                $data['title'],
                $data['content'],
                $data['created_by_id']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating module: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a module by its ID, scoped to the organization.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM training_modules WHERE id = ? AND organization_id = ?");
            return $stmt->execute([$id, $this->organization_id]);
        } catch (PDOException $e) {
            error_log("Error deleting module: " . $e->getMessage());
            return false;
        }
    }
}
?>