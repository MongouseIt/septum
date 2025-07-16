-- Simple version (will throw errors if indexes already exist) added to help counting only published comments
ALTER TABLE `#__jp_comments`
    ADD INDEX `idx_state_parent` (`state`, `parent_id`),
    ADD INDEX `idx_state_level` (`state`, `level`),
    ADD INDEX `idx_path_state` (`path`(191), `state`),
    ADD INDEX `idx_lft_state` (`lft`, `state`, `rgt`);

-- Update comments with empty context to com_jpprojects.project
UPDATE `#__jp_comments`
SET `context` = 'com_jpprojects.project'
WHERE `context` = ''
   OR `context` IS NULL