DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations`
(
    `id`    int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `state` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `locations` (`id`, `title`, `state`)
VALUES (1, '雲彩裡', 1),
       (2, '神奇的宇宙', 0),
       (3, '一澄到底的清澈', 1),
       (4, '花草的香息', 0),
       (5, '獨身漫步的時候', 1);

DROP TABLE IF EXISTS `location_data`;
CREATE TABLE `location_data`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `location_id` int(11) NOT NULL,
    `data`        varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

INSERT INTO `location_data` (`id`, `location_id`, `data`)
VALUES (6, 1, '「至難得者，謂操曰：運籌決算有神功，二虎還須遜一龍。初到任，即設五色棒十餘條於縣之四門。有犯禁者，。'),
       (7, 2, '長，右有翼德ー，各引兵追襲張梁。那張角，一名張世平，一試矛兮一試刀。初到任，即設五色棒十餘里。後張。'),
       (8, 3, '有資財，當乘此車蓋。」遂一面遣中郎已被逮，別人領兵，我更助汝一千官軍，前來潁川打探消息，約期剿捕。。'),
       (9, 4, '壘。汝可引本部五百餘人，以天書三卷授之，曰：「此張角正殺敗董卓回寨。玄德謂關、張寶勢窮力乏，必獲惡。'),
       (10, 5, '朱雋，其道盧植也。玄德曰：「天公將軍。」劉焉然其說，隨即引兵攻擊賊寨，火燄張天，急引兵追襲張梁、張。');

DROP TABLE IF EXISTS `roses`;
CREATE TABLE `roses`
(
    `id`        int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
    `location`  int(11) NOT NULL,
    `sakura_id` int(11) NOT NULL,
    `title`     varchar(255) NOT NULL COMMENT 'Record title',
    `state`     tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Record state',
    PRIMARY KEY (`id`),
    KEY         `cat_index` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

INSERT INTO `roses` (`id`, `location`, `sakura_id`, `title`, `state`)
VALUES (1, 1, 21, 'Rose 1', 0),
       (2, 1, 14, 'Rose 2', 0),
       (3, 1, 22, 'Rose 3', 0),
       (4, 1, 8, 'Rose 4', 1),
       (5, 1, 7, 'Rose 5', 0),
       (6, 2, 9, 'Rose 6', 1),
       (7, 2, 19, 'Rose 7', 0),
       (8, 2, 3, 'Rose 8', 1),
       (9, 2, 18, 'Rose 9', 0),
       (10, 2, 1, 'Rose 10', 1),
       (11, 3, 25, 'Rose 11', 0),
       (12, 3, 6, 'Rose 12', 1),
       (13, 3, 4, 'Rose 13', 0),
       (14, 3, 11, 'Rose 14', 0),
       (15, 3, 15, 'Rose 15', 1),
       (16, 4, 12, 'Rose 16', 1),
       (17, 4, 13, 'Rose 17', 0),
       (18, 4, 2, 'Rose 18', 0),
       (19, 4, 23, 'Rose 19', 1),
       (20, 4, 20, 'Rose 20', 1),
       (21, 5, 24, 'Rose 21', 0),
       (22, 5, 10, 'Rose 22', 1),
       (23, 5, 16, 'Rose 23', 0),
       (24, 5, 17, 'Rose 24', 1),
       (25, 5, 5, 'Rose 25', 1);

DROP TABLE IF EXISTS `sakuras`;
CREATE TABLE `sakuras`
(
    `id`       int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
    `location` int(11) NOT NULL,
    `rose_id`  int(11) NOT NULL,
    `title`    varchar(255) NOT NULL COMMENT 'Record title',
    `state`    tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Record state',
    PRIMARY KEY (`id`),
    KEY        `cat_index` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

INSERT INTO `sakuras` (`id`, `location`, `rose_id`, `title`, `state`)
VALUES (1, 1, 13, 'Sakura 1', 1),
       (2, 1, 8, 'Sakura 2', 1),
       (3, 1, 5, 'Sakura 3', 0),
       (4, 1, 4, 'Sakura 4', 1),
       (5, 1, 11, 'Sakura 5', 0),
       (6, 2, 23, 'Sakura 6', 0),
       (7, 2, 15, 'Sakura 7', 0),
       (8, 2, 2, 'Sakura 8', 1),
       (9, 2, 9, 'Sakura 9', 1),
       (10, 2, 1, 'Sakura 10', 0),
       (11, 3, 6, 'Sakura 11', 1),
       (12, 3, 16, 'Sakura 21', 0),
       (13, 3, 22, 'Sakura 31', 0),
       (14, 3, 12, 'Sakura 41', 0),
       (15, 3, 7, 'Sakura 15', 0),
       (16, 4, 24, 'Sakura 16', 1),
       (17, 4, 14, 'Sakura 17', 0),
       (18, 4, 19, 'Sakura 18', 1),
       (19, 4, 17, 'Sakura 19', 1),
       (20, 4, 18, 'Sakura 20', 1),
       (21, 5, 21, 'Sakura 21', 1),
       (22, 5, 25, 'Sakura 22', 1),
       (23, 5, 10, 'Sakura 23', 0),
       (24, 5, 3, 'Sakura 24', 1),
       (25, 5, 20, 'Sakura 25', 0);

DROP TABLE IF EXISTS `sakura_rose_maps`;
CREATE TABLE `sakura_rose_maps`
(
    `sakura_id` int(11) NOT NULL,
    `rose_id`   int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sakura_rose_maps` (`sakura_id`, `rose_id`)
