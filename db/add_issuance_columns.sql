-- Add status tracking columns to supply_request table
-- This script adds columns for tracking the approval workflow

-- Add noted columns
ALTER TABLE `supply_request` 
ADD COLUMN `noted_by` varchar(100) NULL AFTER `amount`,
ADD COLUMN `noted_date` datetime NULL AFTER `noted_by`;

-- Add checked columns
ALTER TABLE `supply_request` 
ADD COLUMN `checked_by` varchar(100) NULL AFTER `noted_date`,
ADD COLUMN `checked_date` datetime NULL AFTER `checked_by`;

-- Add verified columns
ALTER TABLE `supply_request` 
ADD COLUMN `verified_by` varchar(100) NULL AFTER `checked_date`,
ADD COLUMN `verified_date` datetime NULL AFTER `verified_by`;

-- Add issued columns
ALTER TABLE `supply_request` 
ADD COLUMN `issued_by` varchar(100) NULL AFTER `verified_date`,
ADD COLUMN `issued_date` datetime NULL AFTER `issued_by`;

-- Add approved columns
ALTER TABLE `supply_request` 
ADD COLUMN `approved_by` varchar(100) NULL AFTER `issued_date`,
ADD COLUMN `approved_date` datetime NULL AFTER `approved_by`;

-- Add remarks column for additional notes
ALTER TABLE `supply_request` 
ADD COLUMN `remarks` text NULL AFTER `approved_date`; 