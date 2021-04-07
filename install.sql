CREATE TABLE IF NOT EXISTS `jieqi_cate` (
   `sortid` int(11) NOT NULL AUTO_INCREMENT,
   `cate_name` varchar(20) NOT NULL COMMENT '分类名',
   `create_time` int(11) DEFAULT '0',
   `update_time` int(11) DEFAULT '0',
   PRIMARY KEY (`sortid`) USING BTREE,
   unique KEY `cate_name` (`cate_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jieqi_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic` varchar(255)  DEFAULT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `articleid` int(11) NOT NULL COMMENT '所属漫画ID',
  `title` varchar(50) NOT NULL COMMENT '轮播图标题',
    `banner_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
    KEY `banner_order` (`banner_order`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jieqi_tail` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `articleid` int(11) unsigned NOT NULL DEFAULT '0',
    `tailname` varchar(200) NOT NULL COMMENT '长尾词',
    `tailcode` varchar(255) NOT NULL COMMENT '唯一标识',
    `tailtype` tinyint(4) NOT NULL COMMENT '类型，1.根据书名拓展，2.自己导入，3.导入词拓展',
    `parent` int(11) DEFAULT 0  COMMENT '父词ID',
    `create_time` int(11) DEFAULT '0',
    `update_time` int(11) DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `tailname` (`tailname`),
    KEY `tailtype` (`tailtype`),
    KEY `parent` (`parent`),
    unique key `tailcode` (`tailcode`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jieqi_friendship_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '友链名',
  `url` varchar(255) NOT NULL COMMENT '友链地址',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jieqi_user_favor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `articleid` int(10) unsigned NOT NULL COMMENT '用户收藏的小说ID',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  key articleid (`articleid`) USING BTREE,
  key uid (`uid`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jieqi_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `articleid` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `articleid` (`articleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jieqi_book_logs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `book_name` varchar(100)  DEFAULT '',
  `src_url` varchar(255)  NOT NULL DEFAULT '',
  `log_time` int(10) DEFAULT 0,
  `src` varchar(32) NOT NULL DEFAULT '',
  `last_chapter` varchar(100) DEFAULT NULL,
  `last_chapter_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
    KEY `src_url`(`src_url`) USING BTREE,
    KEY `book_id`(`book_id`) USING BTREE,
    KEY `book_name`(`book_name`) USING BTREE,
    KEY `log_time`(`log_time`) USING BTREE,
    KEY `src`(`src`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `jieqi_chapter_logs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `chapter_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `chapter_name` varchar(100) DEFAULT '',
  `src_url` varchar(255) NOT NULL DEFAULT '',
  `chapter_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `log_time` int(10) UNSIGNED DEFAULT 0,
    `src` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
    KEY `src_url`(`src_url`) USING BTREE,
    KEY `chapter_id`(`chapter_id`) USING BTREE,
    KEY `chapter_name`(`chapter_name`) USING BTREE,
    KEY `chapter_order`(`chapter_order`) USING BTREE,
    KEY `log_time`(`log_time`) USING BTREE,
    KEY `src`(`src`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 ROW_FORMAT = Dynamic;
