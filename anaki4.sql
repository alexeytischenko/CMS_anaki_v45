-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 20 2013 г., 15:29
-- Версия сервера: 5.0.45
-- Версия PHP: 5.2.4
--
-- БД: `anaki4`
--

-- --------------------------------------------------------

--
-- Структура таблицы `anaki_admins`
--

CREATE TABLE `anaki_admins` (
  `an_uid` bigint(20) unsigned NOT NULL auto_increment,
  `an_login` varchar(15) NOT NULL default '',
  `an_passwd` varchar(40) NOT NULL default '',
  `an_name` varchar(50) NOT NULL default '',
  `an_regdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `an_active` tinyint(1) NOT NULL default '0',
  `an_super` tinyint(4) NOT NULL default '0',
  `an_userdata` longtext NOT NULL,
  `an_useract` longtext NOT NULL,
  PRIMARY KEY  (`an_uid`),
  UNIQUE KEY `an_login` (`an_login`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `anaki_admins`
--

INSERT INTO `anaki_admins` VALUES (1, 'editor', '1a28a8f19cad09f9b748cbf390b21bb7', 'Editor', '2011-11-01 00:00:00', 1, 1, 'a:3:{s:8:"template";s:7:"default";s:14:"template_style";s:17:"admin_default.css";s:5:"debug";s:1:"1";}', '');


-- --------------------------------------------------------

--
-- Структура таблицы `anaki_fotos`
--

CREATE TABLE `anaki_fotos` (
  `an_oid` bigint(9) unsigned NOT NULL auto_increment,
  `an_name` varchar(255) NOT NULL default '',
  `an_filename` varchar(255) NOT NULL,
  `an_sort` bigint(9) NOT NULL default '0',
  `an_parent` bigint(9) NOT NULL,
  PRIMARY KEY  (`an_oid`),
  KEY `an_parent` (`an_parent`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `anaki_fotos`
--

-- --------------------------------------------------------

--
-- Структура таблицы `anaki_fotos_order`
--

CREATE TABLE `anaki_fotos_order` (
  `an_parent` bigint(9) NOT NULL,
  `an_sortorder` tinyint(1) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `anaki_fotos_order`
--


-- --------------------------------------------------------

--
-- Структура таблицы `anaki_objectsdata`
--

CREATE TABLE `anaki_objectsdata` (
  `an_oid` bigint(20) unsigned NOT NULL default '0',
  `an_field` varchar(20) NOT NULL,
  `an_lang` tinyint(1) NOT NULL default '0',
  `an_text` longtext,
  `an_varchar` varchar(255) default NULL,
  `an_integer` bigint(20) default NULL,
  `an_float` float default NULL,
  `an_bool` tinyint(1) default NULL,
  `an_date` date NOT NULL,
  KEY `an_oid` (`an_oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `anaki_objectsdata`
--

INSERT INTO `anaki_objectsdata` VALUES (4, '1_modul', 1, NULL, 'sitemap', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (4, '1_modul', 2, NULL, 'sitemap', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (5, '1_modul', 1, NULL, 'search', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (5, '1_modul', 2, NULL, 'search', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (1, '1_template', 1, NULL, 'home', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (1, '1_template', 2, NULL, 'home', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (3, '1_modul', 1, NULL, 'request', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (3, '1_modul', 2, NULL, 'request', NULL, NULL, NULL, '0000-00-00');
INSERT INTO `anaki_objectsdata` VALUES (8, '3_user', 1, NULL, ',1,', NULL, NULL, NULL, '0000-00-00');

-- --------------------------------------------------------

--
-- Структура таблицы `anaki_objectsprop`
--

CREATE TABLE `anaki_objectsprop` (
  `an_oid` bigint(20) unsigned NOT NULL default '0',
  `an_lang` tinyint(1) NOT NULL default '0',
  `an_name` varchar(255) default NULL,
  `an_title` varchar(255) NOT NULL,
  `an_header` varchar(255) NOT NULL,
  `an_tags` varchar(255) NOT NULL,
  `an_keywords` varchar(255) NOT NULL,
  `an_description` varchar(255) NOT NULL,
  `an_sorttype` enum('name','sortnumber','date') NOT NULL default 'sortnumber',
  `an_sortorder` tinyint(1) NOT NULL default '0',
  `an_sortnumber` float NOT NULL default '0',
  `an_frontendaccess` tinyint(1) NOT NULL default '0',
  `an_menu` tinyint(1) NOT NULL default '0',
  `an_restr` tinyint(1) NOT NULL default '0',
  KEY `an_oid` (`an_oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `anaki_objectsprop`
--

INSERT INTO `anaki_objectsprop` VALUES (4, 1, 'Карта сайта', '', '', '', '', '', 'sortnumber', 0, 4, 1, 0, 0);
INSERT INTO `anaki_objectsprop` VALUES (4, 2, 'Sitemap', '', '', '', '', '', 'sortnumber', 0, 4, 1, 0, 0);
INSERT INTO `anaki_objectsprop` VALUES (5, 1, 'Результаты поиска', '', '', '', '', '', 'sortnumber', 0, 5, 1, 0, 0);
INSERT INTO `anaki_objectsprop` VALUES (5, 2, 'Search results', '', '', '', '', '', 'sortnumber', 0, 5, 1, 0, 0);
INSERT INTO `anaki_objectsprop` VALUES (1, 1, 'Главная страница', '', '', '', '', '', 'sortnumber', 0, 1, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (1, 2, 'Home Page', '', '', '', '', '', 'sortnumber', 0, 1, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (2, 1, 'О компании', '', '', ',1,', '', '', 'sortnumber', 0, 2, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (3, 1, 'Контакты', '', '', '', '', '', 'sortnumber', 0, 3, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (3, 2, 'Contacts', '', '', '', '', '', 'sortnumber', 0, 3, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (6, 1, 'История компании', '', '', '', '', '', 'sortnumber', 0, 6, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (6, 2, 'History', '', '', '', '', '', 'sortnumber', 0, 6, 1, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (8, 1, '', '', '', '', '', '', 'sortnumber', 0, 8, 0, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (8, 2, '', '', '', '', '', '', 'sortnumber', 0, 8, 0, 1, 0);
INSERT INTO `anaki_objectsprop` VALUES (2, 2, 'About', '', '', ',2,3,', '', '', 'sortnumber', 0, 2, 1, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `anaki_objectstree`
--

CREATE TABLE `anaki_objectstree` (
  `an_oid` bigint(20) unsigned NOT NULL auto_increment,
  `an_url` varchar(255) default NULL,
  `an_date` datetime default NULL,
  `an_parent` bigint(20) NOT NULL default '0',
  `an_type` smallint(6) NOT NULL default '0',
  `an_isfolder` tinyint(1) NOT NULL default '0',
  `an_inside` tinyint(1) NOT NULL default '0',
  `an_modifdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `an_modifuser` bigint(20) NOT NULL default '0',
  `an_owner` bigint(20) NOT NULL default '0',
  `an_left` bigint(20) default NULL,
  `an_right` bigint(20) default NULL,
  `an_level` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`an_oid`),
  KEY `an_left` (`an_left`,`an_right`),
  KEY `an_url` (`an_url`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `anaki_objectstree`
--

INSERT INTO `anaki_objectstree` VALUES (1, 'home', '2012-02-08 00:00:00', 0, 1, 1, 0, '2013-02-20 12:46:00', 1, 1, 1, 12, 1);
INSERT INTO `anaki_objectstree` VALUES (2, 'about', '2012-04-26 23:58:00', 1, 1, 1, 0, '2013-02-20 12:47:00', 1, 1, 2, 5, 2);
INSERT INTO `anaki_objectstree` VALUES (3, 'contacts', '2012-04-30 21:11:00', 1, 1, 1, 0, '2012-05-04 15:41:00', 1, 1, 6, 7, 2);
INSERT INTO `anaki_objectstree` VALUES (4, 'sitemap', '2012-05-01 16:33:00', 1, 1, 1, 0, '2012-05-01 16:34:00', 1, 1, 8, 9, 2);
INSERT INTO `anaki_objectstree` VALUES (5, 'search', '2012-05-01 16:40:00', 1, 1, 1, 0, '2012-05-01 16:42:00', 1, 1, 10, 11, 2);
INSERT INTO `anaki_objectstree` VALUES (6, 'history', '2012-05-04 15:47:00', 2, 1, 1, 0, '2012-05-04 15:47:00', 1, 1, 3, 4, 3);
INSERT INTO `anaki_objectstree` VALUES (8, '', '2013-02-20 01:44:00', 0, 3, 0, 0, '2013-02-20 01:44:00', 1, 1, 13, 14, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `anaki_properties`
--

CREATE TABLE `anaki_properties` (
  `an_lang` tinyint(1) NOT NULL,
  `an_oid` bigint(9) NOT NULL,
  `an_value` bigint(20) NOT NULL,
  `an_flag` varchar(10) NOT NULL,
  `an_date` datetime NOT NULL,
  KEY `an_flag` (`an_flag`,`an_lang`),
  KEY `an_oid` (`an_oid`,`an_lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `anaki_properties`
--


-- --------------------------------------------------------

--
-- Структура таблицы `anaki_tags`
--

CREATE TABLE `anaki_tags` (
  `an_tid` bigint(20) NOT NULL auto_increment,
  `an_lang` tinyint(1) default NULL,
  `an_name` varchar(255) NOT NULL,
  `an_count` mediumint(9) NOT NULL,
  PRIMARY KEY  (`an_tid`),
  UNIQUE KEY `an_lang` (`an_lang`,`an_name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `anaki_tags`
--

INSERT INTO `anaki_tags` VALUES (1, 1, 'new', 3);


-- --------------------------------------------------------

--
-- Структура таблицы `anaki_texttosend`
--

CREATE TABLE `anaki_texttosend` (
  `an_text` longtext NOT NULL,
  `an_pubid` bigint(20) NOT NULL default '0',
  `an_email` varchar(100) NOT NULL default '',
  `an_name` varchar(200) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `anaki_texttosend`
--


-- --------------------------------------------------------

--
-- Структура таблицы `anaki_users`
--

CREATE TABLE `anaki_users` (
  `an_uid` bigint(20) unsigned NOT NULL auto_increment,
  `an_networkid` varchar(100) NOT NULL,
  `an_network` enum('','VK','FB') NOT NULL,
  `an_login` varchar(100) NOT NULL default '',
  `an_passwd` varchar(40) NOT NULL default '',
  `an_name` varchar(50) NOT NULL default '',
  `an_lastname` varchar(50) NOT NULL,
  `an_regdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `an_active` tinyint(1) NOT NULL default '0',
  `an_email` varchar(100) NOT NULL default '',
  `an_userdata` longtext NOT NULL,
  PRIMARY KEY  (`an_uid`),
  UNIQUE KEY `an_login` (`an_login`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `anaki_users`
--


