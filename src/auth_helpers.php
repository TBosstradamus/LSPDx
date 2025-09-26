<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

/**
 * Checks if the currently logged-in user has a specific permission.
 *
 * @param string $permissionName The name of the permission to check (e.g., 'hr_manage_officers').
 * @return bool True if the user has the permission, false otherwise.
 */
function hasPermission($permissionName) {
    // If the permissions are not set in the session, the user has no rights.
    if (!isset($_SESSION['permissions'])) {
        return false;
    }

    // Admins have all permissions implicitly. The 'admin_area_access' is a stand-in for this.
    if (in_array('admin_area_access', $_SESSION['permissions'])) {
        return true;
    }

    // Check if the specific permission exists for the user.
    return in_array($permissionName, $_SESSION['permissions']);
}

/**
 * Halts execution and shows a 403 Forbidden page if the user does not have the required permission.
 *
 * @param string $permissionName The name of the permission to check.
 */
function requirePermission($permissionName) {
    if (!hasPermission($permissionName)) {
        http_response_code(403);
        // You could include a nice 403 template page here.
        die('<h1>403 - Zugriff verweigert</h1><p>Sie haben nicht die erforderliche Berechtigung, um auf diese Seite zuzugreifen.</p>');
    }
}
?>