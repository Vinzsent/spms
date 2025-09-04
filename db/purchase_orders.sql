-- Purchase Orders Database Tables
-- Created for DARTS (SPMS) System

-- Main Purchase Orders Table
CREATE TABLE IF NOT EXISTS purchase_orders (
    po_id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    po_date DATE NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_address TEXT,
    payment_method VARCHAR(50) DEFAULT 'Check',
    payment_details TEXT,
    cash_amount DECIMAL(15,2) DEFAULT 0.00,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status ENUM('Draft', 'Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled') DEFAULT 'Draft',
    prepared_by INT,
    checked_by INT,
    approved_by INT,
    prepared_date DATETIME,
    checked_date DATETIME,
    approved_date DATETIME,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (created_by) REFERENCES user(id) ON DELETE RESTRICT,
    FOREIGN KEY (prepared_by) REFERENCES user(id) ON DELETE SET NULL,
    FOREIGN KEY (checked_by) REFERENCES user(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES user(id) ON DELETE SET NULL,
    
    -- Indexes for better performance
    INDEX idx_po_number (po_number),
    INDEX idx_po_date (po_date),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
);

-- Purchase Order Items Table
CREATE TABLE IF NOT EXISTS purchase_order_items (
    poi_id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    item_number INT NOT NULL,
    item_description TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    line_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_po_id (po_id),
    INDEX idx_item_number (item_number),
    
    -- Unique constraint to prevent duplicate item numbers per PO
    UNIQUE KEY unique_po_item (po_id, item_number)
);

-- Purchase Order Status History Table (for audit trail)
CREATE TABLE IF NOT EXISTS purchase_order_status_history (
    posh_id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    old_status ENUM('Draft', 'Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled'),
    new_status ENUM('Draft', 'Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled') NOT NULL,
    changed_by INT NOT NULL,
    change_reason TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES user(id) ON DELETE RESTRICT,
    
    -- Index
    INDEX idx_po_id_history (po_id),
    INDEX idx_changed_at (changed_at)
);

-- Purchase Order Attachments Table (for invoices, receipts, etc.)
CREATE TABLE IF NOT EXISTS purchase_order_attachments (
    poa_id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES user(id) ON DELETE RESTRICT,
    
    -- Index
    INDEX idx_po_id_attachments (po_id)
);

-- Insert sample data for testing
INSERT INTO purchase_orders (po_number, po_date, supplier_name, supplier_address, total_amount, created_by) 
VALUES 
('PO-2025-001', '2025-01-04', 'ABC Office Supplies', '123 Main St, City, Province', 15000.00, 1),
('PO-2025-002', '2025-01-04', 'XYZ Equipment Corp', '456 Business Ave, City, Province', 25000.00, 1);

-- Insert sample items for the first PO
INSERT INTO purchase_order_items (po_id, item_number, item_description, quantity, unit_cost, line_total)
VALUES 
(1, 1, 'Bond Paper A4 (500 sheets)', 10, 250.00, 2500.00),
(1, 2, 'Ballpen (Blue, Box of 12)', 5, 150.00, 750.00),
(1, 3, 'Folder (Legal Size)', 20, 25.00, 500.00);

-- Insert sample items for the second PO
INSERT INTO purchase_order_items (po_id, item_number, item_description, quantity, unit_cost, line_total)
VALUES 
(2, 1, 'Desktop Computer', 2, 35000.00, 70000.00),
(2, 2, 'Printer (Laser)', 1, 15000.00, 15000.00),
(2, 3, 'UPS (1000VA)', 2, 5000.00, 10000.00);

-- Update total amounts based on line items
UPDATE purchase_orders p 
SET total_amount = (
    SELECT COALESCE(SUM(line_total), 0) 
    FROM purchase_order_items poi 
    WHERE poi.po_id = p.po_id
) 
WHERE po_id IN (1, 2);