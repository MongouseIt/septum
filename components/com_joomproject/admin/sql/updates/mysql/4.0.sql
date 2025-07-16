SET SESSION innodb_strict_mode=OFF;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
--
-- Table structure for table `#__jp_comments`
--
ALTER TABLE `#__jp_comments` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_comments` CHANGE `context` `context` varchar (50) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_comments` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_comments` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_comments` CHANGE `description` `description` text  DEFAULT NULL;
ALTER TABLE `#__jp_comments` CHANGE `created` `created`  datetime NULL;
ALTER TABLE `#__jp_comments` CHANGE `modified` `modified`  datetime NULL;
ALTER TABLE `#__jp_comments` CHANGE `attribs` `attribs`  text  DEFAULT NULL;
ALTER TABLE `#__jp_comments` CHANGE `parent_id` `parent_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_comments` CHANGE `lft` `lft` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_comments` CHANGE `rgt` `rgt` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_comments` CHANGE `level` `level` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_comments` CHANGE `path` `path` varchar(255) NOT NULL DEFAULT '';
--
-- Table structure for table `#__jp_labels`
--
ALTER TABLE `#__jp_labels` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_labels` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_labels` CHANGE `style` `style` varchar(24) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_labels` CHANGE `asset_group` `asset_group` varchar(50) NOT NULL DEFAULT '';
--
-- Table structure for table `#__jp_milestones`
--
ALTER TABLE `#__jp_milestones` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_milestones` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_milestones` CHANGE `description` `description` LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_milestones` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_milestones` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_milestones` CHANGE `checked_out_time` `checked_out_time`  datetime NULL ;
ALTER TABLE `#__jp_milestones` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_milestones` CHANGE `start_date` `start_date`  datetime NULL ;
ALTER TABLE `#__jp_milestones` CHANGE `end_date` `end_date`  datetime NULL ;
--
-- Table structure for table `#__jp_groups_view_action`
--
ALTER TABLE `#__jp_groups_view_action` CHANGE `type` `type` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_groups_view_action` CHANGE `component` `component` varchar(128) NOT NULL DEFAULT '';
--
-- Table structure for table `#__jp_projects`
--
ALTER TABLE `#__jp_projects` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_projects` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_projects` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jp_projects` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_projects` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_projects` CHANGE `checked_out_time` `checked_out_time`  datetime NULL ;
ALTER TABLE `#__jp_projects` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_projects` CHANGE `start_date` `start_date`  datetime NULL ;
ALTER TABLE `#__jp_projects` CHANGE `end_date` `end_date`  datetime NULL ;
--
-- Table structure for table `#__jp_ref_attachments`
--
ALTER TABLE `#__jp_ref_attachments` CHANGE `item_type` `item_type` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_ref_attachments` CHANGE `item_id` `item_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_attachments` CHANGE `attachment` `attachment` varchar(128) NOT NULL DEFAULT '';
--
-- Table structure for table `#__jp_ref_observer`
--
ALTER TABLE `#__jp_ref_observer` CHANGE `user_id` `user_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_observer` CHANGE `item_type` `item_type` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_ref_observer` CHANGE `item_id` `item_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_observer` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_ref_labels`
--
ALTER TABLE `#__jp_ref_labels` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_labels` CHANGE `item_type` `item_type` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_ref_labels` CHANGE `item_id` `item_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_labels` CHANGE `label_id` `label_id` int(10) NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_ref_tasks`
--
ALTER TABLE `#__jp_ref_tasks` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_tasks` CHANGE `task_id` `task_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_ref_tasks` CHANGE `parent_id` `parent_id` int(10) NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_ref_users`
--
ALTER TABLE `#__jp_ref_users` CHANGE `item_type` `item_type` varchar(50) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_ref_users` CHANGE `item_id` `item_id` int(10) NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_tags`
--
ALTER TABLE `#__jp_tags` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_tags` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
--
-- Table structure for table `#__jp_tasks`
--
ALTER TABLE `#__jp_tasks` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_tasks` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_tasks` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jp_tasks` CHANGE `created` `created`  datetime NULL;
ALTER TABLE `#__jp_tasks` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_tasks` CHANGE `checked_out_time` `checked_out_time`  datetime NULL ;
ALTER TABLE `#__jp_tasks` CHANGE `attribs` `attribs`  text  DEFAULT NULL;
ALTER TABLE `#__jp_tasks` CHANGE `start_date` `start_date`  datetime NULL ;
ALTER TABLE `#__jp_tasks` CHANGE `end_date` `end_date`  datetime NULL ;
ALTER TABLE `#__jp_tasks` CHANGE `completed` `completed`  datetime NULL ;
ALTER TABLE `#__jp_tasks` CHANGE `completed_by` `completed_by` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_tasks` CHANGE `rate` `rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `#__jp_tasks` CHANGE `estimate` `estimate` INT(10) NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_task_lists`
--
ALTER TABLE `#__jp_task_lists` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_task_lists` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_task_lists` CHANGE `description` `description` LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_task_lists` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_task_lists` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_task_lists` CHANGE `checked_out_time` `checked_out_time`  datetime NULL ;
ALTER TABLE `#__jp_task_lists` CHANGE `attribs` `attribs`  text DEFAULT NULL;
--
-- Table structure for table `#__jp_topics`
--
ALTER TABLE `#__jp_topics` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_topics` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_topics` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_topics` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_topics` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jp_topics` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_topics` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_topics` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_topics` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_topics` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_topics` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_topics` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_topics` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_topics` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_replies`
--
ALTER TABLE `#__jp_replies` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `topic_id` `topic_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jp_replies` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_replies` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_replies` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_replies` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_replies` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_replies` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_timesheet`
--
ALTER TABLE `#__jp_timesheet` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `task_id` `task_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `task_title` `task_title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_timesheet` CHANGE `description` `description` LONGTEXT DEFAULT NULL ;
ALTER TABLE `#__jp_timesheet` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_timesheet` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_timesheet` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_timesheet` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_timesheet` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `billable` `billable` decimal(5,2)  NOT NULL DEFAULT 0.00;
ALTER TABLE `#__jp_timesheet` CHANGE `rate` `rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `#__jp_timesheet` CHANGE `log_time` `log_time`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_timesheet` CHANGE `log_date` `log_date` datetime  NULL ;
--
-- Table structure for table `#__jp_repo_dirs`
--
ALTER TABLE `#__jp_repo_dirs` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_dirs` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_dirs` CHANGE `description` `description` LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_repo_dirs` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_repo_dirs` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_repo_dirs` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_repo_dirs` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_repo_dirs` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `parent_id` `parent_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `lft` `lft` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `rgt` `rgt` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `level` `level` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_dirs` CHANGE `path` `path` varchar(255) NOT NULL DEFAULT '';
--
-- Table structure for table `#__jp_repo_notes`
--
ALTER TABLE `#__jp_repo_notes` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_notes` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_notes` CHANGE `dir_id` `dir_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_notes` CHANGE `alias` `alias` varchar(56) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_notes` CHANGE `title` `title` varchar(56) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_notes` CHANGE `description` `description`  TEXT DEFAULT NULL;
ALTER TABLE `#__jp_repo_notes` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_repo_notes` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_notes` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_repo_notes` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_notes` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_notes` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_repo_notes` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_repo_notes` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_repo_files`
--
ALTER TABLE `#__jp_repo_files` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_files` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_files` CHANGE `dir_id` `dir_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_files` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_files` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_files` CHANGE `description` `description` LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_repo_files` CHANGE `file_name` `file_name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_files` CHANGE `file_extension` `file_extension` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_files` CHANGE `file_size` `file_size` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_files` CHANGE `created` `created`  datetime NULL;
ALTER TABLE `#__jp_repo_files` CHANGE `created_by` `created_by` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_files` CHANGE `modified` `modified` datetime NULL;
ALTER TABLE `#__jp_repo_files` CHANGE `checked_out` `checked_out` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__jp_repo_files` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_repo_files` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_repo_files` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_repo_file_revs`
--
ALTER TABLE `#__jp_repo_file_revs` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `parent_id` `parent_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `alias` `alias` varchar(56) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `title` `title` varchar(56) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `description` `description` LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_repo_file_revs` CHANGE `file_name` `file_name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `file_extension` `file_extension` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `file_size` `file_size` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_repo_file_revs` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_file_revs` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_repo_file_revs` CHANGE `ordering` `ordering`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_repo_note_revs`
--
ALTER TABLE `#__jp_repo_note_revs` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_note_revs` CHANGE `parent_id` `parent_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_note_revs` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_note_revs` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_note_revs` CHANGE `description` `description` LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_repo_note_revs` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_repo_note_revs` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_repo_note_revs` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_repo_note_revs` CHANGE `ordering` `ordering`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_emailqueue`
--
ALTER TABLE `#__jp_emailqueue` CHANGE `email` `email` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_emailqueue` CHANGE `subject` `subject`  text DEFAULT NULL;
ALTER TABLE `#__jp_emailqueue` CHANGE `message` `message`  text DEFAULT NULL;
ALTER TABLE `#__jp_emailqueue` CHANGE `created` `created`  datetime NULL ;
--
-- Table structure for table `#__jp_design_albums`
--
ALTER TABLE `#__jp_design_albums` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_albums` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_albums` CHANGE `description` `description`  LONGTEXT DEFAULT NULL;
ALTER TABLE `#__jp_design_albums` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_design_albums` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_design_albums` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_design_albums` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_design_albums` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_albums` CHANGE `ordering` `ordering`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_designs`
--
ALTER TABLE `#__jp_designs` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `album_id` `album_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_designs` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_designs` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jp_designs` CHANGE `file_name` `file_name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_designs` CHANGE `file_extension` `file_extension` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_designs` CHANGE `file_size` `file_size` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_designs` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_designs` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
ALTER TABLE `#__jp_designs` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_designs` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs` CHANGE `ordering` `ordering`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_designs_approved`
--
ALTER TABLE `#__jp_designs_approved` CHANGE `revision_id` `revision_id`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs_approved` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_designs_approved` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_designs_approved` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_design_revisions`
--
ALTER TABLE `#__jp_design_revisions` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `project_id` `project_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `parent_id` `parent_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_revisions` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_revisions` CHANGE `description` `description` text DEFAULT NULL;
ALTER TABLE `#__jp_design_revisions` CHANGE `file_name` `file_name` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_revisions` CHANGE `file_extension` `file_extension` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_revisions` CHANGE `file_size` `file_size` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_design_revisions` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_design_revisions` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_design_revisions` CHANGE `ordering` `ordering`  int(10)  NOT NULL DEFAULT '0';
--
-- Table structure for table `#__jp_reminders`
--
ALTER TABLE `#__jp_reminders` CHANGE `asset_id` `asset_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `task_id` `task_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `assigned_users` `assigned_users`  varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_reminders` CHANGE `description` `description`  text  DEFAULT NULL;
ALTER TABLE `#__jp_reminders` CHANGE `start_date` `start_date` datetime NULL;
ALTER TABLE `#__jp_reminders` CHANGE `amount` `amount` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `types` `types` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `repeats` `repeats` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `state` `state`  tinyint(3)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `access` `access`  int(10)  NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `attribs` `attribs`  text DEFAULT NULL;
ALTER TABLE `#__jp_reminders` CHANGE `ordering` `ordering`  int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `created` `created`  datetime NULL ;
ALTER TABLE `#__jp_reminders` CHANGE `created_by` `created_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `modified` `modified`  datetime NULL ;
ALTER TABLE `#__jp_reminders` CHANGE `modified_by` `modified_by`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `checked_out` `checked_out`  int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders` CHANGE `checked_out_time` `checked_out_time` datetime  NULL ;
--
-- Table structure for table `#__jp_reminders_dates`
--
ALTER TABLE `#__jp_reminders_dates` CHANGE `reminder_id` `reminder_id` int(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__jp_reminders_dates` CHANGE `date` `date` datetime NULL;
