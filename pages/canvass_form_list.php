<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Canvass List';
include '../includes/auth.php';
include '../includes/db.php';

// Normalize user role early for both standard and AJAX requests
$user_type = $_SESSION['user_type'] ?? '';
$user_role_norm = str_replace([' ', '-'], '', strtolower($user_type));

// Capture search term from GET query parameter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Pagination settings
$items_per_page = 10; // 10 rows per page
$current_page_num = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page_num < 1) $current_page_num = 1;
$offset = ($current_page_num - 1) * $items_per_page;

if ($search !== '') {
    $search_param = '%' . $search . '%';
    
    // Get total count for pagination with search filter (GEMINI.md optimized search query)
    $count_query = "
        SELECT COUNT(DISTINCT c.canvass_id) as total 
        FROM canvass c 
        LEFT JOIN user u ON c.created_by = u.id
        LEFT JOIN canvass_items ci ON c.canvass_id = ci.canvass_id
        WHERE (c.hide_canvass = '0' OR c.hide_canvass IS NULL)
          AND (
               ci.supplier_name LIKE ? 
               OR ci.item_description LIKE ? 
               OR ci.department LIKE ? 
               OR ci.campus LIKE ? 
               OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? 
               OR c.status LIKE ?
          )
    ";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("ssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
    $total_rows = $total_result ? $total_result->fetch_assoc()['total'] : 0;
    $count_stmt->close();
} else {
    // Get total count for pagination (no search)
    $count_query = "SELECT COUNT(DISTINCT c.canvass_id) as total FROM canvass c WHERE (c.hide_canvass = '0' OR c.hide_canvass IS NULL)";
    $total_result = $conn->query($count_query);
    $total_rows = $total_result ? $total_result->fetch_assoc()['total'] : 0;
}

$total_pages = ceil($total_rows / $items_per_page);

// Guard: if current page exceeds total pages, set to last page
if ($current_page_num > $total_pages && $total_pages > 0) {
    $current_page_num = $total_pages;
    $offset = ($current_page_num - 1) * $items_per_page;
}

// Fetch canvass records with user information (using prepared statements for efficiency and security)
if ($search !== '') {
    $search_param = '%' . $search . '%';
    $canvass_query = "
        SELECT 
            c.canvass_id,
            c.hide_canvass,
            c.canvass_date,
            c.total_amount,
            c.status,
            c.notes,
            c.created_at,
            ci.supplier_name,
            ci.item_description,
            ci.department,
            ci.campus,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
            COUNT(ci.canvass_item_id) as item_count
        FROM canvass c
        LEFT JOIN user u ON c.created_by = u.id
        LEFT JOIN canvass_items ci ON c.canvass_id = ci.canvass_id
        WHERE (c.hide_canvass = '0' OR c.hide_canvass IS NULL)
          AND (
               ci.supplier_name LIKE ? 
               OR ci.item_description LIKE ? 
               OR ci.department LIKE ? 
               OR ci.campus LIKE ? 
               OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? 
               OR c.status LIKE ?
          )
        GROUP BY c.canvass_id
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($canvass_query);
    $stmt->bind_param("ssssssii", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $items_per_page, $offset);
    $stmt->execute();
    $canvass_result = $stmt->get_result();
    $stmt->close();
} else {
    $canvass_query = "
        SELECT 
            c.canvass_id,
            c.hide_canvass,
            c.canvass_date,
            c.total_amount,
            c.status,
            c.notes,
            c.created_at,
            ci.supplier_name,
            ci.item_description,
            ci.department,
            ci.campus,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
            COUNT(ci.canvass_item_id) as item_count
        FROM canvass c
        LEFT JOIN user u ON c.created_by = u.id
        LEFT JOIN canvass_items ci ON c.canvass_id = ci.canvass_id
        WHERE (c.hide_canvass = '0' OR c.hide_canvass IS NULL)
        GROUP BY c.canvass_id
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($canvass_query);
    $stmt->bind_param("ii", $items_per_page, $offset);
    $stmt->execute();
    $canvass_result = $stmt->get_result();
    $stmt->close();
}

/**
 * Helper to render canvass table rows HTML
 */
