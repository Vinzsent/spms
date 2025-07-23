
<?php
include 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $suffix = $_POST['suffix'];
    $academic_title = $_POST['academic_title'];
    $user_type = $_POST['user_type'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (title, first_name, middle_name, last_name, suffix, academic_title, user_type, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $title, $first_name, $middle_name, $last_name, $suffix, $academic_title, $user_type, $email, $password);
    $stmt->execute();

    if ($stmt->affected_rows > 0) 
      {
        header("Location: login.php");
        exit;
    } else {
        $error = "Registration failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>User Registration</title>
</head>
<body class="container mt-5">
  <h2>Register</h2>
  <form method="POST" class="w-75">
    <div class="row">
      <div class="col">
        <input class="form-control mb-2" name="title" placeholder="Title">
      </div>
      <div class="col">
        <input class="form-control mb-2" name="suffix" placeholder="Suffix">
      </div>
    </div>
    <input class="form-control mb-2" name="first_name" placeholder="First Name">
    <input class="form-control mb-2" name="middle_name" placeholder="Middle Name">
    <input class="form-control mb-2" name="last_name" placeholder="Last Name">
    <input class="form-control mb-2" name="academic_title" placeholder="Academic Title">
    <select class="form-control mb-2" name="user_type">
      <option value="admin">Admin</option>
      <option value="staff">Staff</option>
    </select>
    <input class="form-control mb-2" type="email" name="email" placeholder="Email">
    <input class="form-control mb-2" type="password" name="password" placeholder="Password">
    <div class="d-grid mt-3 justify-content-center">
      <a href="login.php"><button class="btn btn-primary" type="button">Login</button></a>
    </div>
  </form>
  <?php if ($error): ?><div class="alert alert-danger mt-3"><?= $error ?></div><?php endif; ?>
</body>
</html>
