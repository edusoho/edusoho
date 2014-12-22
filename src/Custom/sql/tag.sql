ALTER TABLE  `tag` ADD  `largeAvatar` varchar(255) AFTER  `name`;

ALTER TABLE  `tag` ADD  `mediumAvatar` varchar(255) AFTER  `name`;

ALTER TABLE  `tag` ADD  `smallAvatar` varchar(255) AFTER  `name`;

ALTER TABLE  `tag` ADD  `description` text AFTER  `name`;

ALTER TABLE  `tag` ADD  `weight` int(11)  NOT NULL DEFAULT '1' AFTER  `name`;
		