function get_table_rows_html($canvass_result, $user_role_norm, $search = '') {
    ob_start();
    if ($canvass_result && $canvass_result->num_rows > 0) {
        while ($row = $canvass_result->fetch_assoc()): ?>
            <tr data-canvass-id="<?= $row['canvass_id'] ?>">
                <td class="select-cell" style="display: none;">
                    <input type="checkbox" class="row-checkbox">
                </td>
                <td>
                    <strong><?= htmlspecialchars($row['supplier_name'] ?? '—') ?></strong>
                </td>
                <td>
                    <?= htmlspecialchars($row['department'] ?? '—') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['campus'] ?? '—') ?>
                </td>
                <td>
                    <?= htmlspecialchars($row['item_description'] ?? '—') ?>
                </td>
                <td>
                    <strong>₱<?= number_format($row['total_amount'], 2) ?></strong>
                </td>
                <td>
                    <span class="badge badge-info" style="background-color: var(--primary-green); color: white; font-weight: bold; font-size: 14px; padding: 5px 10px; border-radius: 4px; display: inline-block;"><?= $row['item_count'] ?> items</span>
                </td>
                <td>
                    <span class="status-badge status-<?= strtolower($row['status'] ?? 'draft') ?>">
                        <?= htmlspecialchars($row['status'] ?? 'Draft') ?>
                    </span>
                </td>
                <td>
                    <?= htmlspecialchars($row['created_by_name'] ?? 'Unknown') ?>
                </td>
                <td>
                    <?= date('M d, Y g:i A', strtotime($row['created_at'])) ?>
                </td>
                <td>
                    <div class="table-actions">
                        <button class="btn btn-info btn-sm" onclick="viewCanvass(<?= $row['canvass_id'] ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <?php if (!in_array($user_role_norm, ['propertycustodian', 'supplyincharge'])): ?>
                            <button class="btn btn-warning btn-sm" onclick="editCanvass(<?= $row['canvass_id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteCanvass(<?= $row['canvass_id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endwhile;
    } else { ?>
        <tr class="empty-row-placeholder">
            <td colspan="11" class="text-center py-5 text-slate-500">
                <div class="empty-state" style="padding: 40px 20px; text-align: center;">
                    <i class="fas <?= $search !== '' ? 'fa-search' : 'fa-clipboard-list' ?>" style="font-size: 3rem; opacity: 0.4; margin-bottom: 15px; display: block; color: var(--primary-green);"></i>
                    <h3 style="font-size: 1.25rem; margin-bottom: 8px; font-weight: 600; color: #334155;"><?= $search !== '' ? 'No Matching Records Found' : 'No Canvass Records Found' ?></h3>
                    <p style="font-size: 0.95rem; color: #64748b;"><?= $search !== '' ? 'No records match your search criteria. Try a different query.' : 'Start by creating your first canvass form.' ?></p>
                    <?php if ($search !== ''): ?>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('table-search-input').value = ''; document.getElementById('clear-search-btn').style.display = 'none'; loadPage(1);" style="display: inline-flex; margin-top: 15px; background-color: var(--primary-green); color: white;">
                            <i class="fas fa-undo"></i> Clear Search
                        </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php }
    return ob_get_clean();
}

/**
 * Helper to render pagination controls HTML (preserves search criteria)
 */
function get_pagination_html($current_page, $total_pages, $search = '') {
    if ($total_pages <= 1) {
        return '';
    }
    $q_param = $search !== '' ? '&q=' . urlencode($search) : '';
    ob_start();
    ?>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <!-- Previous Button -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page - 1 ?><?= $q_param ?>" aria-label="Previous" data-page="<?= $current_page - 1 ?>">
                    <i class="fas fa-chevron-left"></i> <span class="d-none d-md-inline ms-1">Prev</span>
                </a>
            </li>

            <!-- Current Page (Always shown) -->
            <li class="page-item active">
                <a class="page-link" href="#" data-page="<?= $current_page ?>"><?= $current_page ?></a>
            </li>

            <!-- Next Button -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page + 1 ?><?= $q_param ?>" aria-label="Next" data-page="<?= $current_page + 1 ?>">
                    <span class="d-none d-md-inline me-1">Next</span> <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php
    return ob_get_clean();
}

// Self-contained AJAX Request Handler
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'rows' => get_table_rows_html($canvass_result, $user_role_norm, $search),
        'pagination' => get_pagination_html($current_page_num, $total_pages, $search),
        'current_page' => $current_page_num,
        'total_pages' => $total_pages
    ]);
    exit;
}

// Continue with full page load header
include '../includes/header.php';

// Fetch suppliers for dropdown
$suppliers_query = "SELECT supplier_name FROM supplier ORDER BY supplier_name ASC";
$suppliers_result = $conn->query($suppliers_query);
$suppliers = [];
if ($suppliers_result && $suppliers_result->num_rows > 0) {
    while ($row = $suppliers_result->fetch_assoc()) {
        $suppliers[] = $row['supplier_name'];
    }
}



$canvass_items_query = "SELECT * FROM canvass_items";
$canvass_items_result = $conn->query($canvass_items_query);

$canvass_items = [];
if ($canvass_items_result && $canvass_items_result->num_rows > 0) {
    while ($row = $canvass_items_result->fetch_assoc()) {
        $canvass_items[] = $row;
    }
}

// Shared lists for dropdowns
$departments = [
    "Admin Office",
    "BSBA",
    "BSHM",
    "ELEMENTARY DEPT. BASIC EDUCATION",
    "JHS/ BASIC EDUCATION",
    "CELA OFFICE",
    "CES",
    "CJE",
    "CLINIC",
    "FINANCE/ ACCOUNTING",
    "GSO/ Security officer",
    "GUIDANCE/Chaplain",
    "HUMAN RESOURCE MANAGEMENT",
    "ITE Program",
    "MIS",
    "NSTP",
    "OSAS",
    "PHOTOCOPY ROOM",
    "PRESIDENT'S OFFICE",
    "Property Custodian",
    "REGISTRAR'S OFFICE",
    "SENIOR HIGH SCHOOL PROGRAM",
    "SUPPLY ROOM",
    "VPAA OFFICE",
    "OSSD",
    "MAIN LIBRARY",
    "BED LIBRARY"
];
sort($departments);

$campuses = ["MAIN", "BED"];
?>


