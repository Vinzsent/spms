# Notification System Documentation

## Overview
The notification system provides real-time notifications to users about various events in the Asset Management System. It includes a modern bell icon in the navbar with a dropdown showing notifications.

## Features

### 🎯 **Core Features**
- **Bell Icon**: Modern notification bell in the navbar
- **Real-time Updates**: Notifications refresh every 30 seconds
- **Unread Badge**: Shows count of unread notifications
- **Mark as Read**: Click notifications to mark as read
- **Mark All Read**: Button to mark all notifications as read
- **Responsive Design**: Works on mobile and desktop

### 🎨 **Design**
- **Color Scheme**: Consistent with the main application (dark green theme)
- **Modern UI**: Smooth animations and hover effects
- **Icons**: Different icons for different notification types
- **Time Display**: Smart time formatting (just now, 5m ago, 2h ago, etc.)

## Database Setup

### 1. Create Notifications Table
Run the SQL script in `db/notifications_table.sql`:

```sql
-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint
ALTER TABLE `notifications`
ADD CONSTRAINT `notifications_user_fk`
FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
```

## Files Created

### 📁 **Database**
- `db/notifications_table.sql` - Database table creation script

### 📁 **Actions**
- `actions/get_notifications.php` - Retrieve notifications for current user
- `actions/mark_notification_read.php` - Mark single notification as read
- `actions/mark_all_notifications_read.php` - Mark all notifications as read

### 📁 **Includes**
- `includes/notification_helper.php` - Helper functions for creating notifications

### 📁 **Updated Files**
- `includes/navbar.php` - Added notification bell and dropdown

## Integration Guide

### 1. **Include Helper Functions**
Add this to any file where you want to create notifications:

```php
require_once '../includes/notification_helper.php';
```

### 2. **Create Notifications for Different Events**

#### **Supply Request Submitted**
```php
// In add_supply_request.php after successful insertion
if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    notifySupplyRequestSubmitted($request_id, $department, $description, $conn);
    // ... rest of success handling
}
```

#### **Request Status Updated**
```php
// In update_issuance_status.php after status update
notifyRequestStatusUpdate($request_id, $status_action, $action_by, $requester_id, $conn);
```

#### **Item Issued**
```php
// In update_transaction_status.php after issuance
notifyItemIssued($transaction_id, $issued_by, $requester_id, $item_description, $conn);
```

### 3. **Notification Types**
- `request` - New supply requests, status updates
- `approved` - Request approvals
- `rejected` - Request rejections  
- `issued` - Item issuances

## Usage Examples

### **Creating a Simple Notification**
```php
createNotification($user_id, 'request', 'New Request', 'A new supply request has been submitted', $request_id, 'supply_request', $conn);
```

### **Getting Unread Count**
```php
$unread_count = getUnreadNotificationCount($user_id, $conn);
```

### **Notification for Specific Event**
```php
// When a supply request is approved
notifyRequestApproved($request_id, $approved_by, $requester_id, $conn);
```

## Frontend Features

### **JavaScript Functions**
- `loadNotifications()` - Load notifications from server
- `displayNotifications()` - Display notifications in dropdown
- `markAsRead(notificationId)` - Mark single notification as read
- `markAllAsRead()` - Mark all notifications as read
- `updateNotificationBadge()` - Update the notification count badge

### **CSS Classes**
- `.notification-btn` - Bell button styling
- `.notification-badge` - Unread count badge
- `.notification-dropdown` - Dropdown container
- `.notification-item` - Individual notification item
- `.notification-item.unread` - Unread notification styling

## Notification Icons
- **Request**: `fa-clipboard-list` (blue)
- **Approved**: `fa-check-circle` (green)
- **Rejected**: `fa-times-circle` (red)
- **Issued**: `fa-hand-holding-usd` (yellow)

## Responsive Design
- **Desktop**: Full dropdown with all features
- **Mobile**: Smaller dropdown, adjusted positioning
- **Touch-friendly**: Large click targets for mobile

## Security Features
- **User-specific**: Users only see their own notifications
- **SQL Injection Protection**: Prepared statements used
- **Session Validation**: Checks user authentication
- **Input Validation**: Validates all input parameters

## Performance Optimizations
- **Limit Results**: Only loads last 20 notifications
- **Auto-refresh**: Updates every 30 seconds
- **Efficient Queries**: Indexed database columns
- **Lazy Loading**: Only loads when dropdown is opened

## Troubleshooting

### **Common Issues**

1. **Notifications not showing**
   - Check if database table exists
   - Verify user authentication
   - Check browser console for errors

2. **Bell icon not appearing**
   - Ensure Font Awesome is loaded
   - Check if navbar.php is included

3. **Database errors**
   - Run the notifications table SQL script
   - Check database connection
   - Verify foreign key constraints

### **Debug Mode**
Enable console logging by checking browser developer tools:
- Network tab: Check AJAX requests
- Console tab: View JavaScript logs
- Elements tab: Inspect notification elements

## Future Enhancements
- **Email Notifications**: Send email for important events
- **Push Notifications**: Browser push notifications
- **Notification Preferences**: User settings for notification types
- **Notification History**: Archive old notifications
- **Bulk Actions**: Select multiple notifications for actions 