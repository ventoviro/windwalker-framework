CREATE TABLE `articles` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'Primary Key',
  `category_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Category ID',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Title',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Main Image',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Intro Text',
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: unpublished, 1:published',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT 'Created Date',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Author',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Params'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `articles` (`id`, `category_id`, `title`, `image`, `content`, `state`, `created`, `created_by`, `params`) VALUES
(1, 2, '(zh-TW) Corrupti illum.', 'https://picsum.photos/800/600?image=618', 'Ullam aliquid et dignissimos explicabo modi quam. Autem esse non et ut non est nihil id. Maxime placeat quod in et est dolorem. Eum quasi quam delectus alias minus. In voluptatem quidem eum non voluptatem officia reiciendis commodi. Assumenda illo aut sunt ex commodi in reiciendis.', 1, '2009-05-14 17:45:24', 4, ''),
(2, 2, '(ja-JP) Ut occaecati consectetur.', 'https://picsum.photos/800/600?image=692', 'Ut in architecto cupiditate qui eligendi sequi cupiditate autem. Distinctio corrupti suscipit et sint atque quaerat. Molestiae tenetur aut nobis eius dolor id quasi incidunt. Laborum qui sint dolores molestiae deleniti quaerat. Alias deleniti beatae architecto aut voluptatem reprehenderit nisi fuga. Suscipit atque eius dolorem facere. Totam hic mollitia omnis et laboriosam.', 1, '1995-11-26 01:17:07', 2, ''),
(3, 2, '(ja-JP) Architecto modi aut.', 'https://picsum.photos/800/600?image=43', 'Qui sunt eaque minus aut. Sint at qui est ut. Est rem commodi et nostrum eos. Fuga eaque ut et veniam. Minus tempore quasi ut ut.', 1, '1977-06-29 16:15:18', 3, ''),
(4, 2, '(*) Beatae minus.', 'https://picsum.photos/800/600?image=461', 'Animi voluptatem qui ab eligendi officia eius. Qui repudiandae tenetur corrupti unde atque at. Qui ab vitae incidunt sed quidem omnis. Cupiditate est quae reprehenderit enim consequatur sit.', 0, '1971-02-28 03:39:32', 1, ''),
(5, 2, '(zh-TW) Aperiam at.', 'https://picsum.photos/800/600?image=943', 'Sunt nostrum praesentium qui beatae quia ut asperiores non. Velit aut voluptatem dolorem voluptates quia. Ducimus est et qui aut similique. Eos quidem et officiis iusto a aut ea. Ducimus rerum dolores facilis. Ut iste molestiae quis placeat. Possimus dolor perferendis ut est nulla hic omnis.', 1, '2003-11-03 04:17:58', 2, ''),
(6, 3, '(*) Enim officia unde.', 'https://picsum.photos/800/600?image=393', 'Et occaecati ipsa qui dolorem neque. Ea sed adipisci voluptatem. Error tenetur unde dolorem aliquid. Eveniet maiores aut numquam voluptatem. Ea nobis autem libero dolores reprehenderit minima voluptatem. Est minima architecto earum totam cum.', 1, '1993-12-22 09:57:17', 4, ''),
(7, 3, '(zh-TW) Labore qui asperiores.', 'https://picsum.photos/800/600?image=527', 'Dicta aut ex sed accusantium consequatur tempore. Ut sequi dicta cum eum fuga. Cumque quo repudiandae aut autem iusto quia aut culpa. Accusamus vitae vel quo ullam. Impedit debitis aut est quaerat sit. Eos ipsum deserunt labore aliquid quibusdam eos quos. Voluptatem nostrum earum et expedita quos aspernatur temporibus.', 0, '1976-11-08 17:40:13', 2, ''),
(8, 3, '(ja-JP) Quo reprehenderit voluptas.', 'https://picsum.photos/800/600?image=392', 'Recusandae culpa minima rerum harum numquam neque. Quod deserunt at quia fugit libero officiis. Mollitia voluptas dolor libero dignissimos. Ut sed cupiditate ut esse est consequatur. Omnis soluta dolores repudiandae dolor culpa. Nihil odit error quis esse qui recusandae.', 1, '2017-12-24 02:31:03', 2, ''),
(9, 3, '(ja-JP) Dolorem cum earum.', 'https://picsum.photos/800/600?image=90', 'Ut aliquam est eos nam. Quo consequatur possimus in delectus dicta. Quis minima ut et est reprehenderit. Et minus quidem accusantium omnis voluptatem tenetur. Accusantium culpa aperiam vel.', 1, '2008-08-02 16:37:57', 2, ''),
(10, 3, '(en-GB) Ut excepturi et.', 'https://picsum.photos/800/600?image=1043', 'Eaque blanditiis iste eligendi consequatur veritatis non consequuntur saepe. Dolorum molestiae nihil iste. Debitis nobis ducimus itaque quidem quis necessitatibus et. Velit vel velit dolores blanditiis amet consequatur. Ut eos quo distinctio est.', 0, '2008-04-26 13:58:14', 2, ''),
(11, 4, '(en-GB) Sit iusto.', 'https://picsum.photos/800/600?image=203', 'Autem accusantium distinctio sit exercitationem. Pariatur alias aut ut ut et nostrum. Quam ducimus eaque totam et. Ad et esse voluptates dolorum harum eum quidem quae. Nesciunt ut aut eligendi. Quo omnis repellendus necessitatibus saepe. Pariatur sit et similique.', 0, '2015-07-15 15:40:08', 3, ''),
(12, 4, '(*) Iste temporibus aut.', 'https://picsum.photos/800/600?image=300', 'Iste ut ut fuga hic aut numquam. Dolorum harum dicta nihil mollitia exercitationem placeat odio. In perspiciatis omnis quibusdam sint veritatis facere qui. Suscipit quam pariatur reiciendis quia tempore nostrum.', 1, '1994-05-18 15:13:35', 1, ''),
(13, 4, '(ja-JP) Atque nobis autem.', 'https://picsum.photos/800/600?image=493', 'Explicabo fugit ipsa quo quasi reiciendis assumenda mollitia. Quaerat inventore ipsum voluptatum alias est laboriosam iste. Voluptatem rerum voluptatem quam quas. Omnis unde qui consequatur voluptatibus sint magnam atque. Velit nihil quo corporis facere qui et ratione.', 1, '1985-12-03 17:44:15', 4, ''),
(14, 4, '(*) Autem corrupti sit.', 'https://picsum.photos/800/600?image=326', 'Et impedit officiis aut perferendis. Perspiciatis molestias natus reiciendis voluptates in. Voluptas ratione voluptas enim doloremque eveniet. Vitae nulla non aut.', 1, '1995-09-30 13:23:36', 4, ''),
(15, 4, '(zh-TW) Vel nisi est.', 'https://picsum.photos/800/600?image=53', 'Possimus asperiores voluptatem aut architecto at possimus. Magni non similique nostrum pariatur aliquam nobis. Dolor voluptatem praesentium tempora dolores suscipit. Consequuntur officiis sunt molestiae veritatis commodi aut aliquam.', 1, '1987-02-21 23:45:37', 2, '');

CREATE TABLE `comments` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'Primary Key',
  `target_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Target ID',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'User ID',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Type',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Content',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT 'Created Date',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Author'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`id`, `target_id`, `user_id`, `type`, `content`, `created`, `created_by`) VALUES
