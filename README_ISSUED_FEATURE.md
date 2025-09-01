# Issued Feature Implementation

## Overview
This feature allows you to assign items to specific departments/units when marking transactions as "Issued".

## Database Setup
Before using this feature, you need to run the database update script:

1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Execute the SQL script: `db/update_issued_department.sql`

This will update the `supplier_transaction` table:
- Remove `issued_to_user` column
- Add `issued_to_department` (varchar) - Stores the department/unit name
- Keep `issued_date` (datetime) - Records when the item was issued

## Features Added

### 1. Department/Unit Dropdown in Issued Modal
- When marking a transaction as "Issued", you'll now see a dropdown to select the department/unit
- Available options: HR, Accounting, Registrar, Maintenance
- Example: "HR" or "Accounting"

### 2. Enhanced Transaction List
- New "Issued To" column in the transaction table
- Shows the assigned department/unit with a building icon
- Displays "-" for items not yet issued

### 3. Database Integration
- The `update_transaction_status.php` action now handles department assignment
- When status is "Issued", it requires a department to be selected
- Records the issue date automatically

## Usage

1. **Marking Items as Issued:**
   - Click the "Issued" button on any transaction
   - Select a department/unit from the dropdown
   - Click "Confirm Issued"

2. **Viewing Issued Items:**
   - The transaction list now shows which department each item was issued to
   - Issued items display the department name in a green badge

## Technical Details

- **Form Validation:** The system requires a department to be selected when marking as "Issued"
- **Simple Storage:** The `issued_to_department` column stores the department name directly
- **No Foreign Key:** Since departments are predefined, no foreign key constraint is needed

## Files Modified

1. `pages/transaction_list.php` - Added department dropdown and display
2. `actions/update_transaction_status.php` - Enhanced to handle department assignment
3. `db/update_issued_department.sql` - Database schema update
4. `README_ISSUED_FEATURE.md` - This documentation file 