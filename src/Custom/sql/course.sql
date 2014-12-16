ALTER TABLE  `course` ADD  `discount` float(10,1) AFTER  `freeEndTime`;

ALTER TABLE  `course` ADD  `complexity` enum('lowLevel', 'middleLevel', 'highLevel')  AFTER  `freeEndTime`;

ALTER TABLE  `course` ADD  `originalPrice` float(10,2) AFTER  `price`;

ALTER TABLE  `course` ADD  `columns` text  AFTER  `freeEndTime`;
