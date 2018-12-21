CREATE TABLE `queues`(
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tube` VARCHAR(30) NOT NULL DEFAULT 'default',
  `status` ENUM('ready','reserved'),
  `job_data` TEXT NOT NULL,
  `attempts` INT UNSIGNED DEFAULT 0,
  `sort` INT UNSIGNED DEFAULT 0,
  `reserved_at` INT(10) UNSIGNED DEFAULT NULL,
  `available_at` INT(10) UNSIGNED NOT NULL,
  `created_at` INT(10) UNSIGNED NOT NULL，
  PRIMARY KEY (`id`),
  KEY `queues_index`(`tube`)
) ENGINE=InnoDB charset=utf8;