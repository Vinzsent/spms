<?php
include '../includes/db.php';
include '../includes/auth.php';

$subcategory_id = intval($_GET['subcategory_id'] ?? 0);
if ($subcategory_id <= 0) {
    echo '<div class="alert alert-danger">Invalid subcategory specified.</div>';
    exit;
}

// Fetch parent subcategory name
$subRes = mysqli_query($conn, "SELECT name FROM account_subcategories WHERE id = $subcategory_id");
$subRow = mysqli_fetch_assoc($subRes);
$parent_sub_name = $subRow['name'] ?? 'Unknown';

// Fetch child subcategories
$children = mysqli_query($conn, "SELECT * FROM account_sub_subcategories WHERE subcategory_id = $subcategory_id ORDER BY name");
?>

<div class="mb-2">
  <h6>Main Subcategory: <strong><?php echo htmlspecialchars($parent_sub_name); ?></strong></h6>
</div>

<div class="card mb-3">
  <div class="card-header">
    <h6 class="mb-0"><i class="fas fa-plus"></i> Subcategory</h6>
  </div>
  <div class="card-body">
    <form class="child-subcategory-form" method="post" onsubmit="submitChildForm(event)">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="subcategory_id" value="<?php echo $subcategory_id; ?>">
      <div class="row g-2">
        <div class="col-md-8">
          <input class="form-control" type="text" name="child_name" placeholder="Enter subcategory name" required>
        </div>
        <div class="col-md-4">
          <button class="btn btn-success" type="submit"><i class="fas fa-plus"></i> Add Subcategory</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h6 class="mb-0"><i class="fas fa-list"></i> List of Existing Accounts</h6>
  </div>
  <div class="card-body">
    <?php if (mysqli_num_rows($children) > 0): ?>
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($c = mysqli_fetch_assoc($children)): ?>
            <tr>
              <td><?php echo $c['id']; ?></td>
              <td>
                <span class="child-name-<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></span>
                <form class="d-none child-edit-form-<?php echo $c['id']; ?>" onsubmit="submitChildEdit(event, <?php echo $c['id']; ?>)">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="child_id" value="<?php echo $c['id']; ?>">
                  <input type="hidden" name="subcategory_id" value="<?php echo $subcategory_id; ?>">
                  <div class="input-group input-group-sm">
                    <input type="text" name="child_name" class="form-control" value="<?php echo htmlspecialchars($c['name']); ?>" required>
                    <button class="btn btn-success" type="submit"><i class="fas fa-check"></i></button>
                    <button class="btn btn-secondary" type="button" onclick="cancelChildEdit(<?php echo $c['id']; ?>)"><i class="fas fa-times"></i></button>
                  </div>
                </form>
              </td>
              <td><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="editChild(<?php echo $c['id']; ?>)"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteChild(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['name']); ?>')"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-center text-muted py-3">
        <i class="fas fa-folder-open fa-2x mb-2"></i>
        <p>No child subcategories yet.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function reloadChildList() {
  const id = <?php echo $subcategory_id; ?>;
  fetch('../modals/load_sub_subcategories.php?subcategory_id=' + id)
    .then(r => r.text())
    .then(html => { document.getElementById('childSubContent').innerHTML = html; });
}

window.submitChildForm = function(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  fetch('../actions/sub_subcategory_crud.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(j => { 
      if (j.success) { 
        form.reset(); // Clear the form
        reloadChildList(); 
        alert('Subcategory added successfully!');
      } else { 
        alert(j.message||'Error adding subcategory'); 
      } 
    })
    .catch(err => {
      console.error('Error:', err);
      alert('Network error occurred');
    });
}

window.editChild = function(id) {
  document.querySelector('.child-name-' + id).classList.add('d-none');
  document.querySelector('.child-edit-form-' + id).classList.remove('d-none');
}

window.cancelChildEdit = function(id) {
  document.querySelector('.child-name-' + id).classList.remove('d-none');
  document.querySelector('.child-edit-form-' + id).classList.add('d-none');
}

window.submitChildEdit = function(e, id) {
  e.preventDefault();
  const fd = new FormData(e.target);
  fetch('../actions/sub_subcategory_crud.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(j => { if (j.success) { reloadChildList(); } else { alert(j.message||'Error'); } });
}

window.deleteChild = function(id, name) {
  if (!confirm('Delete "' + name + '"?')) return;
  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('child_id', id);
  fd.append('subcategory_id', <?php echo $subcategory_id; ?>);
  fetch('../actions/sub_subcategory_crud.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(j => { if (j.success) { reloadChildList(); } else { alert(j.message||'Error'); } });
}
</script>
