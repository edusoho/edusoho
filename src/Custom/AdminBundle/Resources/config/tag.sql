ALTER TABLE  `tag` ADD  `largeAvatar` varchar(255) AFTER  `name`;

ALTER TABLE  `tag` ADD  `mediumAvatar` varchar(255) AFTER  `name`;

ALTER TABLE  `tag` ADD  `smallAvatar` varchar(255) AFTER  `name`;

ALTER TABLE  `tag` ADD  `description` text AFTER  `name`;