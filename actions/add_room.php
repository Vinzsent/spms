<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $building_name = trim($_POST['building_name'] ?? '');
    $room_number = trim($_POST['room_number'] ?? '');
    $floor = trim($_POST['floor'] ?? '');
    $fluorescent_light = intval($_POST['fluorescent_light'] ?? 0);
    $electric_fans_wall = intval($_POST['electric_fans_wall'] ?? 0);
    $ceiling = intval($_POST['ceiling'] ?? 0);
    $chairs_mono = intval($_POST['chairs_mono'] ?? 0);
    $steel = intval($_POST['steel'] ?? 0);
    $plastic_mini = intval($_POST['plastic_mini'] ?? 0);
    $teacher_table = intval($_POST['teacher_table'] ?? 0);
    $black_whiteboard = intval($_POST['black_whiteboard'] ?? 0);
    $platform = intval($_POST['platform'] ?? 0);
    $tv = intval($_POST['tv'] ?? 0);

    // Validation
    if (empty($building_name) || empty($room_number) || empty($floor)) {
        $_SESSION['error'] = "Please fill in all required fields: Building Name, Room Number, and Floor.";
        header("Location: ../pages/rooms_inventory.php");
        exit;
    }

    // Validate floor value
    if (!in_array($floor, ['R', 'N', 'O'])) {
        $_SESSION['error'] = "Invalid floor selection.";
        header("Location: ../pages/rooms_inventory.php");
        exit;
    }

    // Check if room already exists (unique constraint: building_name + room_number)
    $check_stmt = $conn->prepare("SELECT id FROM rooms_inventory WHERE building_name = ? AND room_number = ?");
    $check_stmt->bind_param("ss", $building_name, $room_number);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $check_stmt->close();
        $_SESSION['error'] = "Room already exists: $building_name - Room $room_number";
        header("Location: ../pages/rooms_inventory.php");
        exit;
    }
    $check_stmt->close();

    // Insert into the database
    $stmt = $conn->prepare("
        INSERT INTO rooms_inventory 
        (building_name, room_number, floor, fluorescent_light, electric_fans_wall, ceiling, 
         chairs_mono, steel, plastic_mini, teacher_table, black_whiteboard, platform, tv) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("sssiiiiiiiiii", 
        $building_name, $room_number, $floor, $fluorescent_light, $electric_fans_wall, 
        $ceiling, $chairs_mono, $steel, $plastic_mini, $teacher_table, 
        $black_whiteboard, $platform, $tv
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Room '$building_name - Room $room_number' has been added successfully.";
    } else {
        $_SESSION['error'] = "Error adding room: " . $conn->error;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: ../pages/rooms_inventory.php");
exit;
?>

