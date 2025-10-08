<?php
include '../includes/auth.php';
include '../includes/db.php';

try {
    // Validate DB connection
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Collect semester info
    $semesters = [];
    if (!empty($_POST['semester_1'])) $semesters[] = $_POST['semester_1'];
    if (!empty($_POST['semester_2'])) $semesters[] = $_POST['semester_2'];
    if (!empty($_POST['summer'])) $semesters[] = $_POST['summer'];
    $semester = implode(", ", $semesters);

    // Collect main form data
    $school_year = $_POST['school_year'] ?? '';
    $building = $_POST['building'] ?? '';
    $office = $_POST['office'] ?? '';
    $accountable_person = $_POST['accountable_person'] ?? '';
    $inventory_date = $_POST['inventory_date'] ?? '';

    if (empty($school_year) || empty($building) || empty($office) || empty($accountable_person) || empty($inventory_date)) {
        throw new Exception("Missing required fields.");
    }

    // Insert into office_inventory table
    $stmt = $conn->prepare("INSERT INTO office_inventory (semester, school_year, building, office, accountable_person, inventory_date) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssss", $semester, $school_year, $building, $office, $accountable_person, $inventory_date);
    $stmt->execute();
    $inventory_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // ✅ Success alert with redirect
    echo "<script>
        alert('Inventory saved successfully!');
        window.location.href = '../pages/office_inventory.php';
    </script>";

} catch (Exception $e) {
    // ✅ Error alert
    echo "<script>
        alert('Error: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
}
?>