# Transaction Specifications Feature Setup

This feature adds the ability to store detailed specifications (brand, serial number, type, size, etc.) for items in supplier transactions without modifying the main table structure.

## Database Setup

### 1. Create the specifications table
Run the SQL from `db/specifications_table.sql`:
```sql
CREATE TABLE `transaction_specifications` (
  `spec_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `warranty_info` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`spec_id`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `transaction_specifications_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `supplier_transaction` (`transaction_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 2. Add status column to supplier_transaction table
Run the SQL from `db/update_transaction_table.sql`:
```sql
ALTER TABLE `supplier_transaction` 
ADD COLUMN `status` varchar(50) DEFAULT 'Pending' AFTER `amount`;

UPDATE `supplier_transaction` SET `status` = 'Pending' WHERE `status` IS NULL;
```

## Features Added

### 1. Specifications Button
- Added a blue "Specs" button to each transaction row
- Button opens a modal to view/edit specifications

### 2. Specifications Modal
- **Brand**: Enter the brand name of the item
- **Serial Number**: Enter the serial number or barcode
- **Type**: Enter the type or model variant
- **Size/Dimensions**: Enter size specifications
- **Model**: Enter the model number
- **Warranty Information**: Enter warranty details
- **Additional Notes**: Any other specifications or notes

### 3. Functionality
- **View Existing**: Automatically loads existing specifications if available
- **Add New**: Allows adding specifications for items that don't have any
- **Edit**: Update existing specifications
- **Status Indicators**: Shows whether specifications exist or not
- **Auto-save**: Saves changes immediately with success/error feedback

## Files Added/Modified

### New Files:
- `db/specifications_table.sql` - Database table creation
- `db/update_transaction_table.sql` - Add status column
- `modals/view_specifications_modal.php` - Specifications modal
- `actions/save_specifications.php` - Save/update specifications
- `actions/get_specifications.php` - Retrieve specifications

### Modified Files:
- `pages/transaction_list.php` - Added specifications button and modal inclusion

## Usage

1. **View Specifications**: Click the blue "Specs" button on any transaction row
2. **Add Specifications**: Fill in the form fields and click "Save Specifications"
3. **Edit Specifications**: Modify existing specifications and save changes
4. **Status Tracking**: The modal shows whether specifications exist for the item

## Benefits

- **Non-intrusive**: Doesn't change the main table structure
- **Flexible**: Can add specifications to any transaction
- **User-friendly**: Clean modal interface with status indicators
- **Comprehensive**: Covers brand, serial number, type, size, model, warranty, and notes
- **Responsive**: Works well on different screen sizes

## Technical Details

- Uses AJAX for seamless data loading and saving
- Implements proper error handling and user feedback
- Maintains data integrity with foreign key constraints
- Includes timestamp tracking for audit purposes
- Responsive design with Bootstrap styling 