(1, 1, 1, 'article', 'Doloribus magnam quae et sunt. Cupiditate fugit laudantium possimus cupiditate vel. Perferendis qui natus eos corrupti. Aut voluptatibus quidem enim. Ut dolorum et et commodi incidunt minus magnam. Earum deserunt et dolore.', '2013-04-16 02:38:50', 2),
(2, 1, 1, 'article', 'At dolor dignissimos consequatur aperiam facilis numquam sit numquam. Beatae nam totam voluptatem distinctio quo corporis. Doloremque fugit ut vero rerum. Qui facilis at nesciunt ut.', '2009-03-28 16:28:10', 2),
(3, 1, 1, 'article', 'Praesentium est corporis dolores ipsum voluptas consectetur doloribus. Et nesciunt fuga quos dolorum. Molestiae sint vero voluptatem dolores repudiandae. Itaque quo aut ullam pariatur ea dolorum itaque. Facilis optio nisi sit nihil ea laborum. Aut qui consequatur animi velit unde repellendus. Sit delectus quis distinctio unde odio nobis sed aut.', '2000-08-27 03:39:10', 4),
(4, 2, 4, 'article', 'Atque ab delectus dolorum rerum suscipit rerum sed voluptatem. Reiciendis repudiandae ab voluptatem dolor libero dolor enim. Ipsa dolor voluptatum maxime et libero exercitationem sint. Rerum laboriosam alias explicabo sapiente. Iusto quis eius excepturi dolorem et. Quas velit deleniti atque hic expedita veritatis qui molestiae.', '1980-11-02 14:56:44', 3),
(5, 2, 3, 'article', 'Et quisquam sint aut qui soluta dolorum. Accusantium et at magni vero quod expedita totam beatae. Modi esse rem velit perspiciatis consequatur ut rerum qui. Placeat in aperiam aut atque assumenda. Sequi nihil deserunt repellendus et a amet pariatur. Ad odit maiores natus provident molestiae qui. Voluptas error unde eum.', '1971-02-21 08:48:22', 2),
(6, 2, 1, 'article', 'Facilis commodi qui quae magnam sit beatae ea. Autem quod nulla culpa excepturi et. Occaecati error pariatur inventore. Eos perferendis et optio debitis architecto quod in. Est et est quis facere ut facilis. Repudiandae eligendi laborum et sapiente nulla dolorem blanditiis.', '2015-05-20 13:35:24', 1),
(7, 3, 2, 'article', 'Dolore quia hic fugit reprehenderit autem. Quae voluptatum harum fugiat impedit voluptatem. Fugiat laudantium sit soluta. Odit laboriosam similique praesentium eaque repudiandae. Repellendus velit placeat est. Aperiam rerum libero est error.', '2004-10-04 03:52:48', 4),
(8, 3, 2, 'article', 'Explicabo repudiandae minima qui facere omnis illo illo corporis. Minus quidem eum reprehenderit aut quam voluptatum nostrum ad. Accusamus eligendi id veritatis. Sapiente voluptate impedit et qui ut est. Velit sed iure maxime similique expedita. Pariatur rerum non provident. Enim voluptate cum reiciendis excepturi omnis cupiditate culpa.', '2018-01-23 23:31:26', 4),
(9, 3, 4, 'article', 'Recusandae voluptatem provident voluptatem necessitatibus et illum dolores. Corrupti aspernatur debitis quia voluptatem nisi atque. Sit vitae et vel doloremque aut dolores rem. Cum quis amet ut magnam harum harum adipisci.', '2006-06-15 09:47:42', 2),
(10, 4, 2, 'article', 'Est qui eos omnis distinctio voluptates eaque ratione. Quasi dignissimos assumenda aspernatur delectus quis. Magnam itaque nihil quasi laudantium in ut. Earum quam itaque autem est temporibus. Enim perferendis officiis voluptate quas est.', '1998-01-01 06:28:25', 1),
(11, 4, 3, 'article', 'Debitis et blanditiis ipsam quo quisquam. Explicabo totam odio voluptate. Voluptate illo maiores iure autem. Reprehenderit a optio expedita pariatur velit necessitatibus distinctio. Explicabo omnis consequuntur dolorum voluptatem ipsa. Exercitationem laborum fugit quia nihil atque quaerat nobis eos.', '1997-01-26 18:55:02', 3),
(12, 4, 2, 'article', 'Sint repellendus esse non porro quod sunt amet et. Aut ipsum nihil explicabo sed molestiae excepturi. In excepturi ducimus molestiae fuga. Repellat corrupti nam temporibus ratione enim cumque voluptatem. Corporis repudiandae ut et facilis a. Doloribus magnam nemo magnam praesentium saepe ipsum quo quibusdam. Iure alias incidunt atque blanditiis beatae architecto adipisci itaque.', '1993-07-10 13:51:49', 2),
(13, 5, 1, 'article', 'Eligendi repudiandae quo sed et. Ducimus libero hic ut quos quia ducimus eum tempora. Doloribus aliquam libero doloribus modi at. Doloremque sint et a est eligendi consectetur dolores. Possimus qui ea eum. Qui praesentium ipsum temporibus. Corporis consequatur ipsa sed nesciunt.', '1993-07-19 23:54:58', 1),
(14, 5, 2, 'article', 'Voluptatibus repellendus autem rerum fugiat quas. Tenetur dolores est itaque rerum ab et inventore modi. Quia error aspernatur maxime nesciunt at. A mollitia laborum tempora a deleniti enim provident.', '2002-03-16 11:57:58', 4),
(15, 5, 2, 'article', 'Itaque debitis et natus modi. Qui dolores quis assumenda qui. Consequatur sint voluptatibus nobis. Voluptatem eos corrupti omnis dolores. Ut odit saepe aspernatur similique non sit. Quam qui beatae suscipit libero voluptatibus ducimus illum quisquam.', '1973-07-19 00:00:23', 4),
(16, 6, 1, 'article', 'Qui illum nisi quasi cumque labore rerum quae assumenda. Voluptatem fuga atque et omnis laboriosam mollitia. Qui aut aut ut voluptatibus aliquid ducimus modi accusamus. Eum dolor dolorem inventore ullam. Ratione voluptatibus odio distinctio eos ea ut. Voluptatem perferendis placeat qui sed deserunt. Deleniti asperiores repellendus ab eos veritatis ad voluptates rerum.', '1998-04-22 09:41:36', 3),
(17, 6, 1, 'article', 'Est rerum est quam quasi. Nulla dicta aut blanditiis et qui quidem. Hic voluptas voluptas unde impedit maiores. Voluptates ex eos sint. Aut voluptatem voluptatibus repellendus aliquam aut.', '1993-02-24 21:59:13', 1),
(18, 6, 2, 'article', 'Corrupti eum non nostrum officiis aliquam voluptatem adipisci. Id reprehenderit exercitationem quidem consequuntur ipsam. Corporis natus aut ipsa commodi velit laudantium vel. Sed sequi qui placeat aliquid dolorem repudiandae. Possimus consequuntur beatae quidem eos. Qui ut quae et aperiam est quia accusantium dolores.', '2008-11-02 23:07:42', 4),
(19, 7, 1, 'article', 'Quas aut nisi id ipsam. Quia omnis dolor quos aut ullam fugiat. Atque maxime ut magnam dolor itaque. Fugiat doloremque aperiam atque iusto rerum. Incidunt est rerum illo labore magni enim. Dolorum excepturi aut et ipsa blanditiis fuga voluptatibus.', '2010-08-28 22:59:14', 4),
(20, 7, 1, 'article', 'Consequatur est laudantium ratione. Culpa molestias et molestiae delectus. Ad odit quia nesciunt possimus velit. Doloribus sed omnis iusto occaecati saepe est dolor corporis. Veniam pariatur id reiciendis aut dolor est. Ex porro possimus eius quis quod.', '2013-03-05 13:30:03', 4),
(21, 7, 2, 'article', 'Nobis consequuntur voluptatibus non optio. Neque fugiat facere autem quaerat est ratione. Voluptate neque dolore deleniti doloribus voluptate facilis. Qui laboriosam unde soluta iste animi totam voluptatem. Et tenetur vitae perspiciatis ipsa quia alias. Recusandae voluptas in ipsum qui nobis. Dolorem in corporis voluptates aut et.', '1991-08-10 06:36:25', 2),
(22, 8, 4, 'article', 'Beatae ex provident est. Earum quod vitae temporibus est. Nam explicabo qui rerum necessitatibus. Voluptate corrupti earum quia nam. Maxime eligendi aperiam qui deserunt excepturi et in. Et facere magnam molestiae aspernatur nostrum.', '2020-02-28 09:49:03', 3),
(23, 8, 4, 'article', 'Sed sunt itaque alias. Illum eveniet nobis temporibus deleniti. Quod sit quae et inventore. Repellat nulla facere reprehenderit vel qui ad. Ea consequatur eos cupiditate soluta corrupti dolore.', '1994-12-27 14:33:03', 3),
(24, 8, 4, 'article', 'Itaque dolores repellendus voluptas sed impedit. Ut qui accusamus sed excepturi velit voluptatem. Harum magnam quae eos ut cum. Minima voluptate quia quia aliquid fugiat eligendi. Blanditiis aut et aut sunt vel laboriosam.', '1988-01-07 04:01:57', 1),
(25, 9, 4, 'article', 'Magni numquam nihil sint deleniti deserunt et. Similique eaque suscipit voluptatem sit animi et minus. Hic qui provident dolore modi dolorem quos unde. Facere id voluptate aperiam. Dolorem rerum rem quia sit pariatur. Rem et at ut sunt molestiae quaerat consequuntur et. Sunt nostrum quia voluptatem impedit asperiores.', '2009-09-30 22:16:27', 3),
(26, 9, 3, 'article', 'Quae rerum placeat asperiores aperiam optio. Beatae perspiciatis numquam corporis aut nihil ratione sapiente. Vel pariatur esse delectus aspernatur et nemo. Et et non consequatur perferendis et asperiores ullam. Explicabo similique enim cum dolore sapiente. Commodi doloribus qui aut similique vel iusto aut.', '2009-03-25 02:35:15', 1),
(27, 9, 3, 'article', 'Quaerat dolore et sed et voluptate. Ipsam labore quam natus aut sit eum consectetur aperiam. Quidem voluptatum veniam et in non ad delectus. Rem nihil et architecto unde itaque voluptatum qui. Facere rem itaque laboriosam ipsum maxime. Eum voluptatem ut ut molestiae eos et. Odio quaerat nulla rerum eos.', '1991-02-10 16:12:11', 4),
(28, 10, 3, 'article', 'Repudiandae perspiciatis ut incidunt voluptatum. Impedit quas et animi unde consequatur molestiae sint. Dolor facilis dolor ea eligendi officia qui. Itaque dolorum fuga accusantium quis. Rerum nisi consequatur corrupti eaque consequatur iusto dolores. Id dolores doloremque omnis ut blanditiis impedit quam.', '1972-06-30 20:46:51', 4),
(29, 10, 2, 'article', 'Commodi vel aut aut qui vel rerum quia. Illum animi natus et eum. Rerum et dignissimos distinctio aut mollitia dolorem eos in. Ipsa temporibus animi consectetur dicta. Magni et mollitia tempore cum. Illum ratione sapiente fuga vel eius molestiae.', '2015-02-21 19:18:31', 1),
(30, 10, 2, 'article', 'Accusamus saepe distinctio vel expedita impedit ab. Voluptatem suscipit et ex corrupti. Inventore ut porro repellat quo magni nesciunt. Aliquid enim modi labore qui error est. Cumque possimus aut corporis amet aperiam nam est. Veritatis quas fugiat aliquid qui consequatur ut. Est vel est deserunt aliquid quia dolores.', '2001-09-22 22:35:14', 1),
(31, 11, 4, 'article', 'Odit odio quia ea incidunt nihil totam sunt. Incidunt sit reprehenderit ut non corporis ad consectetur. Dolor repellendus odit asperiores. Beatae illum quis corrupti blanditiis magni possimus.', '1991-07-15 15:31:55', 3),
(32, 11, 3, 'article', 'Perspiciatis dignissimos occaecati ex occaecati fuga nobis. Nihil quae temporibus voluptatem. Odit ut et et inventore. Id quia minima voluptatibus sed. Quia rem rem labore et aut recusandae. Earum dolorum maiores ea cum officia eum quae et.', '2005-04-19 17:45:15', 1),
(33, 11, 2, 'article', 'Voluptate non beatae modi quo molestiae similique enim. Facere a velit repudiandae eum error voluptate. Dolores reprehenderit ducimus nesciunt nemo voluptas minima dolore. Quis quo aut quaerat.', '2015-11-25 17:25:41', 1),
(34, 12, 1, 'article', 'Expedita minima est non ea quis occaecati expedita. Soluta sunt nisi ab similique qui minus. Corrupti fuga quibusdam ea. Saepe deserunt maxime quas aperiam labore.', '2020-09-03 23:00:50', 3),
(35, 12, 2, 'article', 'Optio rerum voluptates quae placeat expedita aperiam eaque. Voluptate eaque est quidem omnis voluptas repudiandae dolor esse. Aut et et quas repudiandae. Blanditiis autem quis debitis sit itaque. Est porro sunt nulla sit perferendis dignissimos voluptatem.', '1971-10-21 15:14:38', 4),
(36, 12, 2, 'article', 'Eligendi et culpa molestiae magni maxime fugiat. Reiciendis labore qui reiciendis est nihil. Adipisci ut sunt pariatur odio fuga sit. Cupiditate aut nam harum sunt hic error aperiam autem. Sed iste recusandae qui delectus et at aut veniam. Earum ratione consectetur iste consequuntur quaerat.', '1977-06-05 07:16:51', 4),
(37, 13, 2, 'article', 'Soluta tempora molestias perspiciatis et similique voluptates qui. Sequi nesciunt omnis explicabo qui consequatur aliquam corporis nisi. Voluptatem minus nemo tempore in est est. Asperiores hic sunt eaque officia ullam. Nesciunt optio reiciendis tempore omnis repellat. Voluptas voluptate dicta totam repudiandae praesentium modi quia. Accusantium dolorem animi voluptatem nam autem adipisci quaerat.', '1981-10-30 09:49:55', 1),
(38, 13, 3, 'article', 'Nam ut maxime quia deleniti ut accusantium. Vel nostrum reiciendis quis. Non eos eius et similique rerum quidem quidem. Sit eum recusandae quam unde nobis quisquam esse. Consectetur ex excepturi debitis sed veniam est quam qui. Ut perspiciatis alias temporibus vel reiciendis vel.', '1971-01-13 02:02:57', 1),
(39, 13, 1, 'article', 'Ut sed excepturi incidunt ab unde voluptatem molestias ut. Velit consequatur voluptatem magnam aperiam eos. Veniam molestiae neque enim. Mollitia est perferendis aperiam nostrum ex.', '2018-08-09 08:20:17', 2),
(40, 14, 1, 'article', 'Voluptatem rerum sit animi voluptatem alias dolores rerum veritatis. Voluptates natus eius omnis ut in rerum et. Laudantium deleniti dolore reiciendis minus. Ratione est et omnis corporis et perferendis.', '2004-10-20 14:37:37', 3),
(41, 14, 1, 'article', 'Quia est sequi sit nisi iste nisi nam. Consequatur hic qui fugit inventore laborum. Dignissimos aperiam ducimus in eius autem. Quidem eos et necessitatibus hic qui. Voluptatem earum commodi enim aut. Sapiente eius magnam deserunt quia labore officia. Corporis veniam illo est quis voluptatem.', '1983-05-17 17:17:04', 4),
(42, 14, 2, 'article', 'Qui quia voluptatem consequatur provident. Velit ut cumque cumque quisquam quo totam nihil. Reiciendis ut corrupti et non porro. Omnis quis accusamus enim mollitia corporis ex est. Quia ea aut consequatur adipisci. Non amet aliquam harum minus est qui amet.', '1974-10-01 17:57:04', 3),
(43, 15, 4, 'article', 'Sit error sapiente rem qui accusamus excepturi voluptatem. Neque qui cum nihil expedita in repudiandae. Id beatae deleniti excepturi. Ipsum et deserunt sed provident inventore doloremque voluptas eius. Non magni quasi sed quia. Et qui nobis vitae architecto corrupti atque voluptates.', '1974-08-27 07:12:46', 3),
(44, 15, 2, 'article', 'Assumenda rerum esse nostrum amet. Itaque optio rerum vel ullam. Delectus dicta saepe sint consequuntur. Nobis sed velit incidunt blanditiis. Magnam blanditiis aut qui alias dolorem et maiores.', '1987-02-01 12:08:43', 2),
(45, 15, 3, 'article', 'Quo et reprehenderit alias debitis. Ullam eum repellendus repellat qui non pariatur iste. Odit molestiae magnam non delectus dolorum qui sed. Libero eum veritatis provident. Minus voluptas expedita est quas. Esse architecto nisi qui quisquam est. Omnis et ut ad consequuntur totam.', '2009-08-19 07:50:11', 2);

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'Primary Key',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Full Name',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Login name',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Email',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Password',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Avatar',
  `registered` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT 'Register Time',
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Params'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `avatar`, `registered`, `params`) VALUES
(1, 'Simular', 'admin', 'test@simular.no', '$2y$10$Grpvuil3S6CLdzNUXjLq8ejctq3JZAnjWHxy2qISH4xbSWBXI39uy', 'https://avatars0.githubusercontent.com/u/13175487', '2020-02-25 21:20:29', ''),
(2, 'Nels Robel', 'rohan.stefan', 'vmohr@example.org', '$2y$10$wL/yzrNr5lSJJGym1XL0HOu6hi.QDOuU8uEqNhm46S9hKz7BKvB0y', 'https://i.pravatar.cc/600?u=16023ad83146845.88910210', '2020-10-26 11:37:50', ''),
(3, 'Diana Swaniawski', 'jannie.wuckert', 'clovis.berge@example.org', '$2y$10$wL/yzrNr5lSJJGym1XL0HOu6hi.QDOuU8uEqNhm46S9hKz7BKvB0y', 'https://i.pravatar.cc/600?u=26023ad83150355.41567515', '2021-01-13 18:19:53', ''),
(4, 'Warren Kessler', 'alyson40', 'nadia.shanahan@example.org', '$2y$10$wL/yzrNr5lSJJGym1XL0HOu6hi.QDOuU8uEqNhm46S9hKz7BKvB0y', 'https://i.pravatar.cc/600?u=36023ad83154c90.20043323', '2020-09-18 09:05:24', '');

ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_users_username` (`username`(150)),
  ADD KEY `idx_users_email` (`email`(150));

ALTER TABLE `articles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=16;

ALTER TABLE `comments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=46;

ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=5;
COMMIT;
