-- Create rooms_inventory table
CREATE TABLE IF NOT EXISTS rooms_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(100) NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    floor VARCHAR(10),
    fluorescent_light INT DEFAULT 0,
    electric_fans_wall INT DEFAULT 0,
    ceiling INT DEFAULT 0,
    chairs_mono INT DEFAULT 0,
    steel INT DEFAULT 0,
    plastic_mini INT DEFAULT 0,
    teacher_table INT DEFAULT 0,
    black_whiteboard INT DEFAULT 0,
    platform INT DEFAULT 0,
    tv INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_room (building_name, room_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data from the image
INSERT INTO rooms_inventory (building_name, room_number, floor, fluorescent_light, electric_fans_wall, ceiling, chairs_mono, steel, plastic_mini, teacher_table, black_whiteboard, platform, tv) VALUES
-- Salusiano Building - First Floor (R)
('Salusiano Building', '101', 'R', 6, 3, 1, 1, 50, 1, 1, 2, 1, 1),
('Salusiano Building', '103', 'R', 8, 4, 4, 1, 47, 1, 1, 1, 1, 1),
('Salusiano Building', '201', 'R', 8, 3, 1, 1, 52, 1, 1, 2, 1, 1),
('Salusiano Building', '202', 'R', 8, 4, 2, 1, 57, 1, 1, 1, 1, 1),
('Salusiano Building', '203', 'R', 8, 1, 4, 1, 47, 1, 1, 1, 1, 1),
('Salusiano Building', '204', 'R', 8, 1, 2, 1, 45, 1, 1, 1, 1, 1),

-- Salusiano Building - Second Floor (N)
('Salusiano Building', '301', 'N', 8, 1, 2, 1, 1, 1, 1, 1, 1, 1),
('Salusiano Building', '302', 'N', 8, 2, 1, 1, 49, 1, 1, 1, 1, 1),
('Salusiano Building', '303', 'N', 8, 1, 4, 1, 1, 1, 1, 1, 1, 1),
('Salusiano Building', '304', 'N', 8, 1, 3, 1, 1, 1, 1, 1, 1, 1),
('Salusiano Building', '305', 'N', 8, 4, 2, 1, 1, 1, 1, 1, 1, 1),
('Salusiano Building', '306', 'N', 8, 4, 2, 1, 2, 1, 1, 1, 1, 1),

-- Salusiano Building - Third Floor (O)
('Salusiano Building', '401', 'O', 6, 3, 2, 1, 57, 1, 1, 1, 1, 1),
('Salusiano Building', '402', 'O', 6, 3, 2, 1, 52, 1, 1, 1, 1, 1),
('Salusiano Building', '403', 'O', 6, 1, 6, 1, 50, 1, 1, 2, 1, 1),
('Salusiano Building', '404', 'O', 6, 1, 6, 1, 38, 1, 1, 1, 1, 1),
('Salusiano Building', '405', 'O', 9, 4, 1, 1, 45, 1, 1, 1, 1, 1),
('Salusiano Building', '406', 'O', 6, 1, 4, 1, 1, 1, 1, 1, 1, 1);
