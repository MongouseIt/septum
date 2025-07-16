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