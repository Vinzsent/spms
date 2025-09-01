<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/header.php';

// Fetch subcategories for this parent
$parent_id = intval($_GET['id'] ?? 0);
if ($parent_id <= 0) {
    echo '<div class="alert alert-danger">Invalid category.</div>';
    exit;
}

// Get parent name
$parent_res = mysqli_query($conn, "SELECT name FROM account_types WHERE id = $parent_id");
$parent_row = mysqli_fetch_assoc($parent_res);
$parent_name = $parent_row['name'] ?? 'Unknown';

// Get subcategories under this parent
$subcategories_query = "SELECT * FROM account_subcategories WHERE parent_id = $parent_id ORDER BY name DESC";
$subcategories_result = mysqli_query($conn, $subcategories_query);
?>

<!-- Theme -->
<link rel="stylesheet" href="../assets/css/category-theme.css">

<div class="page-container">
    <div class="page-hero">
        <h2>Subcategories</h2>
        <div class="subtitle">Manage main subcategories under <strong><?php echo htmlspecialchars($parent_name); ?></strong></div>
    </div>

    <!-- Add New Subcategory Form -->
    <div class="card card-modern mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-plus"></i> Add New Main Subcategory</h6>
        </div>
        <div class="card-body">
            <div class="mb-2">Main Category: <strong><?php echo htmlspecialchars($parent_name); ?></strong></div>
            <form class="subcategory-form" method="post">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" name="subcategory_name" class="form-control" placeholder="Enter subcategory name" required>
                    </div>
                    <div class="col-md-4 d-flex justify-content-start">
                        <button type="submit" class="btn btn-modern btn-accent me-2">
                            <i class="fas fa-plus"></i> Add Main Subcategory
                        </button>
                        <a href="assets.php?id=<?php echo $parent_id; ?>&name=<?php echo urlencode($parent_name); ?>"
                            class="btn btn-modern btn-outline-brand">
                            <i class="fas fa-arrow-left"></i> Back to Main Subcategories
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Existing Subcategories -->
    <div class="card card-modern">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Existing Subcategories</h6>
        </div>
        <div class="card-body">
            <?php if ($subcategories_result && mysqli_num_rows($subcategories_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-modern">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Main Subcategory Name</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sub = mysqli_fetch_assoc($subcategories_result)): ?>
                                <tr>
                                    <td><?php echo $sub['id']; ?></td>
                                    <td>
                                        <span class="subcategory-name-<?php echo $sub['id']; ?>">
                                            <?php echo htmlspecialchars($sub['name']); ?>
                                        </span>
                                        <form class="subcategory-form d-none edit-form-<?php echo $sub['id']; ?>" method="post">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="subcategory_id" value="<?php echo $sub['id']; ?>">
                                            <input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="subcategory_name" class="form-control" value="<?php echo htmlspecialchars($sub['name']); ?>" required>
                                                <button type="submit" class="btn btn-brand btn-sm btn-modern">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm btn-modern" onclick="cancelEdit(<?php echo $sub['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-brand me-1 btn-modern" title="Edit subcategory" onclick="editSubcategory(<?php echo $sub['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="add_sub_subcategories.php?id=<?php echo $sub['id']; ?>&name=<?php echo urlencode($sub['name']); ?>"
                                            class="btn btn-sm btn-modern btn-brand me-1"
                                            title="Add subcategory">
                                            <i class="fas fa-plus-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger btn-modern" title="Delete subcategory" onclick="deleteSubcategory(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['name']); ?>', <?php echo $parent_id; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                    <p>No subcategories found. Add one above to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.page-container -->

<!-- Child Subcategories Modal -->
<div class="modal fade" id="childSubModal" tabindex="-1" aria-labelledby="childSubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="childSubModalLabel">Main Subcategory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="childSubContent" class="py-2 text-center text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
    // Submit add/update via AJAX
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('subcategory-form')) {
            e.preventDefault();
            const fd = new FormData(e.target);
            fetch('../actions/subcategory_crud.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(j => {
                    if (j.success) {
                        location.reload();
                    } else {
                        alert(j.message || 'Error');
                    }
                })
                .catch(() => alert('Network error'));
        }
    });

    function editSubcategory(id) {
        document.querySelector('.subcategory-name-' + id).classList.add('d-none');
        document.querySelector('.edit-form-' + id).classList.remove('d-none');
    }

    function cancelEdit(id) {
        document.querySelector('.subcategory-name-' + id).classList.remove('d-none');
        document.querySelector('.edit-form-' + id).classList.add('d-none');
    }

    function deleteSubcategory(id, name, parentId) {
        if (!confirm('Delete "' + name + '"?')) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('subcategory_id', id);
        fd.append('parent_id', parentId);
        fetch('../actions/subcategory_crud.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(j => {
                if (j.success) {
                    location.reload();
                } else {
                    alert(j.message || 'Error');
                }
            })
            .catch(() => alert('Network error'));
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
            .then(html => {
                document.getElementById('childSubContent').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('childSubContent').innerHTML = '<div class="alert alert-danger">Failed to load.</div>';
            });
    }
</script>

<?php include '../includes/footer.php'; ?>