<?php

/**
 * Supply In-charge Sidebar
 * Links: Dashboard, Issuance, Inventory, Supply Offices, Supply Reports, Canvass Form List, Notification
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Tailwind CSS CDN (Optional: Remove if already in header) -->
<script src="https://cdn.tailwindcss.com"></script>

<div class="sidebar no-print flex flex-col h-screen w-64 bg-[#073b1d] text-white fixed left-0 top-0 z-50 shadow-2xl transition-all duration-300 ease-in-out">
    <!-- Sidebar Header -->
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center text-[#073b1d]">
                <i class="fas fa-box-open text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wider">DARTS</h1>
                <p class="text-[10px] text-white/60 uppercase tracking-tighter">Supply In-charge</p>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <!-- Dashboard -->
        <a href="../dashboard.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'dashboard.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-th-large w-6 <?= ($current_page == 'dashboard.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Management</p>
        </div>

        <!-- Issuance -->
        <a href="issuance.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'issuance.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-file-export w-6 <?= ($current_page == 'issuance.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Issuance</span>
        </a>

        <!-- Inventory -->
        <a href="Inventory.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'Inventory.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-boxes w-6 <?= ($current_page == 'Inventory.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Inventory</span>
        </a>

        <!-- Supply Offices -->
        <a href="supply_offices_request.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'supply_offices_request.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-building w-6 <?= ($current_page == 'supply_offices_request.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Supply Offices</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Reports & Forms</p>
        </div>

        <!-- Supply Reports -->
        <a href="supply_reports.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'supply_reports.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-chart-pie w-6 <?= ($current_page == 'supply_reports.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Supply Reports</span>
        </a>

        <!-- Canvass Form List -->
        <a href="canvass_form_list.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'canvass_form_list.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-list-alt w-6 <?= ($current_page == 'canvass_form_list.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Canvass Form List</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">System</p>
        </div>

        <!-- Notification -->
        <a href="notifications.php"
            class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'notifications.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-bell w-6 <?= ($current_page == 'notifications.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Notification</span>
            <span class="ml-auto bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">New</span>
        </a>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-white/10 bg-black/10">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-[#073b1d] font-bold text-xs">
                <?= strtoupper(substr($_SESSION['user']['first_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-1 truncate">
                <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></p>
                <p class="text-[10px] text-white/50 truncate">In-charge</p>
            </div>
        </div>
        <a href="../logout.php" class="flex items-center px-4 py-2 text-xs text-red-400 hover:bg-red-400/10 rounded-lg transition-colors group">
            <i class="fas fa-sign-out-alt mr-2 group-hover:translate-x-1 transition-transform"></i>
            Logout
        </a>
    </div>
</div>