CREATE TABLE IF NOT EXISTS `#__user_activity` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
    `item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the user_activity_items table',
    `event_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the user_activity_events table',
    `client_id` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Location ID. 0 = Site, 1 = Admin',
    `created` datetime NULL ,
    `created_day` smallint(5) NOT NULL DEFAULT '0' COMMENT 'Days since unix epoch. Used to better index records by date',
    `created_by` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the users table',
    `delta_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Minutes past since the last activity of the same type, event and user. Used for grouping.',
    `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Record state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
    `vaccess` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the viewlevels table',
    PRIMARY KEY (`id`),
    KEY `idx_event_by` (`event_id`,`created_by`),
    KEY `idx_client_state` (`client_id`,`state`),
    KEY `idx_day` (`created_day`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user activity';

CREATE TABLE IF NOT EXISTS `#__user_activity_events` (
    `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
    `name` varchar(16) NOT NULL DEFAULT '' COMMENT 'Event name',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user activity events';

CREATE TABLE IF NOT EXISTS `#__user_activity_items` (
    `asset_id` int(10) unsigned NOT NULL COMMENT 'Primary Key and FK to the assets table',
    `type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the user_activity_item_types table',
    `xref_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Cross Reference ID. Plugin controlled',
    `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The id of the item itself',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'Last known item title',
    `state` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Last known item state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
    `vaccess` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the viewlevels table',
    `metadata` varchar(1530) NOT NULL DEFAULT '' COMMENT 'Item meta info such as category title',
    PRIMARY KEY (`asset_id`),
    KEY `idx_type_id` (`type_id`),
    KEY `idx_xref_id` (`xref_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores user activity item information';

CREATE TABLE IF NOT EXISTS `#__user_activity_item_types` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `plugin` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'The type and name of the plugin that handles this type',
    `extension` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'The extension name',
    `name` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'The item type name',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user activity item type information';

REPLACE INTO `#__user_activity_events` VALUES
                                              (1,'save_new'),
                                              (2,'save_update'),
                                              (3,'publish'),
                                              (4,'unpublish'),
                                              (5,'archive'),
                                              (6,'trash'),
                                              (7,'delete');
