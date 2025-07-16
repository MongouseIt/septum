DELETE FROM #__user_activity_item_types WHERE id = 0;
ALTER TABLE #__user_activity_item_types CHANGE id id int(10) unsigned NOT NULL AUTO_INCREMENT