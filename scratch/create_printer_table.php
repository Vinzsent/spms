<?php
include 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS printer_header_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    telephone_number VARCHAR(100),
    fax_number VARCHAR(100),
    mobile_number VARCHAR(100),
    email_address VARCHAR(150),
    website VARCHAR(150),
    logo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

if ($conn->query($sql) === TRUE) {
    echo "Table printer_header_settings created successfully.\n";
    
    // Check if default row exists
    $result = $conn->query("SELECT COUNT(*) as cnt FROM printer_header_settings WHERE id = 1");
    $row = $result->fetch_assoc();
    if ($row['cnt'] == 0) {
        $logo = 'assets/images/logo.png';
        if (file_exists('DCC2.png') && !file_exists('uploads/DCC2.png')) {
            // copy to uploads/ or just keep it there
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            copy('DCC2.png', 'uploads/DCC2.png');
            $logo = 'uploads/DCC2.png';
        }
        
        $insert = "INSERT INTO printer_header_settings (id, school_name, address, telephone_number, fax_number, mobile_number, email_address, website, logo_path)
                   VALUES (1, 'DAVAO CENTRAL COLLEGE', 'Juan dela Cruz St., Toril, Davao City, Philippines', '(082) 291-1882', '(082) 291-2053', '', 'davaocentralcollege2011@gmail.com', 'www.davaocentralcollege.com', ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("s", $logo);
        if ($stmt->execute()) {
            echo "Default printer header settings seeded successfully.\n";
        } else {
            echo "Error seeding: " . $stmt->error . "\n";
        }
    } else {
        echo "Default printer header settings already seeded.\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
