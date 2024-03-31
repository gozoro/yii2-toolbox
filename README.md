# yii2-toolbox
Collection classes and helpers for yii2 project.

THIS PACKAGE IS DEPRECATED.


Usage models/File
-----------------

Create table

```sql

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `mime` varchar(128) NOT NULL,
  `size` int(11) NOT NULL,
  `uploadDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

```