<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class License {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all licenses for a specific officer.
     * @param int $officerId The ID of the officer.
     * @return array An array of license records.
     */
    public function getForOfficer($officerId) {
        try {
            $sql = "SELECT
                        ol.id,
                        ol.issuedBy,
                        ol.issuedAt,
                        ol.expiresAt,
                        l.name
                    FROM officer_licenses ol
                    JOIN licenses l ON ol.license_id = l.id
                    WHERE ol.officer_id = :officer_id
                    ORDER BY l.name";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching licenses for officer: " . $e->getMessage());
            return [];
        }
    }

    // Methods for assigning/revoking licenses can be added later.
}
?>