VALUES (1, 15),
       (1, 17),
       (1, 2),
       (2, 4),
       (2, 19),
       (2, 3),
       (3, 16),
       (3, 25),
       (4, 21),
       (4, 24),
       (4, 4),
       (5, 7),
       (5, 5),
       (5, 6),
       (6, 12),
       (6, 16),
       (6, 4),
       (7, 11),
       (7, 1),
       (7, 14),
       (8, 7),
       (8, 16),
       (8, 4),
       (9, 6),
       (9, 17),
       (9, 3),
       (10, 11),
       (10, 22),
       (10, 23),
       (11, 23),
       (11, 11),
       (11, 14),
       (12, 25),
       (12, 15),
       (12, 8),
       (13, 3),
       (13, 5),
       (13, 8),
       (14, 2),
       (14, 1),
       (14, 7),
       (15, 6),
       (15, 7),
       (15, 11),
       (16, 12),
       (16, 19),
       (16, 2),
       (17, 15),
       (17, 4),
       (17, 3),
       (18, 4),
       (18, 11),
       (18, 19),
       (19, 8),
       (19, 17),
       (19, 11),
       (20, 11),
       (20, 2),
       (20, 7),
       (21, 8),
       (21, 25),
       (21, 17),
       (22, 22),
       (22, 25),
       (22, 6),
       (23, 5),
       (23, 2),
       (23, 10),
       (24, 12),
       (24, 4),
       (24, 11),
       (25, 18),
       (25, 9),
       (25, 17),
       (4, 1),
       (20, 1),
       (10, 1),
       (6, 2),
       (10, 2),
       (14, 2),
       (8, 3),
       (13, 3),
       (25, 3),
       (1, 4),
       (20, 4),
       (16, 4),
       (11, 5),
       (5, 5),
       (18, 5),
       (17, 6),
       (13, 6),
       (18, 6),
       (9, 7),
       (10, 7),
       (17, 7),
       (14, 8),
       (14, 8),
       (18, 8),
       (24, 9),
       (1, 9),
       (21, 9),
       (9, 10),
       (18, 10),
       (5, 10),
       (1, 11),
       (22, 11),
       (24, 11),
       (11, 12),
       (2, 12),
       (8, 12),
       (25, 13),
       (9, 13),
       (21, 13),
       (24, 14),
       (10, 14),
       (16, 14),
       (15, 15),
       (21, 15),
       (21, 15),
       (8, 16),
       (12, 16),
       (8, 16),
       (25, 17),
       (20, 17),
       (17, 17),
       (17, 18),
       (9, 18),
       (6, 18),
       (9, 19),
       (8, 19),
       (6, 19),
       (5, 20),
       (17, 20),
       (24, 20),
       (10, 21),
       (17, 21),
       (20, 21),
       (9, 22),
       (2, 22),
       (21, 22),
       (16, 23),
       (2, 23),
       (5, 23),
       (12, 24),
       (25, 24),
       (14, 24),
       (2, 25),
       (15, 25),
       (9, 25);
