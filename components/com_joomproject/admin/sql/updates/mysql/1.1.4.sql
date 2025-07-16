CREATE TABLE IF NOT EXISTS `#__jp_reminders` (
    `id` int(10)  UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `task_id` int(10) UNSIGNED NOT NULL,
    `assigned_users` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `amount` int(10) NOT NULL,
    `types` int(10) NOT NULL,
    `repeats` int(10) NOT NULL,
    `state` tinyint(3) NOT NULL DEFAULT '0',
    `access` int(10) NOT NULL,
    `attribs` varchar(5120) COLLATE utf8mb4_unicode_ci NOT NULL,
    `ordering` int(11) NOT NULL,
    `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_by` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `modified_by` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_reminders_dates` (
`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`reminder_id` int(10) UNSIGNED NOT NULL,
`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;