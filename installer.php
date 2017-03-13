<?php

//self installing script
include_once 'application/config/config.php';

$this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, $options);

//tasks
$sql = "CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
//users  - only1   login:admin pass:md5(123);
$sql .= "CREATE TABLE IF NOT EXISTS `users` (
  `id` tinyint(8) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `admintoken` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `last_logged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `users` (`id`, `login`, `password`, `admintoken`, `last_logged`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', '8b98ab962da6a9eee520d37d8e53c040', '2017-03-04 20:47:26');
";

try {

	$query = $this->db->prepare($sql);
	$query->execute();
	echo "<br>Table created";

}
catch (PDOException $ex) {
	echo "<br>An error occured " . $ex->getMessage();
}
