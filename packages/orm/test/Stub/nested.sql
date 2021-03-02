CREATE TABLE IF NOT EXISTS `#__nestedsets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `lft` int(11) NOT NULL DEFAULT '0',
    `rgt` int(11) NOT NULL DEFAULT '0',
    `level` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `title` varchar(255) NOT NULL DEFAULT '',
    `alias` varchar(255) NOT NULL DEFAULT '',
    `access` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
    `path` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_left_right` (`lft`,`rgt`)
    ) DEFAULT CHARSET=utf8;
