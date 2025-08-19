<?php
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

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
</head>
<style>
    .container {
        width: 70%;
        margin: 80px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background: #fdfdfd;
    }
    .text-center {
        text-align: center;
    }
</style>
<body>
<div class="container">

    <h1 class="text-center mb-4">Assets Category</h1>

    <!-- Back button -->
    <div class="mb-3">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='../dashboard.php'">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
    </div>

    <!-- Table for Account Types -->
    <form action="../actions/assets_category_crud.php" method="post">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 50%">Account Name</th>
                    <th style="width: 15%">Subcategories</th>
                    <th style="width: 25%">Action</th>
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
                        <button type="button" class="btn btn-sm btn-primary me-1" onclick="openSubcategories(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                            <i class="fas fa-list"></i> Subcategories
                        </button>
                        <button type="button" class="btn btn-sm btn-success me-1" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger me-1" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>

                <!-- Add new row -->
                <tr class="table-light">
                    <td><strong>New</strong></td>
                    <td><input type="text" name="new_account_name" placeholder="Enter new account type" class="form-control" required></td>
                    <td>-</td>
                    <td><button type="submit" name="add" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add Category</button></td>
                </tr>
            </tbody>
        </table>
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
</script>
</body>
</html>
