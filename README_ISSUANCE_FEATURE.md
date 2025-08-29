# Issuance Management Feature

## Overview
The Issuance Management feature allows administrators to track and manage supply requests through a comprehensive approval workflow. This system provides a modern, user-friendly interface for managing the entire lifecycle of supply requests from submission to final approval.

## Features

### 1. Modern Dashboard Interface
- **Sidebar Navigation**: Fixed sidebar with navigation to all system modules
- **Statistics Cards**: Real-time statistics showing total requests, pending approvals, approved requests, and issued items
- **Responsive Design**: Modern UI with consistent color scheme matching the asset management system

### 2. Supply Request Management
- **Request Listing**: View all supply requests in a modern table format
- **Status Tracking**: Visual status badges showing current approval stage
- **Detailed View**: Modal popup showing complete request details and approval timeline

### 3. Approval Workflow
The system supports a 5-stage approval process:
1. **Noted** - Initial acknowledgment of the request
2. **Checked** - Verification of request details
3. **Verified** - Confirmation of requirements
4. **Issued** - Items have been distributed
5. **Approved** - Final approval completed

### 4. Status Management
- **Status Updates**: Update request status through intuitive modal interface
- **Action Tracking**: Record who performed each action and when
- **Remarks System**: Optional remarks for additional context
- **Timeline View**: Visual timeline showing approval progress

## Database Setup

Before using this feature, you need to run the database update script:

1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Execute the SQL script: `db/add_issuance_columns.sql`

This will add the following columns to the `supply_request` table:
- `noted_by` (varchar) - Person who noted the request
- `noted_date` (datetime) - When the request was noted
- `checked_by` (varchar) - Person who checked the request
- `checked_date` (datetime) - When the request was checked
- `verified_by` (varchar) - Person who verified the request
- `verified_date` (datetime) - When the request was verified
- `issued_by` (varchar) - Person who issued the items
- `issued_date` (datetime) - When the items were issued
- `approved_by` (varchar) - Person who approved the request
- `approved_date` (datetime) - When the request was approved
- `remarks` (text) - Additional notes or remarks

## Usage

### 1. Viewing Supply Requests
- Navigate to the Issuance page
- View all supply requests in the table
- Click "View" to see detailed information and approval timeline
- Click "Update" to change the request status

### 2. Updating Request Status
- Click the "Update" button on any request
- Select the appropriate action from the dropdown
- Enter your name in the "Action By" field
- Add optional remarks
- Click "Update Status" to save

### 3. Understanding Status Badges
- **Pending** (Yellow) - No actions taken yet
- **Noted** (Blue) - Request has been acknowledged
- **Checked** (Purple) - Request details verified
- **Verified** (Orange) - Requirements confirmed
- **Issued** (Green) - Items distributed
- **Approved** (Teal) - Final approval complete

## Technical Details

### Files Created/Modified
1. `pages/issuance.php` - Main issuance management page
2. `actions/update_issuance_status.php` - Backend handler for status updates
3. `db/add_issuance_columns.sql` - Database schema updates
4. `README_ISSUANCE_FEATURE.md` - This documentation

### Key Features
- **AJAX Integration**: Real-time status updates without page refresh
- **Form Validation**: Client and server-side validation
- **Error Handling**: Comprehensive error handling and user feedback
- **Responsive Design**: Works on desktop and mobile devices
- **Modern UI**: Consistent with the overall system design

### Color Scheme
The interface uses the same color scheme as the asset management system:
- Primary: #073b1d (Dark Green)
- Secondary: #ff6b35 (Orange)
- Success: #28a745 (Green)
- Warning: #ffc107 (Yellow)
- Info: #17a2b8 (Blue)
- Danger: #dc3545 (Red)

## Security Features
- **Session Management**: Proper session handling and authentication
- **Input Validation**: All user inputs are validated and sanitized
- **SQL Injection Protection**: Prepared statements for all database queries
- **XSS Protection**: HTML escaping for all output

## Browser Compatibility
- Chrome (recommended)
- Firefox
- Safari
- Edge

## Dependencies
- Bootstrap 5.3.3
- jQuery 3.6.0
- Font Awesome 6.4.0
- PHP 7.4+

## Troubleshooting

### Common Issues
1. **Status not updating**: Check if the database columns were added correctly
2. **Modal not opening**: Ensure Bootstrap and jQuery are loaded
3. **AJAX errors**: Check browser console for JavaScript errors
4. **Database errors**: Verify database connection and table structure

### Error Messages
- "Invalid request ID" - The request doesn't exist
- "Invalid status action" - Unsupported status action
- "Failed to update status" - Database error occurred
- "Database error occurred" - Check database connection

## Future Enhancements
- Email notifications for status changes
- Bulk status updates
- Export functionality for reports
- Advanced filtering and search
- Audit trail logging
- Mobile app integration 