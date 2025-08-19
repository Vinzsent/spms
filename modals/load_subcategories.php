<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/header.php';

if (!isset($_GET['parent_id'])) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit;
}

$parent_id = intval($_GET['parent_id']);

// Fetch subcategories for this parent
$subcategories_query = "SELECT * FROM account_subcategories WHERE parent_id = $parent_id ORDER BY name";
$subcategories_result = mysqli_query($conn, $subcategories_query);

// Get parent name
$parent_query = "SELECT name FROM account_types WHERE id = $parent_id";
$parent_result = mysqli_query($conn, $parent_query);
$parent_name = mysqli_fetch_assoc($parent_result)['name'] ?? 'Unknown';
?>

<div class="mb-3">
    <h6>Parent Category: <strong><?php echo htmlspecialchars($parent_name); ?></strong></h6>
</div>

<!-- Add New Subcategory Form -->
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-plus"></i> Add New Subcategory</h6>
    </div>
    <div class="card-body">
        <form class="subcategory-form" method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" name="subcategory_name" class="form-control" placeholder="Enter subcategory name" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Subcategory
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Existing Subcategories -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-list"></i> Existing Subcategories</h6>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($subcategories_result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Subcategory Name</th>
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
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(<?php echo $sub['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editSubcategory(<?php echo $sub['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success me-1" title="Manage Children" onclick="openChildSubcategories(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['name']); ?>')">
                                    <i class="fas fa-plus-square"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSubcategory(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['name']); ?>', <?php echo $parent_id; ?>)">
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

