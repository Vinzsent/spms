<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/header.php';

// Fetch account types with subcategory count
$account_names = mysqli_query($conn, "SELECT at.*, COUNT(sc.id) as subcategory_count FROM account_types at LEFT JOIN account_subcategories sc ON at.id = sc.parent_id GROUP BY at.id ORDER BY at.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr"
      crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>DCC-DARTS</title>
    <link rel="stylesheet" href="../assets/css/category-theme.css">
</head>
<body>
<div class="page-container">

    <div class="page-hero">
        <h2>Assets Category</h2>
        <div class="subtitle">Browse main categories and manage their subcategories</div>
    </div>

    <!-- Back button -->
    <div class="mb-3">
        <button type="button" class="btn btn-modern btn-outline-brand" onclick="window.location.href='assets_page.php'">
            <i class="fa-solid fa-arrow-left"></i> Back to Budgets
        </button>
    </div>

    <!-- Table for Account Types -->
    <form action="../actions/assets_category_crud.php" method="post" class="card-modern">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-layer-group me-2"></i>Account Types</span>
        </div>
        <div class="card-body p-0">
        <table class="table table-modern table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 10%" class="text-center">ID</th>
                    <th style="width: 50%" class="text-center">Account Name</th>
                    <th style="width: 15%" class="text-center">Subcategories</th>
                    <th style="width: 25%" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($account_names)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <span class="badge bg-info"><?php echo $row['subcategory_count']; ?> subcategories</span>
                    </td>
                    <td>
                        <a href="load_subcategories.php?id=<?php echo $row['id']; ?>&name=<?php echo urlencode($row['name']); ?>" 
                           class="btn btn-sm btn-modern btn-brand btn-primary me-1">
                            <i class="fas fa-eye"></i> View Subcategories
                        </a>
                        <button type="button" class="btn btn-sm btn-modern btn-outline-brand me-1" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-modern btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>

                <!-- Add new row -->
                <tr class="table-light">
                    <td class="text-center"><strong>New</strong></td>
                    <td><input type="text" name="new_account_name" placeholder="Enter new account type" class="form-control" required></td>
                    <td>-</td>
                    <td><button type="submit" name="add" class="btn btn-sm btn-modern btn-accent"><i class="fas fa-plus"></i> Add Category</button></td>
                </tr>
            </tbody>
        </table>
        </div>
    </form>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../actions/assets_category_crud.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Account Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Account Type Name</label>
                        <input type="text" class="form-control" id="editName" name="account_name" required>
                        <input type="hidden" id="editId" name="id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../actions/assets_category_crud.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the account type "<span id="deleteItemName"></span>"?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> This will also delete all associated subcategories.</p>
                    <input type="hidden" id="deleteId" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subcategories Modal -->
<div class="modal fade" id="subcategoriesModal" tabindex="-1" aria-labelledby="subcategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subcategoriesModalLabel">Manage Subcategories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="subcategoriesContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Child Subcategories Modal -->
<div class="modal fade" id="childSubModal" tabindex="-1" aria-labelledby="childSubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="childSubModalLabel">Manage Child Subcategories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="childSubContent" class="py-2 text-center text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous">
</script>

<script>
    function openEditModal(id, name) {
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteItemName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function openSubcategories(parentId, parentName) {
        document.getElementById('subcategoriesModalLabel').textContent = 'Manage Subcategories for: ' + parentName;
        
        // Show loading
        document.getElementById('subcategoriesContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        
        // Show modal
        new bootstrap.Modal(document.getElementById('subcategoriesModal')).show();
        
        // Load subcategories via AJAX
        fetch('../modals/load_subcategories.php?parent_id=' + parentId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('subcategoriesContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('subcategoriesContent').innerHTML = '<div class="alert alert-danger">Error loading subcategories.</div>';
                console.error('Error:', error);
            });
    }

    // Handle subcategory form submissions via AJAX
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('subcategory-form')) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            fetch('../actions/subcategory_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload subcategories
                    const parentId = formData.get('parent_id');
                    if (parentId) {
                        fetch('../modals/load_subcategories.php?parent_id=' + parentId)
                            .then(response => response.text())
                            .then(data => {
                                document.getElementById('subcategoriesContent').innerHTML = data;
                            });
                    }
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing request.');
            });
        }
    });

    // Subcategory management functions
    function editSubcategory(id) {
        document.querySelector('.subcategory-name-' + id).classList.add('d-none');
        document.querySelector('.edit-form-' + id).classList.remove('d-none');
    }

    function cancelEdit(id) {
        document.querySelector('.subcategory-name-' + id).classList.remove('d-none');
        document.querySelector('.edit-form-' + id).classList.add('d-none');
    }

    function deleteSubcategory(id, name, parentId) {
        if (confirm('Are you sure you want to delete the subcategory "' + name + '"?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('subcategory_id', id);
            formData.append('parent_id', parentId);
            
            fetch('../actions/subcategory_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload subcategories
                    fetch('../modals/load_subcategories.php?parent_id=' + parentId)
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('subcategoriesContent').innerHTML = data;
                        });
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing request.');
            });
        }
    }

    function openChildSubcategories(subcategoryId, subcategoryName) {
        const label = document.getElementById('childSubModalLabel');
        if (label) label.textContent = 'Main Subcategory for: ' + subcategoryName;
        const content = document.getElementById('childSubContent');
        if (content) content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        const modal = new bootstrap.Modal(document.getElementById('childSubModal'));
        modal.show();
        fetch('../modals/load_sub_subcategories.php?subcategory_id=' + subcategoryId)
            .then(r => r.text())
            .then(html => { document.getElementById('childSubContent').innerHTML = html; })
            .catch(() => { document.getElementById('childSubContent').innerHTML = '<div class="alert alert-danger">Failed to load.</div>'; });
    }
</script>
</body>
</html>
