-- Canvass Database Tables
-- Created for DARTS (SPMS) System

-- Main Canvass Table
CREATE TABLE IF NOT EXISTS canvass (
    canvass_id INT AUTO_INCREMENT PRIMARY KEY,
    canvass_number VARCHAR(50) UNIQUE NOT NULL,
    canvass_date DATE NOT NULL,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('Draft', 'Completed', 'Approved', 'Cancelled') DEFAULT 'Draft',
    canvassed_by INT,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (created_by) REFERENCES user(id) ON DELETE RESTRICT,
    FOREIGN KEY (canvassed_by) REFERENCES user(id) ON DELETE SET NULL,
    
    -- Indexes for better performance
    INDEX idx_canvass_number (canvass_number),
    INDEX idx_canvass_date (canvass_date),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
);

-- Canvass Items Table
CREATE TABLE IF NOT EXISTS canvass_items (
    canvass_item_id INT AUTO_INCREMENT PRIMARY KEY,
    canvass_id INT NOT NULL,
    item_number INT NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    item_description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_cost DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (canvass_id) REFERENCES canvass(canvass_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_canvass_id (canvass_id),
    INDEX idx_item_number (item_number),
    INDEX idx_supplier_name (supplier_name),
    
    -- Unique constraint to prevent duplicate item numbers per canvass
    UNIQUE KEY unique_canvass_item (canvass_id, item_number)
);

-- Canvass Status History Table (for audit trail)
CREATE TABLE IF NOT EXISTS canvass_status_history (
    csh_id INT AUTO_INCREMENT PRIMARY KEY,
    canvass_id INT NOT NULL,
    old_status ENUM('Draft', 'Completed', 'Approved', 'Cancelled'),
    new_status ENUM('Draft', 'Completed', 'Approved', 'Cancelled') NOT NULL,
    changed_by INT NOT NULL,
    change_reason TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (canvass_id) REFERENCES canvass(canvass_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES user(id) ON DELETE RESTRICT,
    
    -- Index
    INDEX idx_canvass_id_history (canvass_id),
    INDEX idx_changed_at (changed_at)
);

-- Insert sample data for testing
INSERT INTO canvass (canvass_number, canvass_date, total_amount, created_by) 
VALUES 
('CV-2025-001', '2025-01-04', 25000.00, 1),
('CV-2025-002', '2025-01-04', 18500.00, 1);

-- Insert sample items for the first canvass
INSERT INTO canvass_items (canvass_id, item_number, supplier_name, item_description, quantity, unit_cost, total_cost)
VALUES 
(1, 1, 'ABC Office Supplies', 'Bond Paper A4 (500 sheets)', 10, 250.00, 2500.00),
(1, 2, 'XYZ Equipment Corp', 'Bond Paper A4 (500 sheets)', 10, 230.00, 2300.00),
(1, 3, 'Tech Solutions Inc', 'Bond Paper A4 (500 sheets)', 10, 245.00, 2450.00);

-- Insert sample items for the second canvass
INSERT INTO canvass_items (canvass_id, item_number, supplier_name, item_description, quantity, unit_cost, total_cost)
VALUES 
(2, 1, 'ABC Office Supplies', 'Printer Ink Cartridge', 5, 1200.00, 6000.00),
(2, 2, 'Office Depot', 'Printer Ink Cartridge', 5, 1150.00, 5750.00),
(2, 3, 'Supplies Plus', 'Printer Ink Cartridge', 5, 1100.00, 5500.00);

-- Update total amounts based on items
UPDATE canvass c 
SET total_amount = (
    SELECT COALESCE(SUM(total_cost), 0) 
    FROM canvass_items ci 
    WHERE ci.canvass_id = c.canvass_id
) 
WHERE canvass_id IN (1, 2);
