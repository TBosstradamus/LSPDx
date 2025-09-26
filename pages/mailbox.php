<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Placeholder for Mailbox page
echo "<h1>Mailbox</h1>";
echo "<p>This page is under construction.</p>";
?>