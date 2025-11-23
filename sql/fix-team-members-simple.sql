-- SIMPLE FIX: Add Missing Columns to team_members Table
-- Copy and paste this into phpMyAdmin SQL tab
-- If you get "Duplicate column" errors, ignore them (column already exists)

ALTER TABLE `team_members` ADD COLUMN `department` VARCHAR(255) NULL AFTER `title`;
ALTER TABLE `team_members` ADD COLUMN `expertise` TEXT NULL AFTER `bio`;
ALTER TABLE `team_members` ADD COLUMN `location` VARCHAR(255) NULL AFTER `phone`;
ALTER TABLE `team_members` ADD COLUMN `languages` VARCHAR(255) NULL AFTER `location`;
ALTER TABLE `team_members` ADD COLUMN `twitter` VARCHAR(500) NULL AFTER `linkedin`;
ALTER TABLE `team_members` ADD COLUMN `facebook` VARCHAR(500) NULL AFTER `twitter`;
ALTER TABLE `team_members` ADD COLUMN `instagram` VARCHAR(500) NULL AFTER `facebook`;
ALTER TABLE `team_members` ADD COLUMN `website` VARCHAR(500) NULL AFTER `instagram`;
ALTER TABLE `team_members` ADD COLUMN `github` VARCHAR(500) NULL AFTER `website`;
ALTER TABLE `team_members` ADD COLUMN `youtube` VARCHAR(500) NULL AFTER `github`;
ALTER TABLE `team_members` ADD COLUMN `telegram` VARCHAR(500) NULL AFTER `youtube`;
ALTER TABLE `team_members` ADD COLUMN `whatsapp` VARCHAR(100) NULL AFTER `telegram`;

