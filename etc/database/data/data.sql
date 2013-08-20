INSERT INTO `setting` (`id`, `name`, `value`) VALUES
(3, 'file', 0x613a333a7b733a31363a227075626c69635f6469726563746f7279223b733a393a227765622f66696c6573223b733a31353a227075626c69635f7765625f70617468223b733a363a222f66696c6573223b733a31373a22707269766174655f6469726563746f7279223b733a31333a22707269766174655f66696c6573223b7d);


INSERT INTO `category_group` (`id`, `code`, `name`, `depth`) VALUES
(1, 'default', '默认分类', 2);

INSERT INTO `category` (`id`, `code`, `name`, `path`, `weight`, `groupId`, `parentId`) VALUES
(1, 'a', '测试分类A', '', 0, 1, 0),
(2, 'b', '测试分类B', '', 0, 1, 0),
(4, 'c', '测试分类C', '', 140, 1, 0),
(5, 'a1', '测试分类A-1', '', 190, 1, 1),
(6, 'a2', '测试分类A-2', '', 170, 1, 1),
(7, 'a3', '测试分类A-3', '', 160, 1, 1),
(8, 'b1', '测试分类B-1', '', 150, 1, 2),
(9, 'b2', '测试分类B-2', '', 200, 1, 2),
(10, 'c1', '测试分类C-1', '', 130, 1, 3);

INSERT INTO `tag` (`id`, `name`, `createdTime`) VALUES
(1, '标签A', 0),
(2, '标签B', 0),
(3, '标签C', 0),
(4, '标签D', 0);

INSERT INTO `file_group` (`id`, `name`, `code`, `public`) VALUES
(1, '默认文件组', 'default', 1),
(2, '缩略图', 'thumb', 1),
(3, '课程', 'course', 1),
(4, '课程私有资料', 'course_private', 1),
(5, '用户', 'user', 1);
