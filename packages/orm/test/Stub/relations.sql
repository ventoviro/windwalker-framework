CREATE TABLE `locations`
(
    `id`    int(11) UNSIGNED NOT NULL,
    `no`    varchar(255) NOT NULL DEFAULT '',
    `title` varchar(255) NOT NULL,
    `state` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `locations` (`id`, `no`, `title`, `state`)
VALUES (1, 'L00001', '雲彩裡', 1),
       (2, 'L00002', '神奇的宇宙', 0),
       (3, 'L00003', '一澄到底的清澈', 1),
       (4, 'L00004', '花草的香息', 0),
       (5, 'L00005', '獨身漫步的時候', 1);

CREATE TABLE `location_data`
(
    `id`          int(11) NOT NULL,
    `location_no` varchar(255) NOT NULL DEFAULT '',
    `data`        varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `location_data` (`id`, `location_no`, `data`)
VALUES (6, 'L00001', '「至難得者，謂操曰：運籌決算有神功，二虎還須遜一龍。初到任，即設五色棒十餘條於縣之四門。有犯禁者，。'),
       (7, 'L00002', '長，右有翼德ー，各引兵追襲張梁。那張角，一名張世平，一試矛兮一試刀。初到任，即設五色棒十餘里。後張。'),
       (8, 'L00003', '有資財，當乘此車蓋。」遂一面遣中郎已被逮，別人領兵，我更助汝一千官軍，前來潁川打探消息，約期剿捕。。'),
       (9, 'L00004', '壘。汝可引本部五百餘人，以天書三卷授之，曰：「此張角正殺敗董卓回寨。玄德謂關、張寶勢窮力乏，必獲惡。'),
       (10, 'L00005', '朱雋，其道盧植也。玄德曰：「天公將軍。」劉焉然其說，隨即引兵攻擊賊寨，火燄張天，急引兵追襲張梁、張。');

CREATE TABLE `roses`
(
    `id`          int(11) UNSIGNED NOT NULL COMMENT 'Primary Key',
    `no`          varchar(255) NOT NULL DEFAULT '',
    `location_no` varchar(255) NOT NULL DEFAULT '',
    `sakura_no`   varchar(255) NOT NULL DEFAULT '',
    `title`       varchar(255) NOT NULL COMMENT 'Record title',
    `state`       tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Record state'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `roses` (`id`, `no`, `location_no`, `sakura_no`, `title`, `state`)
VALUES (1, 'R00001', 'L00001', 'S00021', 'Rose 1', 0),
       (2, 'R00002', 'L00001', 'S00014', 'Rose 2', 0),
       (3, 'R00003', 'L00001', 'S00022', 'Rose 3', 0),
       (4, 'R00004', 'L00001', 'S00008', 'Rose 4', 1),
       (5, 'R00005', 'L00001', 'S00007', 'Rose 5', 0),
       (6, 'R00006', 'L00002', 'S00009', 'Rose 6', 1),
       (7, 'R00007', 'L00002', 'S00019', 'Rose 7', 0),
       (8, 'R00008', 'L00002', 'S00003', 'Rose 8', 1),
       (9, 'R00009', 'L00002', 'S00018', 'Rose 9', 0),
       (10, 'R00010', 'L00002', 'S00001', 'Rose 10', 1),
       (11, 'R00011', 'L00003', 'S00025', 'Rose 11', 0),
       (12, 'R00012', 'L00003', 'S00006', 'Rose 12', 1),
       (13, 'R00013', 'L00003', 'S00004', 'Rose 13', 0),
       (14, 'R00014', 'L00003', 'S00011', 'Rose 14', 0),
       (15, 'R00015', 'L00003', 'S00015', 'Rose 15', 1),
       (16, 'R00016', 'L00004', 'S00012', 'Rose 16', 1),
       (17, 'R00017', 'L00004', 'S00013', 'Rose 17', 0),
       (18, 'R00018', 'L00004', 'S00002', 'Rose 18', 0),
       (19, 'R00019', 'L00004', 'S00023', 'Rose 19', 1),
       (20, 'R00020', 'L00004', 'S00020', 'Rose 20', 1),
       (21, 'R00021', 'L00005', 'S00024', 'Rose 21', 0),
       (22, 'R00022', 'L00005', 'S00010', 'Rose 22', 1),
       (23, 'R00023', 'L00005', 'S00016', 'Rose 23', 0),
       (24, 'R00024', 'L00005', 'S00017', 'Rose 24', 1),
       (25, 'R00025', 'L00005', 'S00005', 'Rose 25', 1);

CREATE TABLE `sakuras`
(
    `id`          int(11) UNSIGNED NOT NULL COMMENT 'Primary Key',
    `no`          varchar(255) NOT NULL DEFAULT '',
    `location_no` varchar(255) NOT NULL DEFAULT '',
    `rose_no`     varchar(255) NOT NULL DEFAULT '',
    `title`       varchar(255) NOT NULL COMMENT 'Record title',
    `state`       tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Record state'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sakuras` (`id`, `no`, `location_no`, `rose_no`, `title`, `state`)
VALUES (1, 'S00001', 'L00001', '', 'Sakura 1', 1),
       (2, 'S00002', 'L00001', '', 'Sakura 2', 1),
       (3, 'S00003', 'L00001', '', 'Sakura 3', 0),
       (4, 'S00004', 'L00001', '', 'Sakura 4', 1),
       (5, 'S00005', 'L00001', '', 'Sakura 5', 0),
       (6, 'S00006', 'L00002', '', 'Sakura 6', 0),
       (7, 'S00007', 'L00002', '', 'Sakura 7', 0),
       (8, 'S00008', 'L00002', '', 'Sakura 8', 1),
       (9, 'S00009', 'L00002', '', 'Sakura 9', 1),
       (10, 'S00010', 'L00002', '', 'Sakura 10', 0),
       (11, 'S00011', 'L00003', '', 'Sakura 11', 1),
       (12, 'S00012', 'L00003', '', 'Sakura 21', 0),
       (13, 'S00013', 'L00003', '', 'Sakura 31', 0),
       (14, 'S00014', 'L00003', '', 'Sakura 41', 0),
       (15, 'S00015', 'L00003', '', 'Sakura 15', 0),
       (16, 'S00016', 'L00004', '', 'Sakura 16', 1),
       (17, 'S00017', 'L00004', '', 'Sakura 17', 0),
       (18, 'S00018', 'L00004', '', 'Sakura 18', 1),
       (19, 'S00019', 'L00004', '', 'Sakura 19', 1),
       (20, 'S00020', 'L00004', '', 'Sakura 20', 1),
       (21, 'S00021', 'L00005', '', 'Sakura 21', 1),
       (22, 'S00022', 'L00005', '', 'Sakura 22', 1),
       (23, 'S00023', 'L00005', '', 'Sakura 23', 0),
       (24, 'S00024', 'L00005', '', 'Sakura 24', 1),
       (25, 'S00025', 'L00005', '', 'Sakura 25', 0);

CREATE TABLE `sakura_rose_maps`
(
    `sakura_no` varchar(255) NOT NULL DEFAULT '',
    `rose_no`   varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sakura_rose_maps` (`sakura_no`, `rose_no`)
VALUES ('S00001', 'R00015'),
       ('S00001', 'R00017'),
       ('S00001', 'R00002'),
       ('S00002', 'R00004'),
       ('S00002', 'R00019'),
       ('S00002', 'R00003'),
       ('S00003', 'R00016'),
       ('S00003', 'R00025'),
       ('S00004', 'R00021'),
       ('S00004', 'R00024'),
       ('S00004', 'R00004'),
       ('S00005', 'R00007'),
       ('S00005', 'R00005'),
       ('S00005', 'R00006'),
       ('S00006', 'R00012'),
       ('S00006', 'R00016'),
       ('S00006', 'R00004'),
       ('S00007', 'R00011'),
       ('S00007', 'R00001'),
       ('S00007', 'R00014'),
       ('S00008', 'R00007'),
       ('S00008', 'R00016'),
       ('S00008', 'R00004'),
       ('S00009', 'R00006'),
       ('S00009', 'R00017'),
       ('S00009', 'R00003'),
       ('S00010', 'R00011'),
       ('S00010', 'R00022'),
       ('S00010', 'R00023'),
       ('S00011', 'R00023'),
       ('S00011', 'R00011'),
       ('S00011', 'R00014'),
       ('S00012', 'R00025'),
       ('S00012', 'R00015'),
       ('S00012', 'R00008'),
       ('S00013', 'R00003'),
       ('S00013', 'R00005'),
       ('S00013', 'R00008'),
       ('S00014', 'R00002'),
       ('S00014', 'R00001'),
       ('S00014', 'R00007'),
       ('S00015', 'R00006'),
       ('S00015', 'R00007'),
       ('S00015', 'R00011'),
       ('S00016', 'R00012'),
       ('S00016', 'R00019'),
       ('S00016', 'R00002'),
       ('S00017', 'R00015'),
       ('S00017', 'R00004'),
       ('S00017', 'R00003'),
       ('S00018', 'R00004'),
       ('S00018', 'R00011'),
       ('S00018', 'R00019'),
       ('S00019', 'R00008'),
       ('S00019', 'R00017'),
       ('S00019', 'R00011'),
       ('S00020', 'R00011'),
       ('S00020', 'R00002'),
       ('S00020', 'R00007'),
       ('S00021', 'R00008'),
       ('S00021', 'R00025'),
       ('S00021', 'R00017'),
       ('S00022', 'R00022'),
       ('S00022', 'R00025'),
       ('S00022', 'R00006'),
       ('S00023', 'R00005'),
       ('S00023', 'R00002'),
       ('S00023', 'R00010'),
       ('S00024', 'R00012'),
       ('S00024', 'R00004'),
       ('S00024', 'R00011'),
       ('S00025', 'R00018'),
       ('S00025', 'R00009'),
       ('S00025', 'R00017'),
       ('S00004', 'R00001'),
       ('S00020', 'R00001'),
       ('S00010', 'R00001'),
       ('S00006', 'R00002'),
       ('S00010', 'R00002'),
       ('S00014', 'R00002'),
       ('S00008', 'R00003'),
       ('S00013', 'R00003'),
       ('S00025', 'R00003'),
       ('S00001', 'R00004'),
       ('S00020', 'R00004'),
       ('S00016', 'R00004'),
       ('S00011', 'R00005'),
       ('S00005', 'R00005'),
       ('S00018', 'R00005'),
       ('S00017', 'R00006'),
       ('S00013', 'R00006'),
       ('S00018', 'R00006'),
       ('S00009', 'R00007'),
       ('S00010', 'R00007'),
       ('S00017', 'R00007'),
       ('S00014', 'R00008'),
       ('S00014', 'R00008'),
       ('S00018', 'R00008'),
       ('S00024', 'R00009'),
       ('S00001', 'R00009'),
       ('S00021', 'R00009'),
       ('S00009', 'R00010'),
       ('S00018', 'R00010'),
       ('S00005', 'R00010'),
       ('S00001', 'R00011'),
       ('S00022', 'R00011'),
       ('S00024', 'R00011'),
       ('S00011', 'R00012'),
       ('S00002', 'R00012'),
       ('S00008', 'R00012'),
       ('S00025', 'R00013'),
       ('S00009', 'R00013'),
       ('S00021', 'R00013'),
       ('S00024', 'R00014'),
       ('S00010', 'R00014'),
       ('S00016', 'R00014'),
       ('S00015', 'R00015'),
       ('S00021', 'R00015'),
       ('S00021', 'R00015'),
       ('S00008', 'R00016'),
       ('S00012', 'R00016'),
       ('S00008', 'R00016'),
       ('S00025', 'R00017'),
       ('S00020', 'R00017'),
       ('S00017', 'R00017'),
       ('S00017', 'R00018'),
       ('S00009', 'R00018'),
       ('S00006', 'R00018'),
       ('S00009', 'R00019'),
       ('S00008', 'R00019'),
       ('S00006', 'R00019'),
       ('S00005', 'R00020'),
       ('S00017', 'R00020'),
       ('S00024', 'R00020'),
       ('S00010', 'R00021'),
       ('S00017', 'R00021'),
       ('S00020', 'R00021'),
       ('S00009', 'R00022'),
       ('S00002', 'R00022'),
       ('S00021', 'R00022'),
       ('S00016', 'R00023'),
       ('S00002', 'R00023'),
       ('S00005', 'R00023'),
       ('S00012', 'R00024'),
       ('S00025', 'R00024'),
       ('S00014', 'R00024'),
       ('S00002', 'R00025'),
       ('S00015', 'R00025'),
       ('S00009', 'R00025');

ALTER TABLE `locations`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `location_data`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location_data_location_no` (`location_no`);

ALTER TABLE `roses`
    ADD PRIMARY KEY (`id`),
  ADD KEY `cat_index` (`state`),
  ADD KEY `idx_roses_no` (`no`),
  ADD KEY `idx_roses_location_no` (`location_no`);

ALTER TABLE `sakuras`
    ADD PRIMARY KEY (`id`),
  ADD KEY `cat_index` (`state`),
  ADD KEY `idx_sakuras_no` (`no`),
  ADD KEY `idx_sakuras_location_no` (`location_no`);

ALTER TABLE `locations`
    MODIFY `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `location_data`
    MODIFY `id` INT (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `roses`
    MODIFY `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=26;

ALTER TABLE `sakuras`
    MODIFY `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=26;
COMMIT;
