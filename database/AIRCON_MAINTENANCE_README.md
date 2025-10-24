# Aircon Maintenance Records Feature

## Overview
This feature allows users to track and manage maintenance records for each aircon unit in the system. Users can view, add, edit, and delete maintenance records through an interactive modal interface.

## Database Setup

### Step 1: Run the SQL Script
Execute the SQL file to create the maintenance table:
```sql
-- File: database/aircon_maintenance.sql
-- Run this in your MySQL database
```

The table structure includes:
- `maintenance_id` (Primary Key)
- `aircon_id` (Foreign Key to aircons table)
- `service_date` (Date of maintenance)
- `service_type` (Type of service: Cleaning, Repair, etc.)
- `technician` (Person/company who performed service)
- `next_scheduled_date` (Next scheduled maintenance date)
- `remarks` (Notes or issues found)
- `created_by` (User who created the record)
- `date_created` & `last_updated` (Timestamps)

## Features

### 1. View Maintenance Records
- Click the "View Records" button in the Maintenance Schedule column
- Opens a modal showing all maintenance history for that aircon unit
- Displays: Service Date, Service Type, Technician, Next Scheduled Date, Remarks, Created By

### 2. Add Maintenance Record
- Click the "Add Maintenance Record" button in the maintenance modal
- Fill in the form:
  - Service Date (Required)
  - Service Type (Required) - Dropdown with options
  - Technician (Optional)
  - Next Scheduled Date (Optional)
  - Remarks (Optional)
- Automatically updates the aircon's last_service_date field

### 3. Edit Maintenance Record
- Click the Edit (yellow) button on any maintenance record
- Modify the fields as needed
- Updates the record and refreshes the last_service_date

### 4. Delete Maintenance Record
- Click the Delete (red) button on any maintenance record
- Confirms deletion with user
- Removes the record and updates last_service_date to most recent remaining record

### 5. View Details
- Click the View (gray) button to see full details of a maintenance record
- Shows all fields in a read-only modal

## Files Created/Modified

### Backend Files (Actions)
1. `actions/get_aircon_maintenance.php` - Fetches maintenance records
2. `actions/add_aircon_maintenance.php` - Adds new maintenance record
3. `actions/edit_aircon_maintenance.php` - Updates existing record
4. `actions/delete_aircon_maintenance.php` - Deletes maintenance record

### Frontend Files
1. `pages/aircon_list.php` - Modified to include:
   - Maintenance Schedule column with "View Records" button
   - Three modals: Main Records Modal, Add/Edit Form Modal, View Details Modal
   - JavaScript handlers for all CRUD operations

### Database Files
1. `database/aircon_maintenance.sql` - Table creation script

## Service Type Options
- Cleaning
- Repair
- Preventive Maintenance
- Filter Replacement
- Gas Refill
- Inspection
- Others

## Technical Details

### AJAX Endpoints
- **GET** `/actions/get_aircon_maintenance.php?aircon_id={id}`
  - Returns: aircon details and array of maintenance records
  
- **POST** `/actions/add_aircon_maintenance.php`
  - Parameters: aircon_id, service_date, service_type, technician, next_scheduled_date, remarks
  
- **POST** `/actions/edit_aircon_maintenance.php`
  - Parameters: maintenance_id, service_date, service_type, technician, next_scheduled_date, remarks
  
- **POST** `/actions/delete_aircon_maintenance.php`
  - Parameters: maintenance_id

### Security Features
- Session authentication required for all endpoints
- Prepared statements to prevent SQL injection
- Input validation on required fields
- Foreign key constraint ensures data integrity

### Auto-Update Feature
When adding, editing, or deleting maintenance records, the system automatically updates the parent aircon's `last_service_date` field to reflect the most recent service date.

## Usage Instructions

1. **First Time Setup:**
   - Run the SQL script in `database/aircon_maintenance.sql`
   - Ensure the `aircons` table has the `last_service_date` column

2. **Adding Maintenance Records:**
   - Navigate to Aircon List page
   - Click "View Records" button in Maintenance Schedule column
   - Click "Add Maintenance Record" button
   - Fill in the form and submit

3. **Managing Records:**
   - Use View button to see full details
   - Use Edit button to modify records
   - Use Delete button to remove records

## Notes
- The page automatically refreshes after add/edit/delete operations to show updated last service dates
- Maintenance records are sorted by service_date in descending order (most recent first)
- Deleting an aircon unit will cascade delete all its maintenance records (foreign key constraint)
