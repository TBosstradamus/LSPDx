<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}
$currentPage = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'LSPD Intranet'; ?> - Intranet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            gray: {
              800: '#1F2937',
              900: '#111827',
            },
            blue: {
                400: '#60A5FA',
                500: '#3B82F6',
            }
          }
        }
      }
    }
    </script>
</head>
<body class="h-full">
    <div class="flex h-full">
        <!-- Static sidebar for desktop -->
        <div class="flex flex-col w-64 bg-gray-900 border-r border-gray-700">
            <div class="flex items-center justify-center h-16 border-b border-gray-700">
                <span class="text-white text-xl font-bold">LSPD Intranet</span>
            </div>
            <div class="flex-1 flex flex-col overflow-y-auto">
                <nav class="flex-1 px-2 py-4">
                    <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Hauptmenü</h3>
                    <a href="index.php?page=dashboard" class="mt-1 group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === 'dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                        <!-- Icon: Dashboard -->
                        <svg class="mr-3 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        Dashboard
                    </a>
                    <a href="index.php?page=dispatch" class="mt-1 group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === 'dispatch') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                        <!-- Icon: Dispatch -->
                        <svg class="mr-3 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Dispatch
                    </a>
                    <a href="index.php?page=mein_dienst" class="mt-1 group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === 'mein_dienst') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                        <!-- Icon: Mein Dienst -->
                        <svg class="mr-3 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Mein Dienst
                    </a>
                    <a href="index.php?page=mailbox" class="mt-1 group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === 'mailbox') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                        <!-- Icon: Mailbox -->
                        <svg class="mr-3 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        Postfach
                    </a>

                    <h3 class="mt-4 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Verwaltung</h3>
                    <a href="index.php?page=hr" class="mt-1 group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === 'hr') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                        <!-- Icon: HR -->
                        <svg class="mr-3 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197" /></svg>
                        Personal
                    </a>
                     <a href="index.php?page=fuhrpark" class="mt-1 group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === 'fuhrpark') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?>">
                        <!-- Icon: Fuhrpark -->
                        <svg class="mr-3 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h8a1 1 0 001-1zM3 11h10" /></svg>
                        Fuhrpark
                    </a>

                </nav>
            </div>
            <div class="flex-shrink-0 flex border-t border-gray-700 p-4">
                <a href="index.php?page=logout" class="flex-shrink-0 w-full group block">
                    <div class="flex items-center">
                        <div>
                            <!-- Icon: Logout -->
                            <svg class="inline-block h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-400 group-hover:text-red-300">
                                Abmelden
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold text-white"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>
                    <div class="mt-4">
                        <!-- Page content will be inserted here -->