<?php
include '../../includes/db.php';
session_start();

// 1. Create Tables
$sql = "
CREATE TABLE IF NOT EXISTS business_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS supplier_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_type_id INT NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_type_id) REFERENCES business_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_per_type (business_type_id, category_name)
);
";

if ($conn->multi_query($sql)) {
    echo "Tables created successfully.\n";
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error creating tables: " . $conn->error . "\n";
    exit;
}

// 2. Seed Data
$categoryMap = [
    "IT Equipment Supplier" => [
        "ICT Equipment and Devices",
        "Subscription, License, and Software Services"
    ],
    "Office Equipment Vendor" => [
        "Office Equipment",
        "Office Supplies and Materials"
    ],
    "Air Conditioning Equipment Supplier" => [
        "Air Conditioning Units and Cooling Systems"
    ],
    "Equipment Maintenance Provider" => [
        "Repairs and Maintenance – Equipment and Devices"
    ],
    "Furniture Supplier" => [
        "Furniture and Fixtures"
    ],
    "Laboratory Equipment Supplier" => [
        "Lab Equipment",
        "Lab Chemicals and Reagents"
    ],
    "Construction and Renovation Contractor" => [
        "Construction Materials",
        "Renovation Services"
    ],
    "Machinery and Equipment Supplier" => [
        "Heavy Machinery",
        "Production Equipment"
    ],
    "Janitorial Services" => [
        "Cleaning Supplies",
        "Janitorial Services"
    ],
    "Educational Materials Supplier" => [
        "Books and Publications",
        "Teaching Aids"
    ],
    "Medical Supplies Provider" => [
        "Medicines",
        "Medical Equipment"
    ],
    "Printing Services" => [
        "Document Printing",
        "Custom Printing"
    ],
    "Logistics and Delivery Services" => [
        "Freight Services",
        "Courier Services"
    ],
    "Electrical Supplies Provider" => [
        "Electrical Components",
        "Wiring and Cabling"
    ]
];

echo "Seeding data...\n";

foreach ($categoryMap as $type => $categories) {
    // Insert Business Type
    $stmt = $conn->prepare("INSERT IGNORE INTO business_types (type_name) VALUES (?)");
    $stmt->bind_param("s", $type);
    $stmt->execute();

    // Get ID (either inserted or existing)
    if ($stmt->affected_rows > 0) {
        $typeId = $stmt->insert_id;
    } else {
        $res = $conn->query("SELECT id FROM business_types WHERE type_name = '" . $conn->real_escape_string($type) . "'");
        $row = $res->fetch_assoc();
        $typeId = $row['id'];
    }
    $stmt->close();

    // Insert Categories
    $stmt = $conn->prepare("INSERT IGNORE INTO supplier_categories (business_type_id, category_name) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->bind_param("is", $typeId, $cat);
        $stmt->execute();
    }
    $stmt->close();
}

echo "Data seeded successfully.\n";