<script>
    /**
     * Core Canvass Functions
     * Consolidated and cleaned for DARTS Procurement System
     */

    // Available suppliers, departments, and campuses from PHP
    const availableSuppliers = <?= json_encode($suppliers ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]' ?>;
    const availableDepartments = <?= json_encode($departments ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]' ?>;
    const availableCampuses = <?= json_encode($campuses ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '[]' ?>;
    const userRole = '<?= str_replace("'", "\\'", $user_role_norm ?? '') ?>';

    let currentCanvassId = null;
    let canvassModalInstance = null;

    /**
     * Display an error message on the page for better visibility
     */
    function showPageError(message) {
        const errorContainer = document.getElementById('pageErrorContainer');
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 5px solid #dc3545;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong class="d-block">Action Failed</strong>
                            <span>${message}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            errorContainer.style.display = 'block';
            errorContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        } else {
            alert('Error: ' + message);
        }
    }

    /**
     * Create the view modal dynamically if it doesn't exist in the DOM
     */
    function createDynamicModal() {
        if (document.getElementById('viewCanvassModal')) return;

        const modalHtml = `
            <div class="modal fade" id="viewCanvassModal" tabindex="-1" aria-labelledby="viewCanvassModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                        <div class="modal-header bg-slate-800 text-white border-0 py-6 px-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-xl">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title font-black text-xl tracking-tight leading-none" id="viewCanvassModalLabel">Canvass Details</h5>
                                    <p class="text-white/60 text-[10px] uppercase font-bold tracking-widest mt-1">Detailed Record Preview</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white opacity-50 hover:opacity-100 transition-opacity" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0" id="canvassDetailsContent">
                            <div class="p-20 text-center text-slate-400">
                                <div class="animate-spin inline-block w-8 h-8 border-4 border-current border-t-transparent text-primary rounded-full mb-4" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="font-bold text-sm uppercase tracking-widest">Fetching details...</p>
                            </div>
                        </div>
                        <div class="modal-footer bg-slate-50 border-0 py-4 px-8 flex justify-between items-center">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">DARTS Procurement System</p>
                            <button type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2.5 rounded-xl font-bold transition-all text-sm" data-bs-dismiss="modal">Close Preview</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        console.log('Dynamic modal created successfully.');
    }

    /**
     * Fetch and view canvass details in a modal
     */
    function viewCanvass(canvassId) {
        if (!canvassId) {
            showPageError('Invalid Canvass ID.');
            return;
        }

        console.log('Viewing canvass:', canvassId);
        currentCanvassId = canvassId;

        // Find modal element, create if missing
        let modalEl = document.getElementById('viewCanvassModal');
        if (!modalEl) {
            console.warn('Modal element #viewCanvassModal not found in DOM. Creating dynamically...');
            createDynamicModal();
            modalEl = document.getElementById('viewCanvassModal');
        }

        if (!modalEl) {
            console.error('CRITICAL: Modal element could not be found or created.');
            showPageError('Modal initialization failed.');
            return;
        }

        // Initialize modal if not already done
        if (!canvassModalInstance && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                canvassModalInstance = new bootstrap.Modal(modalEl);
            } catch (e) {
                console.error('Bootstrap Modal initialization failed:', e);
            }
        }

        // Show loading state in modal body BEFORE showing modal
        const detailsContent = document.getElementById('canvassDetailsContent');
        if (detailsContent) {
            detailsContent.innerHTML = `
                <div class="p-20 text-center text-slate-400">
                    <div class="animate-spin inline-block w-8 h-8 border-4 border-current border-t-transparent text-primary rounded-full mb-4" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="font-bold text-sm uppercase tracking-widest">Fetching details...</p>
                </div>
            `;
        }

        // Show the modal
        if (canvassModalInstance) {
            canvassModalInstance.show();
        } else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
            $(modalEl).modal('show');
        } else {
            // Manual fallback if bootstrap JS isn't working/loaded
            console.warn('Using manual modal display fallback');
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.style.zIndex = '1060';
            modalEl.style.backgroundColor = 'rgba(0,0,0,0.6)';
            document.body.classList.add('modal-open');

            // Ensure close buttons work in manual mode
            const closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');
            closeBtns.forEach(btn => {
                btn.onclick = () => {
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                    document.body.classList.remove('modal-open');
                };
            });
        }

        // Fetch data
        fetch(`../actions/get_canvass_details.php?id=${canvassId}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayCanvassDetails(data.canvass, data.items);
                } else {
                    showPageError(data.message || 'Failed to load canvass details.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showPageError(`Connection Error: ${error.message}`);
            });
    }

    /**
     * Build and display canvass details HTML
     */
    function displayCanvassDetails(canvass, items) {
        currentCanvassId = canvass.canvass_id;
        let itemsHtml = '';
        let grandTotal = 0;

        items.forEach(item => {
            const showActions = !['propertycustodian', 'supplyincharge'].includes(userRole);
            itemsHtml += `
                <tr data-id="${item.canvass_item_id}">
                    <td class="supplier_name p-3 border-b border-slate-100">${item.supplier_name}</td>
                    <td class="department p-3 border-b border-slate-100">${item.department || ''}</td>
                    <td class="campus p-3 border-b border-slate-100">${item.campus || ''}</td>
                    <td class="item_description p-3 border-b border-slate-100">${item.item_description}</td>
                    <td class="quantity p-3 border-b border-slate-100">${parseFloat(item.quantity)}</td>
                    <td class="unit_cost p-3 border-b border-slate-100">₱${parseFloat(item.unit_cost).toFixed(2)}</td>
                    <td class="total_cost p-3 border-b border-slate-100 font-bold">₱${parseFloat(item.total_cost).toFixed(2)}</td>
                    ${showActions ? `
                    <td class="p-3 border-b border-slate-100">
                        <div class="flex gap-1">
                            <button class="bg-amber-50 text-amber-600 hover:bg-amber-100 p-1.5 rounded-lg transition-all" onclick="editItem(this)">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button class="bg-rose-50 text-rose-600 hover:bg-rose-100 p-1.5 rounded-lg transition-all" onclick="deleteCanvassItem(${item.canvass_item_id})">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>` : ''}
                </tr>
            `;
            grandTotal += parseFloat(item.total_cost);
        });

        const showTableActions = !['propertycustodian', 'supplyincharge'].includes(userRole);

        const content = `
            <div class="canvass-details space-y-6 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Canvass Information</h4>
                        <div class="space-y-1">
                            <p class="text-sm"><strong>ID:</strong> <span class="text-slate-600">#${canvass.canvass_id}</span></p>
                            <p class="text-sm"><strong>Date:</strong> <span class="text-slate-600">${new Date(canvass.canvass_date).toLocaleDateString()}</span></p>
                            <p class="text-sm"><strong>Status:</strong> <span class="status-badge status-${canvass.status.toLowerCase()}">${canvass.status}</span></p>
                        </div>
                    </div>
                    <div class="space-y-2 md:text-right">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Financial Summary</h4>
                        <div class="space-y-1">
                            <p class="text-2xl font-black text-slate-800">₱${parseFloat(canvass.total_amount).toFixed(2)}</p>
                            <p class="text-[10px] text-slate-400 uppercase font-bold">Created by ${canvass.created_by_name || 'System'}</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold text-slate-800">Canvassed Items</h4>
                        <button onclick="printCanvassDetails(${canvass.canvass_id})" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-lg transition-all">
                            <i class="fas fa-print"></i> Print Details
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="p-3 font-bold uppercase text-[10px]">Supplier</th>
                                    <th class="p-3 font-bold uppercase text-[10px]">Dept</th>
                                    <th class="p-3 font-bold uppercase text-[10px]">Campus</th>
                                    <th class="p-3 font-bold uppercase text-[10px]">Description</th>
                                    <th class="p-3 font-bold uppercase text-[10px]">Qty</th>
                                    <th class="p-3 font-bold uppercase text-[10px]">Unit</th>
                                    <th class="p-3 font-bold uppercase text-[10px]">Total</th>
                                    ${showTableActions ? `<th class="p-3 font-bold uppercase text-[10px]">Action</th>` : ''}
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                                <tr class="bg-slate-50 font-bold text-slate-800">
                                    <td colspan="6" class="p-3 text-right text-xs uppercase">Grand Total</td>
                                    <td class="p-3 text-base">₱${grandTotal.toFixed(2)}</td>
                                    ${showTableActions ? `<td class="p-3"></td>` : ''}
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                ${canvass.notes ? `
                <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <h5 class="text-[10px] font-bold text-amber-800 uppercase tracking-widest mb-1">Internal Notes</h5>
                    <p class="text-sm text-amber-900 italic">"${canvass.notes}"</p>
                </div>` : ''}
            </div>
        `;

        const container = document.getElementById('canvassDetailsContent');
        if (container) container.innerHTML = content;
    }

    function editCanvass(canvassId) {
        window.location.href = `canvass_form.php?edit=${canvassId}`;
    }

    function deleteCanvass(canvassId) {
        if (confirm('Are you sure you want to delete this canvass? This action cannot be undone.')) {
            fetch('../actions/delete_canvass.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        canvass_id: canvassId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Canvass deleted successfully');
                        location.reload();
                    } else {
                        showPageError(data.message || 'Failed to delete.');
                    }
                })
                .catch(error => showPageError(`Delete Failed: ${error.message}`));
        }
    }

    function toggleSelectMode() {
        const selectCells = document.querySelectorAll('.select-cell');
        const selectHeader = document.getElementById('selectHeader');
        const selectBtn = document.getElementById('selectForPrintBtn');
        const printBtn = document.getElementById('printSelectedBtn');
        const cancelBtn = document.getElementById('cancelSelectBtn');
        const selectHeaderCell = document.getElementById('selectHeaderCell');

        const isShowing = selectHeader && selectHeader.style.display !== 'none';

        if (selectCells) selectCells.forEach(cell => cell.style.display = isShowing ? 'none' : 'table-cell');
        if (selectHeader) selectHeader.style.display = isShowing ? 'none' : 'table-row';
        if (selectHeaderCell) selectHeaderCell.style.display = isShowing ? 'none' : 'table-cell';
        if (selectBtn) selectBtn.style.display = isShowing ? 'inline-block' : 'none';
        if (printBtn) printBtn.style.display = isShowing ? 'none' : 'inline-block';
        if (cancelBtn) cancelBtn.style.display = isShowing ? 'none' : 'inline-block';
    }

    function cancelSelectMode() {
        const selectHeader = document.getElementById('selectHeader');
        if (selectHeader) selectHeader.style.display = 'table-row'; // Force toggle back
        toggleSelectMode();
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    }

    function toggleSelectAll(source) {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = source.checked);
    }

    function printSelected() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.closest('tr').dataset.canvassId);
        if (selected.length === 0) return alert('Please select at least one record.');

        // Open window synchronously to avoid popup blockers
        const printWindow = window.open('', '_blank');
        if (printWindow) {
            printWindow.document.write('<div style="font-family: sans-serif; padding: 20px; text-align: center;"><h2>Preparing Document...</h2><p>Please wait while we gather the details.</p></div>');
        } else {
            alert('Popup blocker prevented printing. Please allow popups for this site.');
            return;
        }

        // Use the detailed printing logic
        const loadingMessage = document.createElement('div');
        loadingMessage.innerHTML = `<div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 10000; text-align: center;">
            <p><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #073b1d;"></i></p>
            <p style="margin-top: 15px; font-weight: 600;">Preparing print document...</p>
        </div>`;
        document.body.appendChild(loadingMessage);

        const fetchPromises = selected.map(id => fetch(`../actions/get_canvass_details.php?id=${id}`).then(res => res.json()));

        Promise.all(fetchPromises).then(results => {
            document.body.removeChild(loadingMessage);
            generatePrintView(results.filter(r => r.success), printWindow);
        }).catch(err => {
            document.body.removeChild(loadingMessage);
            printWindow.close();
            alert('Error loading details: ' + err.message);
        });
    }

    function printCanvassDetails(id) {
        if (!id) {
            // If called without ID, try to use currentCanvassId
            id = currentCanvassId;
        }
        if (!id) return;
        
        // Open window synchronously to avoid popup blockers
        const printWindow = window.open('', '_blank');
        if (printWindow) {
            printWindow.document.write('<div style="font-family: sans-serif; padding: 20px; text-align: center;"><h2>Preparing Document...</h2><p>Please wait while we gather the details.</p></div>');
        } else {
            alert('Popup blocker prevented printing. Please allow popups for this site.');
            return;
        }

        fetch(`../actions/get_canvass_details.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    generatePrintView([data], printWindow);
                } else {
                    printWindow.close();
                    alert('Failed to load details.');
                }
            })
            .catch(err => {
                printWindow.close();
                alert('Error loading details: ' + err.message);
            });
    }

    function generatePrintView(canvassData, printWindow) {
        let printContent = '<div class="print-container">';
        canvassData.forEach((data, index) => {
            const canvass = data.canvass;
            const items = data.items;
            let itemsHtml = '';
            let total = 0;

            items.forEach(item => {
                itemsHtml += `<tr><td>${item.supplier_name}</td><td>${item.department || ''}</td><td>${item.campus || ''}</td><td>${item.item_description}</td><td style="text-align:right">${parseFloat(item.quantity).toFixed(2)}</td><td style="text-align:right">₱${parseFloat(item.unit_cost).toFixed(2)}</td><td style="text-align:right">₱${parseFloat(item.total_cost).toFixed(2)}</td></tr>`;
                total += parseFloat(item.total_cost);
            });

            printContent += `
                <div class="canvass-detail-section" style="${index > 0 ? 'page-break-before: always; margin-top: 30px;' : ''}">
                    <div style="text-align:center; border-bottom:2px solid #333; margin-bottom:20px; padding-bottom:10px">
                        <h2>CANVASS RECORD #${canvass.canvass_id}</h2>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:20px">
                        <div>
                            <p><strong>Date:</strong> ${new Date(canvass.canvass_date).toLocaleDateString()}</p>
                            <p><strong>Status:</strong> ${canvass.status}</p>
                        </div>
                        <div style="text-align:right">
                            <p><strong>Created By:</strong> ${canvass.created_by_name || 'System'}</p>
                            <p><strong>Total:</strong> ₱${parseFloat(canvass.total_amount).toFixed(2)}</p>
                        </div>
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:11px">
                        <thead style="background:#f5f5f5">
                            <tr><th>Supplier</th><th>Dept</th><th>Campus</th><th>Description</th><th style="text-align:right">Qty</th><th style="text-align:right">Unit</th><th style="text-align:right">Total</th></tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                            <tr style="font-weight:bold; background:#eee"><td colspan="6" style="text-align:right">GRAND TOTAL:</td><td style="text-align:right">₱${total.toFixed(2)}</td></tr>
                        </tbody>
                    </table>
                    ${canvass.notes ? `<div style="margin-top:20px; padding:10px; background:#f9f9f9; border:1px solid #ddd"><strong>Notes:</strong><p>${canvass.notes}</p></div>` : ''}
                </div>`;
        });
        printContent += '</div>';

        if (!printWindow) {
            printWindow = window.open('', '_blank');
        }
        
        if (printWindow) {
            printWindow.document.open();
            printWindow.document.write(`
                <html><head><title>Print Canvass</title><style>
                    body { font-family: sans-serif; padding: 20px; }
                    table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    @media print { .no-print { display: none; } }
                </style></head><body>${printContent}<script>
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 500);
                <\/script></body></html>
            `);
            printWindow.document.close();
        }
    }

    function editItem(btn) {
        const row = btn.closest('tr');
        const supplier = row.querySelector('.supplier_name').innerText;
        const department = row.querySelector('.department').innerText;
        const campus = row.querySelector('.campus').innerText;
        const description = row.querySelector('.item_description').innerText;
        const quantity = row.querySelector('.quantity').innerText;
        const unitCost = row.querySelector('.unit_cost').innerText.replace(/[^\d.-]/g, '');

        let supplierOptions = availableSuppliers.map(s => `<option value="${s}" ${s === supplier ? 'selected' : ''}>${s}</option>`).join('');
        row.querySelector('.supplier_name').innerHTML = `<select class="form-control form-control-sm">${supplierOptions}</select>`;

        let deptOptions = '<option value="">Select Dept</option>' + availableDepartments.map(d => `<option value="${d}" ${d === department ? 'selected' : ''}>${d}</option>`).join('');
        row.querySelector('.department').innerHTML = `<select class="form-control form-control-sm">${deptOptions}</select>`;

        let campusOptions = '<option value="">Select Campus</option>' + availableCampuses.map(c => `<option value="${c}" ${c === campus ? 'selected' : ''}>${c}</option>`).join('');
        row.querySelector('.campus').innerHTML = `<select class="form-control form-control-sm">${campusOptions}</select>`;

        row.querySelector('.item_description').innerHTML = `<input type="text" class="form-control form-control-sm" value="${description}">`;
        row.querySelector('.quantity').innerHTML = `<input type="number" class="form-control form-control-sm" value="${quantity}" step="0.01">`;
        row.querySelector('.unit_cost').innerHTML = `<input type="number" class="form-control form-control-sm" value="${unitCost}" step="0.01">`;

        const actionCell = row.querySelector('td:last-child');
        actionCell.innerHTML = `
            <div class="flex gap-1">
                <button class="bg-green-50 text-green-600 hover:bg-green-100 p-1.5 rounded-lg transition-all" onclick="saveItem(this)">
                    <i class="fas fa-save text-xs"></i>
                </button>
                <button class="bg-slate-50 text-slate-600 hover:bg-slate-100 p-1.5 rounded-lg transition-all" onclick="viewCanvass(currentCanvassId)">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
    }

    function saveItem(btn) {
        const row = btn.closest('tr');
        const canvassItemId = row.getAttribute('data-id');
        const data = {
            canvass_id: currentCanvassId,
            canvass_item_id: canvassItemId,
            supplier_name: row.querySelector('.supplier_name select').value,
            department: row.querySelector('.department select').value,
            campus: row.querySelector('.campus select').value,
            item_description: row.querySelector('.item_description input').value,
            quantity: row.querySelector('.quantity input').value,
            unit_cost: row.querySelector('.unit_cost input').value
        };

        fetch('../actions/update_canvass_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) viewCanvass(currentCanvassId);
                else alert('Update failed: ' + data.message);
            });
    }

    function deleteCanvassItem(itemId) {
        if (!confirm('Delete this item?')) return;
        fetch('../actions/delete_canvass_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    canvass_item_id: itemId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) viewCanvass(currentCanvassId);
                else alert('Delete failed: ' + data.message);
            });
    }

    /**
     * Text highlighter function to draw attention to matching search terms (GEMINI.md rule UX Enhancement)
     */
    function highlightSearchText(query) {
        if (!query) return;
        const tableBody = document.getElementById('canvass-table-body');
        if (!tableBody) return;
        
        // Escape special regex characters
        const escapedQuery = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        
        function traverseAndHighlight(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.nodeValue;
                if (regex.test(text)) {
                    const span = document.createElement('span');
                    span.innerHTML = text.replace(regex, '<mark style="background-color: rgba(234, 202, 38, 0.3); color: #073b1d; padding: 1px 3px; border-radius: 3px; font-weight: 600;">$1</mark>');
                    node.parentNode.replaceChild(span, node);
                }
            } else if (node.nodeType === Node.ELEMENT_NODE && node.nodeName !== 'SCRIPT' && node.nodeName !== 'STYLE' && node.nodeName !== 'BUTTON' && !node.classList.contains('status-badge') && !node.classList.contains('badge')) {
                const children = Array.from(node.childNodes);
                children.forEach(child => traverseAndHighlight(child));
            }
        }
        
        traverseAndHighlight(tableBody);
    }

    /**
     * AJAX Pagination & Search Handler
     * Prevents page reloading and updates only the list table and pagination wrapper
     */
    function loadPage(page) {
        // Build relative URL to ensure perfect adaptability to any online environment
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        url.searchParams.set('page', page);
        
        const searchInput = document.getElementById('table-search-input');
        const q = searchInput ? searchInput.value.trim() : '';
        if (q) {
            url.searchParams.set('q', q);
        } else {
            url.searchParams.delete('q');
        }

        // Show loading indicator during search (GEMINI.md rule UI/UX Standard)
        const searchLoader = document.getElementById('search-loading-indicator');
        if (searchLoader) searchLoader.style.display = 'flex';

        // Visual feedback during AJAX loading
        const tableBody = document.getElementById('canvass-table-body');
        if (tableBody) {
            tableBody.style.opacity = '0.5';
            tableBody.style.transition = 'opacity 0.15s ease';
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update table body content dynamically
                if (tableBody) {
                    tableBody.innerHTML = data.rows;
                    tableBody.style.opacity = '1';
                }

                // Update pagination controls wrapper
                const paginationWrapper = document.getElementById('pagination-wrapper');
                if (paginationWrapper) {
                    if (data.total_pages > 1) {
                        paginationWrapper.innerHTML = `<div class="pagination-container">${data.pagination}</div>`;
                    } else {
                        paginationWrapper.innerHTML = '';
                    }
                }

                // Update browser URL state without reloading (Option A)
                const pushUrl = new URL(window.location.href);
                pushUrl.searchParams.set('page', page);
                if (q) {
                    pushUrl.searchParams.set('q', q);
                } else {
                    pushUrl.searchParams.delete('q');
                }
                window.history.pushState({ page: page, q: q }, '', pushUrl.toString());

                // If checkbox selection mode is active, make sure rows adjust
                const selectHeader = document.getElementById('selectHeader');
                const isSelectionMode = selectHeader && selectHeader.style.display !== 'none';
                if (isSelectionMode) {
                    const selectCells = document.querySelectorAll('.select-cell');
                    if (selectCells) {
                        selectCells.forEach(cell => cell.style.display = 'table-cell');
                    }
                }

                // Highlight search text after rendering rows (GEMINI.md rule UX Enhancement)
                if (q) {
                    highlightSearchText(q);
                }
            } else {
                showPageError(data.message || 'Failed to load page content.');
                if (tableBody) tableBody.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('AJAX pagination error:', error);
            showPageError('Failed to fetch page. Please check your network connection.');
            if (tableBody) tableBody.style.opacity = '1';
        })
        .finally(() => {
            if (searchLoader) searchLoader.style.display = 'none';
        });
    }

    // Attach click listeners to pagination links dynamically using Event Delegation
    document.addEventListener('DOMContentLoaded', () => {
        const paginationWrapper = document.getElementById('pagination-wrapper');
        if (paginationWrapper) {
            paginationWrapper.addEventListener('click', (e) => {
                const link = e.target.closest('.page-link');
                if (link) {
                    e.preventDefault();
                    
                    const item = link.closest('.page-item');
                    if (item && (item.classList.contains('disabled') || item.classList.contains('active'))) {
                        return;
                    }

                    const page = link.getAttribute('data-page');
                    if (page) {
                        loadPage(page);
                    }
                }
            });
        }

        // Handle back/forward browser buttons seamlessly
        window.addEventListener('popstate', (e) => {
            const page = (e.state && e.state.page) ? e.state.page : 1;
            const q = (e.state && e.state.q !== undefined) ? e.state.q : '';
            const searchInput = document.getElementById('table-search-input');
            if (searchInput) {
                searchInput.value = q;
            }
            const clearSearchBtn = document.getElementById('clear-search-btn');
            if (clearSearchBtn) {
                clearSearchBtn.style.display = q ? 'inline-block' : 'none';
            }
            loadPage(page);
        });
        
        // Save initial state to history for back/forward support
        const urlParams = new URLSearchParams(window.location.search);
        const initialPage = urlParams.get('page') || 1;
        const initialQ = urlParams.get('q') || '';
        window.history.replaceState({ page: initialPage, q: initialQ }, '', window.location.href);

        // Search input event listener with 400ms debounce (GEMINI.md Search behavior rules)
        const searchInput = document.getElementById('table-search-input');
        const clearSearchBtn = document.getElementById('clear-search-btn');
        let searchTimeout = null;

        if (searchInput) {
            // Apply initial highlighting if page was loaded with a query parameter
            const initialQuery = searchInput.value.trim();
            if (initialQuery) {
                highlightSearchText(initialQuery);
            }

            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                
                // Show/hide clear button (GEMINI.md rule UX Enhancement)
                if (clearSearchBtn) {
                    clearSearchBtn.style.display = query ? 'inline-block' : 'none';
                }

                // Debounce search
                clearTimeout(searchTimeout);
                
                // Show loading indicator instantly for active response feel
                const searchLoader = document.getElementById('search-loading-indicator');
                if (searchLoader) searchLoader.style.display = 'flex';

                searchTimeout = setTimeout(() => {
                    loadPage(1); // Search resets to page 1
                }, 400);
            });
        }

        // Clear search click listener
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                    clearSearchBtn.style.display = 'none';
                    loadPage(1);
                }
            });
        }
    });
