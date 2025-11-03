# Rooms Inventory Database Setup

## Instructions

To set up the rooms inventory database, follow these steps:

### 1. Access phpMyAdmin
- Open your browser and go to: `http://localhost/phpmyadmin`
- Select your database (usually `spms` or similar)

### 2. Run the SQL Script
- Click on the "SQL" tab in phpMyAdmin
- Open the file `create_rooms_inventory.sql` in this folder
- Copy all the SQL content
- Paste it into the SQL query box in phpMyAdmin
- Click "Go" to execute

### 3. Verify the Table
After running the script, you should see:
- A new table called `rooms_inventory` with 18 sample rooms
- The table contains all the inventory items for each room

### 4. View the Page
- Navigate to: `http://localhost/spms/pages/rooms_inventory.php`
- You should see all rooms displayed in a card layout, grouped by floor

## Database Structure

The `rooms_inventory` table includes:
- **id**: Auto-increment primary key
- **building_name**: Name of the building
- **room_number**: Room number
- **floor**: Floor identifier (R, N, O)
- **fluorescent_light**: Number of fluorescent lights
- **electric_fans_wall**: Number of wall electric fans
- **ceiling**: Number of ceiling items
- **chairs_mono**: Number of mono chairs
- **steel**: Number of steel items
- **plastic_mini**: Number of mini plastic items
- **teacher_table**: Number of teacher's tables
- **black_whiteboard**: Number of blackboards/whiteboards
- **platform**: Number of platforms
- **tv**: Number of TVs
- **created_at**: Timestamp of creation
- **updated_at**: Timestamp of last update

## Sample Data Included

The SQL script includes 18 rooms from Salusiano Building:
- **First Floor (R)**: Rooms 101, 103, 201, 202, 203, 204
- **Second Floor (N)**: Rooms 301, 302, 303, 304, 305, 306
- **Third Floor (O)**: Rooms 401, 402, 403, 404, 405, 406

## Features

- Clean card-based layout
- Rooms grouped by floor
- Color-coded quantity badges
- Responsive design
- Hover effects on cards
- Empty state handling
