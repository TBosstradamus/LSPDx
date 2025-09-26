<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}
?>
        </main> <!-- End of .content -->
    </div> <!-- End of .main-layout -->

    <!-- Global JS file can be included here -->
    <!-- <script src="public/js/main.js"></script> -->
</body>
</html>