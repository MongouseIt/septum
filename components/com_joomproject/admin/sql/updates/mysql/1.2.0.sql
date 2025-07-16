SET SESSION innodb_strict_mode=OFF;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
ALTER TABLE `#__jp_milestones` ADD `catid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID' AFTER `project_id`;