<?php
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/header.php';

// Get data from URL
$id = isset($_GET['id']) ? $_GET['id'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';

// Determine parent category for back link
$backParentId = 0;
$backParentName = '';
if ($id !== '') {
    $subId = intval($id);
    $res = mysqli_query($conn, "SELECT parent_id FROM account_subcategories WHERE id = {$subId} LIMIT 1");
    // If no matching subcategory, this id is likely a main category id; redirect appropriately
    if (!$res || mysqli_num_rows($res) === 0) {
        header('Location: load_subcategories.php?id=' . urlencode($subId) . '&name=' . urlencode($name));
        exit;
    }
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $backParentId = intval($row['parent_id']);
        $pres = mysqli_query($conn, "SELECT name FROM account_types WHERE id = {$backParentId} LIMIT 1");
        if ($pres && ($prow = mysqli_fetch_assoc($pres))) {
            $backParentName = $prow['name'];
        }
    }
}
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
        <h2>Sub Subcategories</h2>
        <div class="subtitle">Manage sub subcategories under <strong><?php echo htmlspecialchars($name); ?></strong></div>
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
                $safeId = intval($id);
                $query = "SELECT * FROM account_sub_subcategories WHERE subcategory_id = {$safeId} ORDER BY name DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $rid = (int)$row['id'];
                        $rname = htmlspecialchars($row['name']);
                        $rc = htmlspecialchars($row['created_at']);
                        $jname = json_encode($row['name']); // safe for JS string literal
                        $urlName = urlencode($row['name']);
                        echo "<tr>
                            <td class='text-center'>{$rid}</td>
                            <td>
                                <span class='child-name-{$rid}'>{$rname}</span>
                                <form class='child-form d-none edit-form-{$rid}' method='post'>
                                    <input type='hidden' name='action' value='update'>
                                    <input type='hidden' name='subcategory_id' value='{$safeId}'>
                                    <input type='hidden' name='child_id' value='{$rid}'>
                                    <div class='input-group input-group-sm'>
                                        <input type='text' name='child_name' class='form-control' value='{$rname}' required>
                                        <button type='submit' class='btn btn-brand btn-sm btn-modern' style='color: white;'><i class='fas fa-check'></i></button>
                                        <button type='button' class='btn btn-secondary btn-sm btn-modern' onclick='cancelChild({$rid})' style='color: white;'><i class='fas fa-times'></i></button>
                                    </div>
                                </form>
                            </td>
                            <td class='text-center'>{$rc}</td>
                            <td class='text-center'>
                                <button type='button' class='btn btn-sm btn-outline-brand me-1 btn-modern' title='Edit' onclick='editChild({$rid})'>Edit</button>
                                <button type='button' class='btn btn-sm btn-danger btn-modern' title='Delete' onclick='deleteChild({$rid}, {$jname})'>Delete</button>
                                <a href='add_sub_sub_subcategories.php?id={$rid}&name={$urlName}' class='btn btn-sm btn-accent btn-modern'>Add Sub Subcategory</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>No subcategories found. After you add subcategory it will display here.</td></tr>";
                }
                ?>

                <!-- ✅ Row for Adding a New Subcategory -->
                <tr>
                    <form class="child-form" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="subcategory_id" value="<?php echo intval($id); ?>">
                        <td class="text-center">New</td>
                        <td>
                            <input type="text" name="child_name" class="form-control" placeholder="Enter subcategory name" required>
                        </td>
                        <td class="text-center">Auto</td>
                        <td class="text-center">
                            <button type="submit" class="btn btn-accent btn-sm btn-modern">Add</button>
                        </td>
                    </form>
                </tr>
            </tbody>
        </table>

        <a href="load_subcategories.php?id=<?php echo $backParentId; ?>&name=<?php echo urlencode($backParentName); ?>" class="btn btn-modern btn-outline-brand mt-3">
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
        fetch('../actions/sub_subcategory_crud.php', { method: 'POST', body: fd })
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
    fd.append('subcategory_id', '<?php echo intval($id); ?>');
    fd.append('child_id', childId);
    fetch('../actions/sub_subcategory_crud.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(j => { if (j.success) location.reload(); else alert(j.message || 'Error'); })
        .catch(() => alert('Network error'));
}
</script>


</body>
</html>
