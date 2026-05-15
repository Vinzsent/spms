-- =========================================================================
-- SECURE SCHEMA FOR SERVICE FORMS
-- Adheres to strict normalization, indexing for scalability, 
-- and exact ENUM mapping for security constraints.
-- =========================================================================

-- --------------------------------------------------------
-- 1. Table structure for `service_completion_slips`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_completion_slips` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unit_code` VARCHAR(100) NOT NULL,
  `building` VARCHAR(100) NOT NULL,
  `room_office` VARCHAR(100) NOT NULL,
  `date_serviced` DATE NOT NULL,
  `serviced_by` VARCHAR(150) NOT NULL,
  `contact_no` VARCHAR(50) DEFAULT NULL,
  
  -- Work Done (Stored as strict booleans instead of comma separated text)
  `work_cleaning` TINYINT(1) NOT NULL DEFAULT 0,
  `work_repair` TINYINT(1) NOT NULL DEFAULT 0,
  `work_freon` TINYINT(1) NOT NULL DEFAULT 0,
  `work_electrical` TINYINT(1) NOT NULL DEFAULT 0,
  `work_parts` TINYINT(1) NOT NULL DEFAULT 0,
  `work_other` VARCHAR(255) DEFAULT NULL,
  
  -- Final Status (ENUM for strict input validation)
  `final_status` ENUM('Fully operational', 'Needs follow-up repair', 'Operational but for monitoring', 'Recommended for replacement') NOT NULL,
  
  -- Remarks
  `remarks` TEXT DEFAULT NULL,
  
  -- Admin Actions (Stored as strict booleans)
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_needs_parts` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_for_outsourcing` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_deferred` TINYINT(1) NOT NULL DEFAULT 0,
  
  -- Audit fields
  `created_by_user_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_unit_code` (`unit_code`),
  INDEX `idx_date_serviced` (`date_serviced`),
  INDEX `idx_final_status` (`final_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- 2. Table structure for `unresolved_unit_requests`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `unresolved_unit_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unit_code` VARCHAR(100) NOT NULL,
  `building` VARCHAR(100) NOT NULL,
  `room_office` VARCHAR(100) NOT NULL,
  `date_first_reported` DATE NOT NULL,
  `report_slip_no` VARCHAR(100) DEFAULT NULL,
  `reported_issue` TEXT NOT NULL,
  
  -- Urgency Level
  `urgency_level` ENUM('Low', 'Medium', 'High') NOT NULL,
  
  -- Actions Already Taken
  `action_inspection` TINYINT(1) NOT NULL DEFAULT 0,
  `action_cleaning` TINYINT(1) NOT NULL DEFAULT 0,
  `action_minor_repair` TINYINT(1) NOT NULL DEFAULT 0,
  `action_referred` TINYINT(1) NOT NULL DEFAULT 0,
  `action_waiting_quote` TINYINT(1) NOT NULL DEFAULT 0,
  `action_waiting_parts` TINYINT(1) NOT NULL DEFAULT 0,
  `action_budget_pending` TINYINT(1) NOT NULL DEFAULT 0,
  `action_other` VARCHAR(255) DEFAULT NULL,
  
  -- Requested Action From Admin
  `req_approve_tech` TINYINT(1) NOT NULL DEFAULT 0,
  `req_approve_purchase` TINYINT(1) NOT NULL DEFAULT 0,
  `req_approve_replacement` TINYINT(1) NOT NULL DEFAULT 0,
  `req_other` VARCHAR(255) DEFAULT NULL,
  
  -- Admin Action Status
  `admin_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_for_review` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_need_quote` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_deferred` TINYINT(1) NOT NULL DEFAULT 0,
  `admin_replacement_planning` TINYINT(1) NOT NULL DEFAULT 0,
  
  -- Remarks
  `remarks` TEXT DEFAULT NULL,
  
  -- Receiving info
  `received_by_gso` VARCHAR(150) DEFAULT NULL,
  `date_received_gso` DATE DEFAULT NULL,
  
  -- Audit fields
  `created_by_user_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_unit_code` (`unit_code`),
  INDEX `idx_date_reported` (`date_first_reported`),
  INDEX `idx_urgency` (`urgency_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- 3. Table structure for `problem_report_slips`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `problem_report_slips` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unit_code` VARCHAR(100) NOT NULL,
  `building` VARCHAR(100) NOT NULL,
  `room_office` VARCHAR(100) NOT NULL,
  `date_reported` DATE NOT NULL,
  `reported_by` VARCHAR(150) NOT NULL,
  `contact_no` VARCHAR(50) DEFAULT NULL,
  
  -- Problem Observed
  `obs_not_cooling` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_weak_airflow` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_water_leaking` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_electrical_smell` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_loud_noise` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_wont_turn_on` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_remote_not_working` TINYINT(1) NOT NULL DEFAULT 0,
  `obs_other` VARCHAR(255) DEFAULT NULL,
  
  -- Initial Check (GSO)
  `check_date` DATE DEFAULT NULL,
  `check_by` VARCHAR(150) DEFAULT NULL,
  `check_findings` TEXT DEFAULT NULL,
  
  -- Initial Action (GSO)
  `act_cleaned` TINYINT(1) NOT NULL DEFAULT 0,
  `act_minor_adj` TINYINT(1) NOT NULL DEFAULT 0,
  `act_referred` TINYINT(1) NOT NULL DEFAULT 0,
  `act_parts_ordered` TINYINT(1) NOT NULL DEFAULT 0,
  `act_further_eval` TINYINT(1) NOT NULL DEFAULT 0,
  
  -- Remarks
  `remarks` TEXT DEFAULT NULL,
  
  -- Receiving info
  `received_by_gso` VARCHAR(150) DEFAULT NULL,
  `date_received_gso` DATE DEFAULT NULL,
  
  -- Audit fields
  `created_by_user_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_unit_code` (`unit_code`),
  INDEX `idx_date_reported` (`date_reported`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