</script>


<style>
    :root {
        --primary-green: #073b1d;
        --dark-green: #073b1d;
        --light-green: #2d8aad;
        --accent-orange: #EACA26;
        --accent-blue: #4a90e2;
        --accent-green-approved: #28a745;
        --accent-red: #e74c3c;
        --text-white: #ffffff;
        --text-dark: #073b1d;
        --bg-light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--bg-light);
        margin: 0;
        padding: 0;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 240px;
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        z-index: 1000;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header h4 {
        margin: 0;
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--text-white);
    }

    .welcome-text {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-top: 5px;
    }

    .sidebar-nav {
        padding: 20px 0;
    }

    .sidebar-nav ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .nav-item {
        padding: 0;
        margin: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        color: var(--text-white);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        font-size: 0.85rem;
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--text-white);
        border-left-color: var(--accent-orange);
    }

    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.15);
        border-left-color: var(--accent-orange);
        font-weight: 600;
    }

    .nav-link i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
    }

    .nav-link.logout {
        color: var(--accent-red);
        margin-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Main Content */
    .main-content {
        margin-left: 280px;
        padding: 20px;
        min-height: 100vh;
        background-color: var(--bg-light);
    }

    .content-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .content-header h1 {
        margin: 0;
        font-weight: 700;
        font-size: 2.2rem;
    }

    /* List Container */
    .list-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .list-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background-color: var(--accent-orange);
        color: var(--text-white);
    }

    .btn-primary:hover {
        background-color: #e8690b;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: var(--text-white);
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    /* Table Styles */
    .canvass-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .canvass-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--primary-green);
    }

    .canvass-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .canvass-table tbody tr:hover {
        background-color: rgba(7, 59, 29, 0.05);
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-draft,
    .status-canvassed {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }

    .status-completed {
        background: linear-gradient(135deg, var(--accent-blue), #357abd);
        color: white;
    }

    .status-approved {
        background: linear-gradient(135deg, var(--accent-green-approved), #1e7e34);
        color: white;
    }

    .status-cancelled {
        background: linear-gradient(135deg, var(--accent-red), #c82333);
        color: white;
    }

    /* Action Buttons in Table */
    .table-actions {
        display: flex;
        gap: 8px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .btn-info {
        background-color: var(--accent-blue);
        color: white;
    }

    .btn-info:hover {
        background-color: #357abd;
    }

    .btn-warning {
        background-color: var(--accent-orange);
        color: white;
    }

    .btn-warning:hover {
        background-color: #e8690b;
    }

    .btn-danger {
        background-color: var(--accent-red);
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 15px;
        }

        .list-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .canvass-table {
            font-size: 0.9rem;
        }

        .canvass-table th,
        .canvass-table td {
            padding: 10px 8px;
        }

        .table-actions {
            flex-direction: column;
            gap: 5px;
        }
    }

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: center;
        padding: 20px;
        background: #fff;
        border-top: 1px solid #eee;
    }

    /* Stable height prevents layout shift when rows are swapped via AJAX */
    #canvass-table-body {
        min-height: 520px;
    }

    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 5px;
    }

    .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 15px;
        text-decoration: none;
        color: var(--primary-green);
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .page-item.active .page-link {
        background-color: var(--primary-green);
        color: #fff;
        border-color: var(--primary-green);
    }

    .page-item.disabled .page-link {
        color: #ccc;
        pointer-events: none;
        background: #f9f9f9;
    }

    .page-item .page-link:hover:not(.active):not(.disabled) {
        background-color: #f0f0f0;
        border-color: #ccc;
    }

    @media (max-width: 768px) {
        .page-item .page-link {
            min-width: 50px;
            height: 50px;
            font-size: 1rem;
        }
    }

    /* Search Bar Styles */
    .search-bar-container {
        padding: 15px 30px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .search-input-wrapper {
        position: relative;
        flex-grow: 1;
        display: flex;
        align-items: center;
    }

    .search-input-wrapper .search-icon {
        position: absolute;
        left: 15px;
        color: #adb5bd;
        font-size: 1rem;
        pointer-events: none;
    }

    .search-input-wrapper input {
        width: 100%;
        padding: 12px 40px 12px 45px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        color: var(--text-dark);
        background-color: #fff;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .search-input-wrapper input:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(7, 59, 29, 0.15);
        outline: none;
    }

    .search-input-wrapper #clear-search-btn {
        position: absolute;
        right: 15px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 1rem;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .search-input-wrapper #clear-search-btn:hover {
        background-color: #f1f5f9;
        color: var(--accent-red);
    }

    .search-loader {
        color: var(--primary-green);
        font-size: 1.2rem;
        display: flex;
        align-items: center;
    }

    @media (max-width: 768px) {
        .search-bar-container {
            padding: 15px;
        }
        .search-input-wrapper input {
            padding: 10px 35px 10px 40px;
            font-size: 0.9rem;
        }
    }
</style>




<!-- Sidebar -->
<?php include '../includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Error Message Container -->
    <div id="pageErrorContainer" style="display: none; margin: 20px;"></div>

    <div class="content-header">
        <h1>Canvass List</h1>
        <p>View and manage all canvass records</p>
    </div>

    <!-- Canvass List -->
    <div class="list-container">
        <div class="list-header">
            <h2 class="list-title">All Canvass Records</h2>
            <div class="table-actions">
                <?php if (!in_array($user_role_norm, ['propertycustodian', 'supplyincharge'])): ?>
                    <a href="canvass_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Canvass
                    </a>
                    <button id="selectForPrintBtn" class="btn btn-info" onclick="toggleSelectMode()">
                        <i class="fas fa-print"></i> Select for Print
                    </button>
                    <button id="printSelectedBtn" class="btn btn-success" onclick="printSelected()" style="display: none;">
                        <i class="fas fa-print"></i> Print Selected
                    </button>
                    <button id="cancelSelectBtn" class="btn btn-secondary" onclick="cancelSelectMode()" style="display: none;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Bar Section (GEMINI.md compliant modern search bar) -->
        <div class="search-bar-container">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="table-search-input" placeholder="Search by supplier, items, department, campus, status or creator..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                <button type="button" id="clear-search-btn" style="display: <?= $search !== '' ? 'inline-block' : 'none' ?>;" title="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="search-loading-indicator" class="search-loader" style="display: none;">
                <i class="fas fa-circle-notch fa-spin"></i>
            </div>
        </div>

        <?php if ($total_rows > 0 || $search !== ''): ?>
            <table class="canvass-table">
                <thead>
                    <tr id="selectHeader" style="display: none;">
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        </th>
                        <th colspan="10">Select All to print</th>
                    </tr>
                    <tr>
                        <th id="selectHeaderCell" style="display: none; width: 40px;"></th>
                        <th>Supplier Name</th>
                        <th>Department</th>
                        <th>Campus</th>
                        <th>Canvass Description</th>
                        <th>Total Amount</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="canvass-table-body">
                    <?= get_table_rows_html($canvass_result, $user_role_norm, $search) ?>
                </tbody>
            </table>

            <!-- Pagination UI Wrapper -->
            <div id="pagination-wrapper">
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <?= get_pagination_html($current_page_num, $total_pages, $search) ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No Canvass Records Found</h3>
                <p>Start by creating your first canvass form.</p>
                <?php if (!in_array($user_role_norm, ['propertycustodian', 'supplyincharge'])): ?>
                    <a href="canvass_form.php" class="btn btn-primary text-dark">
                        <i class="fas fa-plus"></i> Create New Canvass
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="modal fade" id="viewCanvassModal" tabindex="-1" aria-labelledby="viewCanvassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <div class="modal-header bg-slate-800 text-white border-0 py-6 px-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-black text-xl tracking-tight leading-none" id="viewCanvassModalLabel">Canvass Details</h5>
                            <p class="text-white/60 text-[10px] uppercase font-bold tracking-widest mt-1">Detailed Record Preview</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white opacity-50 hover:opacity-100 transition-opacity" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="canvassDetailsContent">
                    <div class="p-20 text-center text-slate-400">
                        <div class="animate-spin inline-block w-8 h-8 border-4 border-current border-t-transparent text-primary rounded-full mb-4" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="font-bold text-sm uppercase tracking-widest">Fetching details...</p>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-0 py-4 px-8 flex justify-between items-center">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">DARTS Procurement System</p>
                    <button type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2.5 rounded-xl font-bold transition-all text-sm" data-bs-dismiss="modal">Close Preview</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>