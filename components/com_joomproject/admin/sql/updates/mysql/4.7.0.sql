ALTER TABLE `#__jp_projects` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_projects` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_comments` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_comments` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_designs` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_designs` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_design_albums` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_albums` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_labels` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';


ALTER TABLE `#__jp_milestones` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_milestones` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_users_teams` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_users_roles` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_topics` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_topics` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_timesheet` CHANGE `task_title` `task_title` varchar (255) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_task_lists` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_task_lists` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_tasks` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_tasks` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_tags` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_tags` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_repo_note_revs` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_note_revs` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_repo_files` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_files` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_repo_dirs` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_repo_dirs` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';

ALTER TABLE `#__jp_design_revisions` CHANGE `title` `title` varchar (255) NOT NULL DEFAULT '';
ALTER TABLE `#__jp_design_revisions` CHANGE `alias` `alias` varchar (400) NOT NULL DEFAULT '';









