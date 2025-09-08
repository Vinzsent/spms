<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/header.php';

// Get data from URL
$rid = isset($_GET['id']) ? $_GET['id'] : '';
$jname = isset($_GET['name']) ? $_GET['name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subcategory Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/category-theme.css">
</head>
<body class="p-0">

<div class="page-container">
    <div class="page-hero">
        <h2>Sub Sub Subcategories</h2>
        <div class="subtitle">Manage third-level subcategories under <strong><?php echo htmlspecialchars($jname); ?></strong></div>
    </div>

    <div class="card card-modern p-0">
        <table class="table table-modern table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Subcategory Name</th>
                    <th class="text-center">Created</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch existing subcategories
                $safeId = intval($rid);
                $query = "SELECT * FROM account_sub_sub_subcategories WHERE sub_subcategory_id = {$safeId} ORDER BY name";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $rid = (int)$row['id'];
                        $rname = htmlspecialchars($row['name']);
                        $rc = htmlspecialchars($row['created_at']);
                        $jname = json_encode($row['name']); // safe for JS string literal
                        echo "<tr>
                            <td class='text-center'>{$rid}</td>
                            <td>
                                <span class='child-name-{$rid}'>{$rname}</span>
                                <form class='child-form d-none edit-form-{$rid}' method='post'>
                                    <input type='hidden' name='action' value='update'>
                                    <input type='hidden' name='sub_subcategory_id' value='{$safeId}'>
                                    <input type='hidden' name='grandchild_id' value='{$rid}'>
                                    <div class='input-group input-group-sm'>
                                        <input type='text' name='grandchild_name' class='form-control' value='{$rname}' required>
                                        <button type='submit' class='btn btn-brand btn-sm btn-modern' style='color: white;'><i class='fas fa-check'></i></button>
                                        <button type='button' class='btn btn-secondary btn-sm btn-modern' onclick='cancelChild({$rid})' style='color: white;'><i class='fas fa-times'></i></button>
                                    </div>
                                </form>
                            </td>
                            <td class='text-center'>{$rc}</td>
                            <td class='text-center'>
                                <button type='button' class='btn btn-sm btn-outline-brand me-1 btn-modern' title='Edit' onclick='editChild({$rid})'>Edit</button>
                                <button type='button' class='btn btn-sm btn-danger btn-modern' title='Delete' onclick='deleteChild({$rid}, {$jname})'>Delete</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>No sub subcategories found. After you add subcategory it will display here.</td></tr>";
                }
                ?>

                <!-- ✅ Row for Adding a New Subcategory -->
                <tr>
                    <form class="child-form" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="sub_subcategory_id" value="<?php echo intval($safeId); ?>">
                        <td class="text-center">New</td>
                        <td>
                            <input type="text" name="grandchild_name" class="form-control" placeholder="Enter subcategory name" required>
                        </td>
                        <td class="text-center">Auto</td>
                        <td class="text-center">
                            <button type="submit" class="btn btn-brand btn-sm btn-modern">Add</button>
                        </td>
                    </form>
                </tr>
            </tbody>
        </table>

        <a href="javascript:history.back()" class="btn btn-modern btn-outline-brand mt-3">
            ⬅ Go Back to the Previous Page
        </a>
    </div>
</div>

<script>
// Submit add/update via AJAX
document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('child-form')) {
        e.preventDefault();
        const fd = new FormData(e.target);
        fetch('../actions/sub_sub_subcategory_crud.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(j => {
                if (j.success) { location.reload(); }
                else { alert(j.message || 'Error'); }
            })
            .catch(() => alert('Network error'));
    }
});

function editChild(id) {
    const nameSpan = document.querySelector('.child-name-' + id);
    const form = document.querySelector('.edit-form-' + id);
    if (nameSpan && form) {
        nameSpan.classList.add('d-none');
        form.classList.remove('d-none');
    }
}

function cancelChild(id) {
    const nameSpan = document.querySelector('.child-name-' + id);
    const form = document.querySelector('.edit-form-' + id);
    if (nameSpan && form) {
        nameSpan.classList.remove('d-none');
        form.classList.add('d-none');
    }
}

function deleteChild(childId, name) {
    if (!confirm('Delete "' + name + '"?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('sub_subcategory_id', '<?php echo intval($safeId); ?>');
    fd.append('grandchild_id', childId);
    fetch('../actions/sub_sub_subcategory_crud.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(j => { if (j.success) location.reload(); else alert(j.message || 'Error'); })
        .catch(() => alert('Network error'));
}
</script>


</body>
</html>
