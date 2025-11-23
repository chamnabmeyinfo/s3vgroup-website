-- Fix Team Members Table - Add Missing Columns
-- Run this in phpMyAdmin or MySQL command line
-- This will add all missing columns that TeamMemberRepository requires

-- Add department column
ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `department` VARCHAR(255) NULL AFTER `title`;

-- Add expertise column
ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `expertise` TEXT NULL AFTER `bio`;

-- Add location column
ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `location` VARCHAR(255) NULL AFTER `phone`;

-- Add languages column
ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `languages` VARCHAR(255) NULL AFTER `location`;

-- Add social media columns
ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `twitter` VARCHAR(500) NULL AFTER `linkedin`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `facebook` VARCHAR(500) NULL AFTER `twitter`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `instagram` VARCHAR(500) NULL AFTER `facebook`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `website` VARCHAR(500) NULL AFTER `instagram`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `github` VARCHAR(500) NULL AFTER `website`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `youtube` VARCHAR(500) NULL AFTER `github`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `telegram` VARCHAR(500) NULL AFTER `youtube`;

ALTER TABLE `team_members` 
ADD COLUMN IF NOT EXISTS `whatsapp` VARCHAR(100) NULL AFTER `telegram`;

-- Note: MySQL doesn't support "IF NOT EXISTS" for ALTER TABLE ADD COLUMN
-- If you get "Duplicate column" errors, that means the column already exists (which is fine)
-- Just ignore those errors and continue

