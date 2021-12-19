CREATE DATABASE IF NOT EXISTS `phppoll_advanced` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `phppoll_advanced`;

CREATE TABLE IF NOT EXISTS `polls` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` text NOT NULL,
	`description` text NOT NULL,
	`expires` datetime DEFAULT NULL,
	`submit_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`start_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`approved` tinyint(1) NOT NULL DEFAULT '1',
	`num_choices` tinyint(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `polls` (`id`, `title`, `description`, `expires`, `submit_date`, `start_date`, `approved`, `num_choices`) VALUES
(1, 'What''s your favorite programming language?', '', NULL, '2021-04-29 19:34:38', '2021-05-19 01:00:00', 1, 1),
(2, 'What''s your favorite gaming console?', '', NULL, '2021-04-29 19:34:38', '2021-05-19 01:00:00', 1, 1),
(3, 'What''s your favorite car manufacturer?', '', NULL, '2021-05-26 16:09:12', '2021-05-26 16:07:00', 1, 1);

CREATE TABLE IF NOT EXISTS `poll_answers` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`poll_id` int(11) NOT NULL,
	`title` varchar(255) NOT NULL,
	`votes` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

INSERT INTO `poll_answers` (`id`, `poll_id`, `title`, `votes`) VALUES
(1, 1, 'PHP', 46),
(2, 1, 'Python', 39),
(3, 1, 'C#', 24),
(4, 1, 'Java', 17),
(5, 2, 'PlayStation 4', 50),
(6, 2, 'Xbox One', 44),
(7, 2, 'Nintendo Switch', 32),
(8, 3, 'BMW', 225),
(9, 3, 'Ford', 194),
(10, 3, 'Tesla', 248),
(11, 3, 'Honda', 129),
(12, 3, 'Toyota', 176);

ALTER TABLE `poll_answers` ADD UNIQUE KEY `title` (`title`);
