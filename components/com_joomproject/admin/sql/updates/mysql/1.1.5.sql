CREATE TABLE IF NOT EXISTS `#__jp_groups_view_action`
(
    `id`        int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'group view action ID',
    `itemid`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'item id',
    `groupid`   int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Group id',
    `type`      varchar(128)     NOT NULL COMMENT 'Item type like project, task ....',
    `component` varchar(128)     NOT NULL,
    PRIMARY KEY (`id`)
) DEFAULT CHARSET = utf8 COMMENT ='Stores Groups that can view the item';