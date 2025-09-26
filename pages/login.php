<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard'); exit;
}
?>
<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSPD Intranet - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            gray: { 800: '#1F2937', 900: '#111827' },
            blue: { 500: '#3B82F6', 600: '#2563EB' }
          }
        }
      }
    }
    </script>
</head>
<body class="h-full">
<div class="flex min-h-full items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="w-full max-w-md space-y-8">
    <div>
      <h1 class="text-center text-4xl font-bold tracking-tight text-white">LSPD Intranet</h1>
      <h2 class="mt-2 text-center text-xl tracking-tight text-gray-400">Anmeldung</h2>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-500 border border-red-400 text-white px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Fehler!</strong>
            <span class="block sm:inline">Ungültiger Benutzername oder Passwort.</span>
        </div>
    <?php endif; ?>

    <form class="mt-8 space-y-6" action="index.php?page=handle_login" method="POST">
      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="username" class="sr-only">Benutzername</label>
          <input id="username" name="username" type="text" required class="relative block w-full appearance-none rounded-none rounded-t-md border border-gray-700 bg-gray-800 px-3 py-2 text-white placeholder-gray-400 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm" placeholder="Benutzername">
        </div>
        <div>
          <label for="password" class="sr-only">Passwort</label>
          <input id="password" name="password" type="password" required class="relative block w-full appearance-none rounded-none rounded-b-md border border-gray-700 bg-gray-800 px-3 py-2 text-white placeholder-gray-400 focus:z-10 focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm" placeholder="Passwort">
        </div>
      </div>

      <div>
        <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900">
          Anmelden
        </button>
      </div>
    </form>
  </div>
</div>
</body>
</html>