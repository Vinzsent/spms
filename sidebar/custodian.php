<?php
/**
 * Custodian Sidebar
 * Links: Dashboard, Property Inventory, Rooms Inventory, Release Records, Aircons, Office Inventory Form, 
 * Property Issuance, Transfer Request, Borrowers Form, Other Property Logs, Canvass Form List, Property Reports
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
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wider">DARTS</h1>
                <p class="text-[10px] text-white/60 uppercase tracking-tighter">Property Custodian</p>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
        <!-- Dashboard -->
        <a href="../dashboard.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'dashboard.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-th-large w-6 <?= ($current_page == 'dashboard.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Inventory</p>
        </div>

        <!-- Property Inventory -->
        <a href="property_inventory.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'property_inventory.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-boxes w-6 <?= ($current_page == 'property_inventory.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Property Inventory</span>
        </a>

        <!-- Rooms Inventory -->
        <a href="rooms_inventory.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'rooms_inventory.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-door-open w-6 <?= ($current_page == 'rooms_inventory.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Rooms Inventory</span>
        </a>

        <!-- Aircons -->
        <a href="aircon_list.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'aircon_list.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-fan w-6 <?= ($current_page == 'aircon_list.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Aircons</span>
        </a>

        <!-- Other Property Logs -->
        <a href="other_property_logs.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'other_property_logs.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-clipboard-list w-6 <?= ($current_page == 'other_property_logs.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Other Property Logs</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Operations</p>
        </div>

        <!-- Release Records Dropdown -->
        <div class="relative">
            <details class="group" <?= (in_array($current_page, ['bulb_release_logs.php', 'property_release_logs.php'])) ? 'open' : '' ?>>
                <summary class="flex items-center justify-between px-4 py-3 rounded-xl cursor-pointer transition-all duration-200 list-none hover:bg-white/10 <?= (in_array($current_page, ['bulb_release_logs.php', 'property_release_logs.php'])) ? 'bg-yellow-400/10' : '' ?>">
                    <div class="flex items-center">
                        <i class="fas fa-receipt w-6 <?= (in_array($current_page, ['bulb_release_logs.php', 'property_release_logs.php'])) ? 'text-yellow-400' : 'text-white/70 group-hover:text-white' ?>"></i>
                        <span class="font-medium">Release Records</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="mt-1 ml-6 pl-4 border-l border-white/10 space-y-1">
                    <a href="bulb_release_logs.php" 
                       class="block px-4 py-2 text-sm rounded-lg transition-all duration-200 <?= ($current_page == 'bulb_release_logs.php') ? 'text-yellow-400 font-semibold' : 'text-white/60 hover:text-white hover:bg-white/5' ?>">
                        Bulb Release Logs
                    </a>
                    <a href="property_release_logs.php" 
                       class="block px-4 py-2 text-sm rounded-lg transition-all duration-200 <?= ($current_page == 'property_release_logs.php') ? 'text-yellow-400 font-semibold' : 'text-white/60 hover:text-white hover:bg-white/5' ?>">
                        Property Release Logs
                    </a>
                </div>
            </details>
        </div>

        <!-- Property Issuance -->
        <a href="property_issuance.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'property_issuance.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-file-export w-6 <?= ($current_page == 'property_issuance.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Property Issuance</span>
        </a>

        <!-- Transfer Request -->
        <a href="equipment_transfer_request.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'equipment_transfer_request.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-exchange-alt w-6 <?= ($current_page == 'equipment_transfer_request.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Transfer Request</span>
        </a>

        <!-- Borrowers Form -->
        <a href="borrowers_forms.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'borrowers_forms.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-user-tag w-6 <?= ($current_page == 'borrowers_forms.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Borrowers Form</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Forms & Reports</p>
        </div>

        <!-- Office Inventory Form -->
        <a href="office_inventory.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'office_inventory.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-file-invoice w-6 <?= ($current_page == 'office_inventory.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Office Inventory</span>
        </a>

        <!-- Canvass Form List -->
        <a href="canvass_form_list.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'canvass_form_list.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-list-ul w-6 <?= ($current_page == 'canvass_form_list.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Canvass Form List</span>
        </a>

        <!-- Property Reports -->
        <a href="property_reports.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'property_reports.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-chart-bar w-6 <?= ($current_page == 'property_reports.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Property Reports</span>
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
                <p class="text-[10px] text-white/50 truncate">Custodian</p>
            </div>
        </div>
        <a href="../logout.php" class="flex items-center px-4 py-2 text-xs text-red-400 hover:bg-red-400/10 rounded-lg transition-colors group">
            <i class="fas fa-sign-out-alt mr-2 group-hover:translate-x-1 transition-transform"></i>
            Logout
        </a>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }
</style>

