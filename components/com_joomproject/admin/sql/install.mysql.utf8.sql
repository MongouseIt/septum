CREATE TABLE IF NOT EXISTS `#__jp_comments`
(
    `id`               int(10) unsigned                                 NOT NULL AUTO_INCREMENT COMMENT 'Comment ID',
    `asset_id`         int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `item_id`          int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Parent item ID',
    `context`          varchar(50)                                      NOT NULL DEFAULT '' COMMENT 'Context reference',
    `title`            varchar(255)                                     NOT NULL COMMENT 'The context item title',
    `alias`            varchar(400) NOT NULL DEFAULT '' ,
    `description`      text                                             DEFAULT NULL COMMENT 'Comment content',
    `created`          datetime         NULL COMMENT 'Comment creation date',
    `created_by`       int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Comment author',
    `modified`         datetime         NULL COMMENT 'Comment modify date',
    `modified_by`      int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Last user to modify the comment',
    `checked_out`      int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the comment',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text                                             DEFAULT NULL COMMENT 'Comment attributes in JSON format',
    `state`            tinyint(3)                                       NOT NULL DEFAULT '0' COMMENT 'Comment state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `parent_id`        int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference ID',
    `lft`              int(11)                                          NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
    `rgt`              int(11)                                          NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
    `level`            int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Nested comment level',
    `path`             varchar(255)                                     NOT NULL DEFAULT '' ,
    PRIMARY KEY (`id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_contextitemid` (`context`, `item_id`),
    KEY `idx_state` (`state`),
    KEY `idx_parentid` (`parent_id`),
    KEY `idx_nested` (`lft`, `rgt`),
    -- New optimized indexes for hierarchical queries
    KEY `idx_state_parent` (`state`, `parent_id`),
    KEY `idx_state_level` (`state`, `level`),
    KEY `idx_path_state` (`path`(191), `state`),
    KEY `idx_lft_state` (`lft`, `state`, `rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__jp_labels`
(
    `id`          int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Label ID',
    `project_id`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The parent project id',
    `title`       varchar(255)      NOT NULL DEFAULT '' COMMENT 'Label title',
    `style`       varchar(24)      NOT NULL DEFAULT '' COMMENT 'Label CSS style',
    `asset_group` varchar(50)      NOT NULL DEFAULT '' COMMENT 'Assigned label asset group',
    PRIMARY KEY (`id`),
    KEY `idx_group` (`project_id`, `asset_group`),
    KEY `idx_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_milestones`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Milestone ID',
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `catid`            int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID',
    `title`            varchar(255)     NOT NULL DEFAULT '' COMMENT 'Milestone title',
    `alias`            varchar(400)     NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`       LONGTEXT     DEFAULT NULL COMMENT 'Milestone description',
    `created`          datetime         NULL COMMENT 'Milestone creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Milestone author',
    `modified`         datetime         NULL COMMENT 'Milestone modify date',
    `modified_by`      int(10) NOT NULL DEFAULT '0' COMMENT 'Last user to modify the milestone',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the milestone',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Milestone attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Milestone ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Milestone state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `ordering`         int(10)          NOT NULL DEFAULT '0' COMMENT 'Milestone ordering',
    `start_date`       datetime         NULL COMMENT 'Milestone start date',
    `end_date`         datetime         NULL COMMENT 'Milestone end date',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`(191), `project_id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_groups_view_action`
(
    `id`        int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'group view action ID',
    `itemid`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'item id',
    `groupid`   int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Group id',
    `type`      varchar(128)     NOT NULL DEFAULT '' COMMENT 'Item type like project, task ....',
    `component` varchar(128)     NOT NULL DEFAULT '' ,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__jp_projects`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Project ID',
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `catid`            int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID',
    `title`            varchar(255)     NOT NULL DEFAULT '' COMMENT 'Project title',
    `alias`            varchar(400)     NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      text             DEFAULT NULL COMMENT 'Project description',
    `gallery_items`    text             DEFAULT NULL,
    `created`          datetime         NULL COMMENT 'Project creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Project owner',
    `modified`         datetime         NULL COMMENT 'Project modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the project',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the project',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Project attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Project ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Project state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
    `start_date`       datetime         NULL COMMENT 'Project start date',
    `end_date`         datetime         NULL COMMENT 'Project end date',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`(191)),
    KEY `idx_catid` (`catid`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_ref_attachments`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Reference ID',
    `item_type`  varchar(32)      NOT NULL DEFAULT '' COMMENT 'The item type',
    `item_id`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The item id',
    `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The project id',
    `attachment` varchar(128)     NOT NULL DEFAULT '' COMMENT 'The attachment type and id',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_connection` (`attachment`, `item_id`, `item_type`),
    KEY `idx_item` (`item_type`, `item_id`),
    KEY `idx_project` (`project_id`),
    KEY `idx_attachment` (`attachment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_ref_observer`
(
    `user_id`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The observing user',
    `item_type`  varchar(50)      NOT NULL DEFAULT '' COMMENT 'The observed item type',
    `item_id`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The observed item ID',
    `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Project ID to which the item belongs',
    UNIQUE KEY `idx_observing` (`item_type`, `item_id`, `user_id`),
    KEY `idx_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_ref_labels`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID reference',
    `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project id',
    `item_id`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Reference item ID',
    `item_type`  varchar(50)      NOT NULL DEFAULT '' COMMENT 'Reference item type',
    `label_id`   int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Reference label ID',
    PRIMARY KEY (`id`),
    KEY `idx_project` (`project_id`),
    KEY `idx_item` (`item_id`, `item_type`),
    KEY `idx_lbl` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_ref_tasks`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID reference',
    `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task project ID',
    `task_id`    int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task ID',
    `parent_id`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent task ID',
    PRIMARY KEY (`id`),
    KEY `idx_task` (`task_id`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_ref_users`
(
    `id`        int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID reference',
    `item_type` varchar(50)      NOT NULL DEFAULT '' COMMENT 'The item type',
    `item_id`   int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The item id',
    `user_id`   int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID reference',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user` (`user_id`, `item_id`, `item_type`),
    KEY `idx_item` (`item_type`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_tags`
(
    `id`    int(10) unsigned NOT NULL COMMENT 'Tag ID',
    `title` varchar(255)      NOT NULL DEFAULT '' COMMENT 'Tag title',
    `alias` varchar(400)      NOT NULL DEFAULT '' COMMENT 'Tag alias',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_tasks`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Task ID',
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `list_id`          int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent task list ID',
    `milestone_id`     int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent milestone ID',
    `title`            varchar(255)     NOT NULL DEFAULT '' COMMENT 'Task title',
    `alias`            varchar(400)     NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      text             DEFAULT NULL             COMMENT 'Task description',
    `created`          datetime         NULL COMMENT 'Task creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task author',
    `modified`         datetime         NULL COMMENT 'Task modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the task',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the task',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL             COMMENT 'Task attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Task state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `priority`         tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Task priority ID',
    `complete`         tinyint(1)       NOT NULL DEFAULT '0' COMMENT 'Task complete state',
    `completed`        datetime         NULL COMMENT 'Task completition date',
    `completed_by`     int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user who completed the task',
    `ordering`         int(10)          NOT NULL DEFAULT '0' COMMENT 'Task ordering in a task list',
    `start_date`       datetime         NULL COMMENT 'Task start date',
    `end_date`         datetime         NULL COMMENT 'Task end date',
    `rate`             decimal(5, 2)    NOT NULL DEFAULT '0.00' COMMENT 'Hourly rate',
    `estimate`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Estimated time required for this task to complete',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`project_id`, `milestone_id`, `list_id`, `alias`(191)),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_listid` (`list_id`),
    KEY `idx_milestone` (`milestone_id`),
    KEY `idx_access` (`access`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_priority` (`priority`),
    KEY `idx_complete` (`complete`),
    KEY `idx_state` (`state`),
    KEY `idx_completedby` (`completed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_task_lists`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Task list ID',
    `asset_id`         int(10)          NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `milestone_id`     int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent milestone ID',
    `title`            varchar(255)      NOT NULL DEFAULT '' COMMENT 'Task list title',
    `alias`            varchar(400)      NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      LONGTEXT   DEFAULT NULL COMMENT 'Task list description',
    `created`          datetime         NULL COMMENT 'Task list creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task list creator',
    `modified`         datetime         NULL COMMENT 'Task list modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the task list',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the task list',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Task list attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task List ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Task list state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `ordering`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Task list ordering',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`project_id`, `alias`(191), `milestone_id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_milestoneid` (`milestone_id`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_state` (`state`),
    KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_topics`
(
    `id`               int(10) unsigned                                 NOT NULL AUTO_INCREMENT COMMENT 'Topic ID',
    `asset_id`         int(10)                                          NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `title`            varchar(255)                                     NOT NULL DEFAULT '' COMMENT 'Topic title',
    `alias`            varchar(400) NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      text                                             DEFAULT NULL COMMENT 'Topic content text',
    `created`          datetime         NULL COMMENT 'Topic creation date',
    `created_by`       int(10) unsigned                                 NOT NULL COMMENT 'Topic author',
    `modified`         datetime         NULL COMMENT 'Topic modify date',
    `modified_by`      int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Last user to modify the topic',
    `checked_out`      int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the topic',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text                                             DEFAULT NULL COMMENT 'Topic attributes in JSON format',
    `access`           int(10) unsigned                                 NOT NULL DEFAULT '0' COMMENT 'Topic ACL access level ID',
    `state`            tinyint(3)                                       NOT NULL DEFAULT '0' COMMENT 'Topic state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`project_id`, `alias`(191)),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_replies`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Topic ID',
    `asset_id`         int(10)          NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `topic_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent topic ID',
    `description`      text             DEFAULT NULL COMMENT 'Reply content text',
    `created`          datetime         NULL COMMENT 'Reply creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Reply author',
    `modified`         datetime         NULL COMMENT 'Reply modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the reply',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the reply',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Reply attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Reply ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Reply state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed',
    PRIMARY KEY (`id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_topicid` (`topic_id`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_timesheet`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT,
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `task_id`          int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent task ID',
    `task_title`       varchar(255)     NOT NULL DEFAULT '' COMMENT 'Parent task title',
    `description`      LONGTEXT   DEFAULT NULL COMMENT 'Description text',
    `created`          datetime         NULL COMMENT 'Creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Time author',
    `modified`         datetime         NULL COMMENT 'Time modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the record',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing this record',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Record attributes in JSON format',
    `billable`         tinyint(1)       NOT NULL DEFAULT '0' COMMENT '1 = Billable, 0 = Unbillable',
    `rate`             decimal(10,2)    NOT NULL DEFAULT 0.00 COMMENT 'Hourly rate',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Record ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Record state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `log_date`         datetime         NULL COMMENT 'Time log date',
    `log_time`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Time log seconds',
    PRIMARY KEY (`id`),
    KEY `idx_project` (`project_id`),
    KEY `idx_task` (`task_id`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`),
    KEY `idx_billable` (`billable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_repo_dirs`
(
    `id`               int(10) unsigned                                NOT NULL AUTO_INCREMENT COMMENT 'Directory ID',
    `asset_id`         int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent project id',
    `title`            varchar(255)                                     NOT NULL DEFAULT '' COMMENT 'Directory title',
    `alias`            varchar(400) NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      LONGTEXT   DEFAULT NULL COMMENT 'Directory description text',
    `created`          datetime         NULL COMMENT 'Directory creation date',
    `created_by`       int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Directory author',
    `modified`         datetime         NULL COMMENT 'Directory modify date',
    `modified_by`      int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Last user to modify the directory',
    `checked_out`      int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the directory',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text                                            DEFAULT NULL COMMENT 'Directory attributes in JSON format',
    `access`           int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Directory ACL access level ID',
    `protected`        tinyint(1)                                      NOT NULL DEFAULT '0' COMMENT 'If set to 1, directories cannot be deleted manually',
    `parent_id`        int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference ID',
    `lft`              int(10)                                         NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
    `rgt`              int(10)                                         NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
    `level`            int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Nested directory level',
    `path`             text DEFAULT NULL COMMENT 'Directory path',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`(191), `parent_id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_parentid` (`parent_id`),
    KEY `idx_nested` (`lft`, `rgt`),
    KEY `idx_access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_repo_notes`
(
    `id`               int(10) unsigned                                NOT NULL AUTO_INCREMENT COMMENT 'Note ID',
    `asset_id`         int(10)                                         NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `dir_id`           int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent directory ID',
    `title`            varchar(255)                                     NOT NULL DEFAULT '' COMMENT 'Note title',
    `alias`            varchar(400) NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      text                                            DEFAULT NULL COMMENT 'Note content text',
    `created`          datetime         NULL COMMENT 'Note creation date',
    `created_by`       int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Note author',
    `modified`         datetime         NULL COMMENT 'Note modify date',
    `modified_by`      int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Last user to modify the note',
    `checked_out`      int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the note',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text                                            DEFAULT NULL COMMENT 'Note attributes in JSON format',
    `access`           int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Note ACL access level ID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`dir_id`, `alias`(191)),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_dirid` (`dir_id`),
    KEY `idx_access` (`access`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_repo_files`
(
    `id`               int(10) unsigned                                NOT NULL AUTO_INCREMENT COMMENT 'File ID',
    `asset_id`         int(10)                                         NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `dir_id`           int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent directory ID',
    `title`            varchar(255)                                     NOT NULL DEFAULT '' COMMENT 'File title',
    `alias`            varchar(400) NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`      LONGTEXT   DEFAULT NULL  COMMENT 'File description',
    `file_name`        varchar(255)                                    NOT NULL DEFAULT '' COMMENT 'The file name',
    `file_extension`   varchar(32)                                     NOT NULL DEFAULT '' COMMENT 'The file extension name',
    `file_size`        int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'The file size in kilobyte',
    `created`          datetime         NULL COMMENT 'File creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'File author',
    `modified`         datetime         NULL COMMENT 'File modify date',
    `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
    `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User who is currently editing the file',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text                                            DEFAULT NULL COMMENT 'File attributes in JSON format',
    `access`           int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'File ACL access level ID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`dir_id`, `alias`(191)),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_dirid` (`dir_id`),
    KEY `idx_access` (`access`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_checkedout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_repo_file_revs`
(
    `id`             int(10) unsigned                                NOT NULL AUTO_INCREMENT COMMENT 'File ID',
    `project_id`     int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `parent_id`      int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'File head revision id',
    `title`          varchar(255)                                     NOT NULL DEFAULT '' COMMENT 'File title',
    `alias`          varchar(400) NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description`     LONGTEXT   DEFAULT NULL COMMENT 'File description',
    `file_name`      varchar(255)                                    NOT NULL DEFAULT '' COMMENT 'The file name',
    `file_extension` varchar(32)                                     NOT NULL DEFAULT '' COMMENT 'The file extension name',
    `file_size`      int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'The file size in kilobyte',
    `created`        datetime         NULL COMMENT 'File creation date',
    `created_by`     int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'File author',
    `attribs`        text                                            DEFAULT NULL COMMENT 'File attributes in JSON format',
    `ordering`       int(10)                                         NOT NULL DEFAULT '0' COMMENT 'File revision number',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`(191), `parent_id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_repo_note_revs`
(
    `id`          int(10) unsigned                                NOT NULL AUTO_INCREMENT COMMENT 'Note ID',
    `project_id`  int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `parent_id`   int(10)                                         NOT NULL DEFAULT '0' COMMENT 'Parent Note ID',
    `title`       varchar(255)                                     NOT NULL DEFAULT '' COMMENT 'Note title',
    `alias`       varchar(400) NOT NULL DEFAULT '' COMMENT 'Title alias. Used in SEF URL''s',
    `description` text                                            NULL COMMENT 'Note content text',
    `created`     datetime         NULL COMMENT 'Note creation date',
    `created_by`  int(10) unsigned                                NOT NULL DEFAULT '0' COMMENT 'Note author',
    `attribs`     text                                            NULL COMMENT 'Note attributes in JSON format',
    `ordering`    int(10)                                         NOT NULL DEFAULT '0' COMMENT 'Note revision number',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`alias`(191), `parent_id`),
    KEY `idx_projectid` (`project_id`),
    KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_emailqueue`
(
    `id`      int(10) unsigned NOT NULL AUTO_INCREMENT,
    `email`   varchar(100)     NOT NULL DEFAULT '' COMMENT 'Recipient email address',
    `subject` text             NULL COMMENT 'Email subject',
    `message` text             NULL COMMENT 'Email message',
    `created` datetime         NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_design_albums`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT,
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `title`            varchar(255)     NOT NULL DEFAULT '' COMMENT 'Album title',
    `alias`            varchar(400)     NOT NULL DEFAULT '',
    `description`      LONGTEXT   DEFAULT NULL COMMENT 'Album description text',
    `created`          datetime         NULL COMMENT 'Album creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Album author ID',
    `modified`         datetime         NULL COMMENT 'Album modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the album',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0'  COMMENT 'The user currently editing the album',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Album attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Album ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Album state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `ordering`         int(10)          NOT NULL DEFAULT '0' COMMENT 'Album display order',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`project_id`, `alias`(191)),
    KEY `idx_project` (`project_id`),
    KEY `idx_author` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_designs`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT,
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `album_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent album ID',
    `title`            varchar(255)     NOT NULL DEFAULT '' COMMENT 'Design title',
    `alias`            varchar(400)     NOT NULL DEFAULT '',
    `description`      text             DEFAULT NULL COMMENT 'Design description text',
    `file_name`        varchar(255)     NOT NULL DEFAULT '' COMMENT 'Media file name',
    `file_extension`   varchar(32)      NOT NULL DEFAULT '' COMMENT 'The media file extension name',
    `file_size`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The media file size in kilobyte',
    `thumbnail`        varchar(255)     NOT NULL DEFAULT '' COMMENT 'The media custom thumbnail',
    `created`          datetime         NULL COMMENT 'Design creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Design author ID',
    `modified`         datetime         NULL COMMENT 'Design modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Last user to modify the design',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user currently editing the design',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Design attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Design ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Design state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `ordering`         int(10)          NOT NULL DEFAULT '0' COMMENT 'Design display order',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`project_id`, `album_id`, `alias`(191)),
    KEY `idx_project` (`project_id`),
    KEY `idx_album` (`album_id`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`),
    KEY `idx_author` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_designs_approved`
(
    `id`          int(10) unsigned    NOT NULL COMMENT 'The design ID',
    `revision_id` int(10) unsigned    NOT NULL DEFAULT '0' COMMENT 'The Revision ID',
    `created_by`  int(10) unsigned    NOT NULL DEFAULT '0' COMMENT 'The author ID',
    `created`     datetime         NULL COMMENT 'The approval date',
    `state`       tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'The approval state',
    PRIMARY KEY (`id`, `revision_id`, `created_by`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_design_revisions`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT,
    `asset_id`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
    `project_id`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent project ID',
    `parent_id`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The parent design ID',
    `title`            varchar(255)     NOT NULL DEFAULT '' COMMENT 'Design revision title',
    `alias`            varchar(400)     NOT NULL DEFAULT '' ,
    `description`      text             DEFAULT NULL COMMENT 'Design revision description text',
    `file_name`        varchar(255)     NOT NULL DEFAULT ''  COMMENT 'File name',
    `file_extension`   varchar(32)      NOT NULL DEFAULT '' COMMENT 'The file extension name',
    `file_size`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The file size in kilobyte',
    `created`          datetime         NULL COMMENT 'Design revision creation date',
    `created_by`       int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Design revision author ID',
    `modified`         datetime         NULL COMMENT 'Design revision modify date',
    `modified_by`      int(10) unsigned NOT NULL DEFAULT '0'  COMMENT 'Last user to modify the design revision',
    `checked_out`      int(10) unsigned NOT NULL DEFAULT '0'  COMMENT 'The user currently editing the design revision',
    `checked_out_time` datetime         NULL COMMENT 'Check-out date and time',
    `attribs`          text             DEFAULT NULL COMMENT 'Design revision attributes in JSON format',
    `access`           int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Design revision ACL access level ID',
    `state`            tinyint(3)       NOT NULL DEFAULT '0' COMMENT 'Design revision state: 1 = Active, 0 = Inactive, 2 = Archived, -2 = Trashed ',
    `ordering`         int(10)          NOT NULL COMMENT 'Design revision number',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_alias` (`project_id`, `parent_id`, `alias`(191)),
    KEY `idx_project` (`project_id`),
    KEY `idx_design` (`parent_id`),
    KEY `idx_author` (`created_by`),
    KEY `idx_checkedout` (`checked_out`),
    KEY `idx_access` (`access`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_reminders`
(
    `id`               int(10) UNSIGNED                         NOT NULL AUTO_INCREMENT,
    `asset_id`         int(10) UNSIGNED                         NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
    `task_id`          int(10) UNSIGNED                         NOT NULL DEFAULT '0',
    `assigned_users`   varchar(255)  NOT NULL DEFAULT '' ,
    `description`      text                                     DEFAULT NULL,
    `start_date`       datetime                                 NULL,
    `amount`           int(10)                                  NOT NULL DEFAULT '0',
    `types`            int(10)                                  NOT NULL DEFAULT '0',
    `repeats`          int(10)                                  NOT NULL DEFAULT '0',
    `state`            tinyint(3)                               NOT NULL DEFAULT '0',
    `access`           int(10)                                  NOT NULL DEFAULT '0',
    `attribs`          varchar(5120) NOT NULL DEFAULT '' ,
    `ordering`         int(11)                                  NOT NULL,
    `created`          datetime                                 NULL,
    `created_by`       int(10) UNSIGNED                         NOT NULL DEFAULT '0',
    `modified`         datetime                                 NULL,
    `modified_by`      int(10) UNSIGNED                         NOT NULL DEFAULT '0',
    `checked_out`      int(10) UNSIGNED                         NOT NULL DEFAULT '0',
    `checked_out_time` datetime                                 NULL ,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_reminders_dates`
(
    `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `reminder_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `date`        datetime         NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;




INSERT IGNORE INTO `#__jp_comments` (`id`, `asset_id`, `project_id`, `item_id`, `context`, `title`, `alias`,
                                     `description`, `created`, `created_by`, `modified`, `modified_by`, `checked_out`,
                                     `checked_out_time`, `attribs`, `state`, `parent_id`, `lft`, `rgt`, `level`, `path`)
VALUES (1, 0, 0, 0, 'system', 'ROOT', 'root', '', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 0,
        '0000-00-00 00:00:00', '', 1, 0, 0, 1, 0, '');

INSERT IGNORE INTO `#__jp_repo_dirs` (`id`, `asset_id`, `project_id`, `title`, `alias`, `description`, `created`,
                                      `created_by`, `modified`, `modified_by`, `checked_out`, `checked_out_time`,
                                      `attribs`, `access`, `protected`, `parent_id`, `lft`, `rgt`, `level`, `path`)
VALUES (1, 0, 0, 'Root', 'root', '', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', '',
        1, 1, 0, 0, 1, 0, '');


CREATE TABLE IF NOT EXISTS `#__jp_users_teams`
(
    `id`       int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `title`  varchar(255)             NOT NULL DEFAULT '',
    `state`    tinyint(3)          NOT NULL DEFAULT 0,
    `ordering` int(11)             NOT NULL DEFAULT 0,
    `created` datetime DEFAULT NULL,
    `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `modified` datetime DEFAULT NULL,
    `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` datetime DEFAULT NULL,
    `access` int(10) UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_users_team_user_map`
(
    `user_id` int(11)             NOT NULL DEFAULT 0,
    `team_id` int(11)             NOT NULL DEFAULT 0
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_users_roles`
(
    `id`       int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `title`  varchar(255)             NOT NULL DEFAULT '',
    `state`    tinyint(3)          NOT NULL DEFAULT 0,
    `ordering` int(11)             NOT NULL DEFAULT 0,
    `created` datetime DEFAULT NULL,
    `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `modified` datetime DEFAULT NULL,
    `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` datetime DEFAULT NULL,
    `access` int(10) UNSIGNED NOT NULL DEFAULT 0
    )
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jp_users_role_action_map`
(
    `role_id` int(11)             NOT NULL DEFAULT 0,
    `action`  varchar(255)             NOT NULL DEFAULT '',
    `context`  varchar(255)             NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__jp_users_users`
(
    `id`       int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `user_id` int(11)             NOT NULL DEFAULT 0,
    `role_id` int(11)             NOT NULL DEFAULT 0,
    `ordering` int(11)             NOT NULL DEFAULT 0,
    `created` datetime DEFAULT NULL,
    `created_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `modified` datetime DEFAULT NULL,
    `modified_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `checked_out_time` datetime DEFAULT NULL,
    `access` int(10) UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS `#__jp_import`
(
    `id`       int(10) NOT NULL AUTO_INCREMENT,
    `name`     varchar(40) DEFAULT NULL,
    `params`   text DEFAULT NULL,
    `user_id`  int(11)     DEFAULT '0',
    `crossids` text DEFAULT NULL,
    `type_id`  int(11)     DEFAULT '0',
    `ctime`    datetime    DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__jp_import_rows`
(
    `id`     int(10) NOT NULL AUTO_INCREMENT,
    `import` int(11)  DEFAULT '0',
    `text`   text DEFAULT NULL,
    `ctime`  datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_import` (`import`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;