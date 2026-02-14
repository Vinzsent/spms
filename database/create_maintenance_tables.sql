-- Maintenance Work Orders Table
CREATE TABLE IF NOT EXISTS maintenance_work_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    maintenance_type ENUM('PM', 'CM', 'PdM') NOT NULL,
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    description TEXT,
    assigned_to VARCHAR(255),
    scheduled_start DATE,
    scheduled_end DATE,
    actual_start DATETIME,
    actual_end DATETIME,
    labor_hours DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('Open', 'Assigned', 'In Progress', 'On Hold', 'Completed', 'Closed') DEFAULT 'Open',
    total_cost DECIMAL(10, 2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES property_inventory(inventory_id) ON DELETE CASCADE
);

-- Maintenance Tasks Table
CREATE TABLE IF NOT EXISTS maintenance_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    task_description TEXT NOT NULL,
    checklist_required BOOLEAN DEFAULT FALSE,
    status ENUM('Pending', 'Completed', 'Skipped') DEFAULT 'Pending',
    completed_by VARCHAR(255),
    completed_at DATETIME,
    FOREIGN KEY (work_order_id) REFERENCES maintenance_work_orders(id) ON DELETE CASCADE
);

-- Maintenance Parts Usage Table
CREATE TABLE IF NOT EXISTS maintenance_parts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    part_id INT NOT NULL,
    quantity_used INT NOT NULL DEFAULT 1,
    cost_per_unit DECIMAL(10, 2) DEFAULT 0.00,
    total_cost DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (work_order_id) REFERENCES maintenance_work_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (part_id) REFERENCES inventory(inventory_id) ON DELETE RESTRICT
);

-- Maintenance Logs Table
CREATE TABLE IF NOT EXISTS maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT,
    action VARCHAR(255) NOT NULL,
    performed_by VARCHAR(255),
    details TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_order_id) REFERENCES maintenance_work_orders(id) ON DELETE SET NULL
);
