-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `xwx_admin`;
CREATE TABLE `xwx_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(32) NOT NULL,
  `password` char(32) NOT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `last_login_time` int(11) DEFAULT '0',
  `last_login_ip` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for xwx_user
-- ----------------------------
DROP TABLE IF EXISTS `xwx_user`;
CREATE TABLE `xwx_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(32) NOT NULL,
  `nick_name` varchar(100) DEFAULT '',
  `email` char(32) DEFAULT '' COMMENT '会员邮箱',
  `password` char(32) NOT NULL,
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `delete_time` int(11) DEFAULT '0',
  `last_login_time` int(11) DEFAULT '0',
  `reg_ip` varchar(32) DEFAULT '' COMMENT '用户注册ip',
  PRIMARY KEY (`id`) USING BTREE,
  unique key `username` (`username`) USING BTREE,
  key `password` (`password`) USING BTREE,
  key `email` (`email`)USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for banner
-- ----------------------------
DROP TABLE IF EXISTS `xwx_banner`;
CREATE TABLE `xwx_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_name` varchar(255) DEFAULT '' COMMENT '轮播图完整路径名',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `book_id` int(11) NOT NULL COMMENT '所属漫画ID',
  `title` varchar(50) NOT NULL COMMENT '轮播图标题',
    `banner_order` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
    KEY `banner_order` (`banner_order`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for author
-- ----------------------------
DROP TABLE IF EXISTS `xwx_author`;
CREATE TABLE `xwx_author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(100) NOT NULL,
   `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  key `author_name` (`author_name`) USING BTREE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for book
-- ----------------------------
DROP TABLE IF EXISTS `xwx_book`;
CREATE TABLE `xwx_book` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(100) NOT NULL COMMENT '小说标识', 
  `book_name` varchar(50) NOT NULL COMMENT '小说名',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `last_chapter_id` int(10) unsigned DEFAULT '0',
  `last_chapter` varchar(255) DEFAULT '无章节',
  `last_time` int(11) DEFAULT '0' COMMENT '最后更新时间',
  `delete_time` int(11) DEFAULT '0',
  `summary` text COMMENT '简介',
  `end` tinyint(4) DEFAULT '1' COMMENT '2为连载，1为完结',
  `author_id` int(11) NOT NULL COMMENT '作者ID',
  `author_name` varchar(24) DEFAULT '佚名',
  `role_name` varchar(24) DEFAULT '未知',
  `cover_url` varchar(255) DEFAULT '' COMMENT '封面图路径',
  `cate_id` int(11) NOT NULL COMMENT '所属题材',
  `words` float(6,2) unsigned DEFAULT 0 COMMENT '字数',
  `is_top` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否推荐',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `end` (`end`) USING BTREE,
  KEY `author_id` (`author_id`) USING BTREE,
  KEY `cate_id` (`cate_id`) USING BTREE,
   KEY `is_top` (`is_top`) USING BTREE,
  FULLTEXT KEY `book_name` (`book_name`) with parser ngram,
  unique KEY `unique_id`(`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for chapter
-- ----------------------------
DROP TABLE IF EXISTS `xwx_chapter`;
CREATE TABLE `xwx_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_name` varchar(255) NOT NULL COMMENT '章节名称',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `book_id` int(10) unsigned NOT NULL COMMENT '章节所属书ID',
  `chapter_order` decimal(10,2) NOT NULL COMMENT '章节序',
  `content_url` varchar(255) DEFAULT '' COMMENT '小说文件路径',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `chapter_name` (`chapter_name`) USING BTREE,
  KEY `book_id` (`book_id`) USING BTREE,
  KEY `chapter_order` (`chapter_order`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;


-- ----------------------------
-- Table structure for category
-- ----------------------------
DROP TABLE IF EXISTS `xwx_cate`;
CREATE TABLE `xwx_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(20) NOT NULL COMMENT '分类名',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  unique KEY `cate_name` (`cate_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;


-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE IF EXISTS `xwx_tags`;
CREATE TABLE `xwx_tags`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(50) NOT NULL COMMENT '标签名',
  `create_time` int(11) DEFAULT 0,
  `update_time` int(11) DEFAULT 0,
  `pinyin` varchar(255) NOT NULL COMMENT '拼音',
  `jianpin` varchar(50) NOT NULL COMMENT '简拼',
  PRIMARY KEY (`id`) USING BTREE,
  FULLTEXT INDEX fidx (tag_name) WITH PARSER ngram,
  KEY `pinyin`(`pinyin`) USING BTREE,
  KEY `jianpin`(`jianpin`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for xwx_friendship_link
-- ----------------------------
DROP TABLE IF EXISTS `xwx_friendship_link`;
CREATE TABLE `xwx_friendship_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '友链名',
  `url` varchar(255) NOT NULL COMMENT '友链地址',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for xwx_user_book
-- ----------------------------
DROP TABLE IF EXISTS `xwx_user_favor`;
CREATE TABLE `xwx_user_favor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` int(10) unsigned NOT NULL COMMENT '用户收藏的小说ID',
  `create_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  key book_id (`book_id`) USING BTREE,
  key user_id (`user_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for xwx_clicks
-- ----------------------------
DROP TABLE IF EXISTS `xwx_clicks`;
CREATE TABLE `xwx_clicks`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` int(10) UNSIGNED NOT NULL,
  `clicks` int(10) UNSIGNED NOT NULL,
  `cdate` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `book_id`(`book_id`) USING BTREE,
  INDEX `cdate`(`cdate`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for booklogs
-- ----------------------------
DROP TABLE IF EXISTS `xwx_book_logs`;
CREATE TABLE `xwx_book_logs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `book_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `src_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `log_time` int(10) DEFAULT 0,
  `src` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `last_chapter` varchar(100) DEFAULT NULL,
  `last_chapter_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `src_url`(`src_url`) USING BTREE,
  INDEX `book_id`(`book_id`) USING BTREE,
  INDEX `book_name`(`book_name`) USING BTREE,
  INDEX `log_time`(`log_time`) USING BTREE,
  INDEX `src`(`src`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for chapterlogs
-- ----------------------------
DROP TABLE IF EXISTS `xwx_chapter_logs`;
CREATE TABLE `xwx_chapter_logs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `chapter_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `chapter_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `src_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `chapter_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `log_time` int(10) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `src_url`(`src_url`) USING BTREE,
  INDEX `chapter_id`(`chapter_id`) USING BTREE,
  INDEX `chapter_name`(`chapter_name`) USING BTREE,
  INDEX `chapter_order`(`chapter_order`) USING BTREE,
  INDEX `log_time`(`log_time`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 ROW_FORMAT = Dynamic;

INSERT INTO xwx_admin(username, `password`) VALUES('admin','123456')