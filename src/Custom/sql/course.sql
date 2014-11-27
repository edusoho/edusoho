ALTER TABLE  `course` ADD  `discount` float(1,1) AFTER  `freeEndTime`;

-- ALTER TABLE  `course` ADD  `isFree` 	enum('active', 'none')  AFTER  `freeEndTime`;

ALTER TABLE  `course` ADD  `complexity` enum('lowLevel', 'middleLevel', 'highLevel')  AFTER  `freeEndTime`;

ALTER TABLE  `course` ADD  `originalPrice` float(10,2) AFTER  `price`;

ALTER TABLE  `course` ADD  `columns` text  AFTER  `freeEndTime`;
