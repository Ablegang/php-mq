CREATE TABLE `queues` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tube` varchar(30) NOT NULL DEFAULT 'default',
  `status` enum('ready','reserved') DEFAULT 'ready',
  `job_data` text NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `sort` int(10) NOT NULL DEFAULT '100',
  `reserved_at` int(11) DEFAULT NULL,
  `available_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `queues_index` (`tube`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8