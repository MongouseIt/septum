SET SESSION innodb_strict_mode=OFF;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
    ) DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__jp_users_team_user_map`
(
    `user_id` int(11)             NOT NULL DEFAULT 0,
    `team_id` int(11)             NOT NULL DEFAULT 0
    )
    DEFAULT CHARSET = utf8;

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
    DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__jp_users_role_action_map`
(
    `role_id` int(11)             NOT NULL DEFAULT 0,
    `action`  varchar(255)             NOT NULL DEFAULT '',
    `context`  varchar(255)             NOT NULL DEFAULT ''
    )
    DEFAULT CHARSET = utf8;


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
    )
    DEFAULT CHARSET = utf8;