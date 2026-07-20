<?php
/**
 * Purchasing Sidebar (Staff & Officer)
 * Links: Dashboard, Procurement Statistics, Supplier List, Received Items, Supply Inventory, 
 * Property Inventory, Procurement Tables, Canvass Form, Canvass Form List, Purchase Order, Purchase Order List
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
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-wider">DARTS</h1>
                <p class="text-[10px] text-white/60 uppercase tracking-tighter">Purchasing Dept.</p>
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
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Analysis & Vendors</p>
        </div>

        <!-- Procurement Statistics -->
        <a href="procurement_statistics.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'procurement_statistics.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-chart-line w-6 <?= ($current_page == 'procurement_statistics.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Statistics</span>
        </a>

        <!-- Supplier List -->
        <a href="suppliers.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'suppliers.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-truck w-6 <?= ($current_page == 'suppliers.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Supplier List</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Procurement</p>
        </div>

        <!-- Received Items -->
        <a href="received_items.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'received_items.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-box-open w-6 <?= ($current_page == 'received_items.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Received Items</span>
        </a>

        <!-- Procurement Tables -->
        <a href="procurement.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'procurement.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-table w-6 <?= ($current_page == 'procurement.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Procurement Tables</span>
        </a>

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Inventory Oversight</p>
        </div>

        <!-- Supply Inventory -->
        <a href="Inventory.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'Inventory.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-archive w-6 <?= ($current_page == 'Inventory.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Supply Inventory</span>
        </a>

        <!-- Property Inventory -->
        <a href="property_inventory.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'property_inventory.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-warehouse w-6 <?= ($current_page == 'property_inventory.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Property Inventory</span>
        </a>

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

        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Forms & Orders</p>
        </div>

        <!-- Canvass Form -->
        <a href="canvass_form.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'canvass_form.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-file-invoice-dollar w-6 <?= ($current_page == 'canvass_form.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Canvass Form</span>
        </a>

        <!-- Canvass Form List -->
        <a href="canvass_form_list.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'canvass_form_list.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-list-ol w-6 <?= ($current_page == 'canvass_form_list.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Canvass Form List</span>
        </a>

        <!-- Purchase Order -->
        <a href="purchase_order.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'purchase_order.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-shopping-bag w-6 <?= ($current_page == 'purchase_order.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Purchase Order</span>
        </a>

        <!-- Purchase Order List -->
        <a href="purchase_order_list.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'purchase_order_list.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-clipboard-check w-6 <?= ($current_page == 'purchase_order_list.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Purchase Order List</span>
        </a>

        <?php if ($user_role_norm === 'admin' || $user_role_norm === 'superadmin'): ?>
        <div class="pt-4 pb-2 px-4">
            <p class="text-[10px] text-white/40 uppercase font-semibold tracking-widest">Settings</p>
        </div>
        <a href="printer_header.php" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group <?= ($current_page == 'printer_header.php') ? 'bg-yellow-400 text-[#073b1d] shadow-lg' : 'hover:bg-white/10' ?>">
            <i class="fas fa-print w-6 <?= ($current_page == 'printer_header.php') ? 'text-[#073b1d]' : 'text-white/70 group-hover:text-white' ?>"></i>
            <span class="font-medium">Printer Header</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-white/10 bg-black/10">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-[#073b1d] font-bold text-xs">
                <?= strtoupper(substr($_SESSION['user']['first_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-1 truncate">
                <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></p>
                <p class="text-[10px] text-white/50 truncate">Purchasing</p>
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

