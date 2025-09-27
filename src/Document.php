<?php
// src/Document.php

// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Document {
    private $db;
    private $organization_id;

    public function __construct($organization_id) {
        $this->db = Database::getInstance()->getConnection();
        if (empty($organization_id)) {
            throw new InvalidArgumentException("Organization ID must be provided for Document model.");
        }
        $this->organization_id = $organization_id;
    }

    /**
     * Gets all documents for the organization.
     * This is a placeholder to prevent errors.
     * @return array
     */
    public function getAll() {
        try {
            $sql = "SELECT d.id, d.title, d.created_at, o.firstName, o.lastName
                    FROM documents d
                    LEFT JOIN officers o ON d.created_by_id = o.id
                    WHERE d.organization_id = ?
                    ORDER BY d.updated_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->organization_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching documents: " . $e->getMessage());
            return [];
        }
    }
}
?>