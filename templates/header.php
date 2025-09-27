<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}
$currentPage = $_GET['page'] ?? 'dashboard';
require_once BASE_PATH . '/src/Auth.php'; // Include Auth to use hasRole

// Mapping pages to navigation groups
$navLinks = [
    'REGISTER' => [
        'fuhrpark' => ['name' => 'Fahrzeuge', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5v-1.875a3.375 3.375 0 013.375-3.375h1.5a1.125 1.125 0 011.125 1.125v-1.5a3.375 3.375 0 013.375-3.375H9.75" />'],
        'documents' => ['name' => 'Dokumente', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />'],
    ],
    'ABTEILUNG' => [
        'dashboard' => ['name' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 018.25 20.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />'],
        'dispatch' => ['name' => 'Dispatch', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />'],
        'hr' => ['name' => 'Personal', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.663M12 12A3 3 0 1012 6a3 3 0 000 6z" />'],
        'training_modules' => ['name' => 'FTO', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0l-2.072-1.036A59.926 59.926 0 0112 2.102a59.926 59.926 0 0110.399 7.009l-2.072 1.036m-15.482 0A50.57 50.57 0 0112 13.489a50.57 50.57 0 017.74-3.342M12 13.489l-7.74-3.342m15.48 0l-7.74 3.342" />'],
        'mein_dienst' => ['name' => 'Mein Dienst', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />'],
        'mailbox' => ['name' => 'Postfach', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />'],
    ],
    'ADMINISTRATION' => [
        'edit_user_roles' => ['name' => 'Rollen verwalten', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-1.003 1.11-1.226M12 21c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0 0a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />'],
        'time_approval' => ['name' => 'Dienstzeit Genehmigung', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />'],
    ]
];
?>
<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | MDT' : 'MDT'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        @layer base {
            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background-color: #161B22; }
            ::-webkit-scrollbar-thumb { background-color: #313945; border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background-color: #4A5361; }
        }
    </style>
    <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'brand-bg': '#0D1117', 'brand-sidebar': '#161B22', 'brand-card': '#161B22',
            'brand-border': '#30363D', 'brand-text-primary': '#C9D1D9',
            'brand-text-secondary': '#8B949E', 'brand-blue': '#58A6FF', 'brand-red': '#F85149',
          }
        }
      }
    }
    </script>
</head>
<body class="h-full bg-brand-bg text-brand-text-primary font-sans">
    <div class="flex h-full">
        <!-- Static sidebar for desktop -->
        <div class="flex flex-col w-64 bg-brand-sidebar">
            <div class="flex items-center justify-center h-20 border-b border-brand-border">
                <div class="flex items-center">
                    <img class="h-10 w-auto" src="https://r2.fivemanage.com/dewOfulJ8c84LP6UMf9j5/global/logo_lspd.png" alt="LSPD Logo">
                    <div class="ml-3">
                        <p class="text-white font-bold text-sm">LOS SANTOS POLICE</p>
                        <p class="text-brand-text-secondary text-xs">Mobile Data Terminal</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 flex flex-col overflow-y-auto">
                <nav class="flex-1 px-4 py-4 space-y-6">
                    <?php foreach ($navLinks as $group => $links): ?>
                        <?php
                        if ($group === 'ADMINISTRATION' && !Auth::hasRole('Admin')) {
                            continue;
                        }
                        ?>
                        <div>
                            <h3 class="px-3 text-xs font-semibold text-brand-text-secondary uppercase tracking-wider"><?php echo $group; ?></h3>
                            <div class="mt-2 space-y-1">
                                <?php foreach ($links as $page => $details): ?>
                                    <a href="index.php?page=<?php echo $page; ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo ($currentPage === $page) ? 'bg-gray-900/50 text-white' : 'text-brand-text-secondary hover:text-white hover:bg-gray-700/50'; ?>">
                                        <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <?php echo $details['icon']; ?>
                                        </svg>
                                        <?php echo $details['name']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="flex-shrink-0 flex border-t border-brand-border p-4">
                <a href="index.php?page=logout" class="flex-shrink-0 w-full group block">
                    <div class="flex items-center">
                        <div class="bg-brand-red rounded-md p-2">
                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">
                                MDT Schließen
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="bg-brand-sidebar border-b border-brand-border px-8 py-3">
                 <div class="flex items-center justify-between">
                    <div class="flex items-center">
                         <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                         <span class="text-sm text-brand-text-secondary">System Online</span>
                    </div>
                    <div class="text-sm text-brand-text-secondary">
                        Version 1.0.0
                    </div>
                 </div>
            </div>
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                <div class="py-8 px-8">
                    <!-- Page content will be inserted here -->