# Department Issued Feature

## Overview
This feature allows users to specify which department/unit an item is issued to when marking a transaction as "Issued".

## Features Added

### 1. Department Selection Modal
- Added a department dropdown in the issued modal
- Available departments: HR, Accounting, Admin, Maintenance, MIS, Teacher
- Required field validation
- Beautiful styling with green theme to match the existing design

### 2. Database Integration
- Uses existing `issued_to_department` column in `supplier_transaction` table
- Stores the department name as a varchar(100)
- Updates both status and department when marking as "Issued"

### 3. Table Display
- Added "Department" column to the transaction list table
- Shows department badge for issued items
- Displays "-" for non-issued items
- Updated table structure to accommodate new column

### 4. Validation
- Client-side validation ensures department is selected before submission
- Server-side validation in `update_transaction_status.php`
- Clear error messages for missing department selection

## How to Use

1. **Mark Item as Issued:**
   - Click the "Issued" button on any transaction
   - Select the department/unit from the dropdown
   - Click "Confirm Issued"

2. **View Department Information:**
   - The "Department" column shows which department received the item
   - Only issued items show department information

## Technical Details

### Files Modified:
- `pages/transaction_list.php` - Added department selection UI and table column
- `actions/update_transaction_status.php` - Already supports department field
- `db/update_issued_department.sql` - Database schema (already exists)

### Database Schema:
```sql
ALTER TABLE `supplier_transaction` 
ADD COLUMN `issued_to_department` varchar(100) NULL AFTER `status`;
```

### Available Departments:
- HR
- Accounting  
- Admin
- Maintenance
- MIS
- Teacher

## Styling
- Department selection has a green-themed background
- Department badges use Bootstrap's info color scheme
- Consistent with existing modal and table styling
- Responsive design for mobile devices

## Validation Rules
- Department selection is required when marking as "Issued"
- Form cannot be submitted without selecting a department
- Clear error messages guide the user 