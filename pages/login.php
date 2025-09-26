<?php
// Prevent direct access to this file
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// If the user is already logged in, redirect them to the dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// We will include a simple header for the login page
// For now, we keep it minimal.
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSPD Intranet - Login</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        /* Specific styles for the login page */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #1a202c; /* bg-gray-900 */
            color: #e2e8f0; /* text-gray-200 */
        }
        .login-container {
            background-color: #2d3748; /* bg-gray-800 */
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            border: 1px solid #4a5568; /* border-gray-700 */
        }
        .login-container h1 {
            font-size: 1.875rem; /* text-3xl */
            font-weight: bold;
            text-align: center;
            margin-bottom: 1.5rem;
            color: #90cdf4; /* text-blue-300 */
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #a0aec0; /* text-gray-400 */
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.25rem;
            background-color: #1a202c; /* bg-gray-900 */
            border: 1px solid #4a5568; /* border-gray-700 */
            color: #e2e8f0; /* text-gray-200 */
            box-sizing: border-box;
        }
        .login-button {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.25rem;
            background-color: #3182ce; /* bg-blue-600 */
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .login-button:hover {
            background-color: #2b6cb0; /* bg-blue-700 */
        }
        .error-message {
            background-color: #c53030; /* bg-red-600 */
            color: white;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>LSPD Intranet</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php
                if ($_GET['error'] === 'invalid_credentials') {
                    echo 'Ungültiger Benutzername oder Passwort.';
                } else {
                    echo 'Ein Fehler ist aufgetreten.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=handle_login" method="POST">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">Anmelden</button>
        </form>
    </div>
</body>
</html>