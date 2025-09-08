<?php
include '../includes/db.php';
include '../includes/header.php';
include '../includes/auth.php';

// Get account_type id
if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM account_types WHERE id = $id");

if (!$result || mysqli_num_rows($result) === 0) {
    die("Account type not found.");
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Account Type</title>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Edit Account Type</h2>

    <form action="update_account_type.php" method="post">
        <input type="hidden" name="id" value="<?= $row['id']; ?>">

        <div class="mb-3">
            <label for="account_name" class="form-label">Account Name</label>
            <input type="text" class="form-control" id="account_name" name="account_name"
                   value="<?= htmlspecialchars($row['name']); ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="account_types.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
