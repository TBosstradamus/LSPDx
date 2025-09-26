<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class TrainingModule {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all training modules from the database.
     * @return array
     */
    public function getAll() {
        try {
            $sql = "SELECT m.*, o.firstName, o.lastName
                    FROM training_modules m
                    LEFT JOIN officers o ON m.created_by_id = o.id
                    ORDER BY m.title ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching training modules: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a single module by its ID.
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM training_modules WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding module by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new module.
     * @param array $data
     * @return int|false The new module's ID or false on failure.
     */
    public function create($data) {
        if (empty($data['title']) || empty($data['content']) || empty($data['created_by_id'])) {
            return false;
        }

        try {
            $sql = "INSERT INTO training_modules (title, content, created_by_id) VALUES (:title, :content, :created_by_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':content', $data['content']);
            $stmt->bindParam(':created_by_id', $data['created_by_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating module: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes a module by its ID.
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM training_modules WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting module: " . $e->getMessage());
            return false;
        }
    }
}
?>