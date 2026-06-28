/*
Navicat MySQL Data Transfer

Source Server         : bt
Source Server Version : 50738
Source Host           : localhost:3306
Source Database       : tempweb

Target Server Type    : MYSQL
Target Server Version : 50738
File Encoding         : 65001

Date: 2026-06-23 23:49:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for announcements
-- ----------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text,
  `type` varchar(20) DEFAULT 'home',
  `status` tinyint(1) DEFAULT '1',
  `closable` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of announcements
-- ----------------------------
INSERT INTO `announcements` VALUES ('2', '你好', '越来越好', 'global', '1', '1', '2026-06-23 19:23:19');
INSERT INTO `announcements` VALUES ('3', '大家好', '才是真的好', 'home', '1', '1', '2026-06-23 22:40:57');

-- ----------------------------
-- Table structure for api_keys
-- ----------------------------
DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `api_key` varchar(100) NOT NULL,
  `secret` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `request_count` int(11) DEFAULT '0',
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of api_keys
-- ----------------------------

-- ----------------------------
-- Table structure for captcha_tokens
-- ----------------------------
DROP TABLE IF EXISTS `captcha_tokens`;
CREATE TABLE `captcha_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of captcha_tokens
-- ----------------------------
INSERT INTO `captcha_tokens` VALUES ('1', '481d6513a15a352d91f3763ba7c82bd2', '7Q9U', '2026-06-23 20:54:35');
INSERT INTO `captcha_tokens` VALUES ('3', '5a1627afe0c393f6001387d40807affa', '9PZB', '2026-06-23 20:54:45');
INSERT INTO `captcha_tokens` VALUES ('4', 'f45f26129e7d9e41c9b617d307d459fa', 'HULW', '2026-06-23 20:54:50');
INSERT INTO `captcha_tokens` VALUES ('6', 'd344cfb8e0362f51b7fe5cf7fd443412', 'NG7R', '2026-06-23 21:00:11');
INSERT INTO `captcha_tokens` VALUES ('7', '041dc0347b98e758ef70c4536fb240a4', '467T', '2026-06-23 21:00:26');
INSERT INTO `captcha_tokens` VALUES ('8', 'a5fceaccbf5b4998c4974e618a3ce8f6', '3Q2S', '2026-06-23 21:00:30');
INSERT INTO `captcha_tokens` VALUES ('9', '8e9501d31bd481dac7043f7169c67e70', 'AHJ6', '2026-06-23 21:00:46');
INSERT INTO `captcha_tokens` VALUES ('12', '24a6efab13293ae3bdbec6720571fa9c', 'MJHA', '2026-06-23 21:04:42');
INSERT INTO `captcha_tokens` VALUES ('14', 'bca38170da9d57f8add85ab1a39ee34c', '7QX6', '2026-06-23 21:04:54');
INSERT INTO `captcha_tokens` VALUES ('15', 'b8a159890b47e1db51382846cd197523', 'KVZ5', '2026-06-23 21:05:07');
INSERT INTO `captcha_tokens` VALUES ('16', '83d479e78f30740793a96f5274404b81', 'HLDK', '2026-06-23 21:05:19');
INSERT INTO `captcha_tokens` VALUES ('17', 'aa1222fa1f38cb19e1c46ba431126424', 'M8N6', '2026-06-23 21:06:00');
INSERT INTO `captcha_tokens` VALUES ('18', '2af58f7ad77d6d3e3e4d6f3e27318e32', 'U93K', '2026-06-23 21:06:27');
INSERT INTO `captcha_tokens` VALUES ('19', '091d56e974f09051c0bda138328c205c', '6DEU', '2026-06-23 21:06:36');
INSERT INTO `captcha_tokens` VALUES ('20', 'e376bbc7a5b4d6dab42a186ef2ae6afa', 'ZGMQ', '2026-06-23 21:06:50');
INSERT INTO `captcha_tokens` VALUES ('21', '51fabef30cf2e18f77c140d6c905db33', 'GBFC', '2026-06-23 21:06:58');
INSERT INTO `captcha_tokens` VALUES ('22', 'f80fa13e68b379960a85a4c0052f536c', 'G49Z', '2026-06-23 21:09:41');
INSERT INTO `captcha_tokens` VALUES ('23', '759f2b0ac8ffb1affbaf13d1ec73981a', 'LTB5', '2026-06-23 21:09:47');
INSERT INTO `captcha_tokens` VALUES ('30', 'ee30fdf7e8e68c3691c36ecbf5365b34', 'KDXL', '2026-06-23 21:53:58');
INSERT INTO `captcha_tokens` VALUES ('33', '804186db5ec7652cdf5755fa7e30d7b3', 'CXAF', '2026-06-23 22:13:11');
INSERT INTO `captcha_tokens` VALUES ('34', '3bda60a20a4e53c55bf9816f3a04cb21', 'FB8A', '2026-06-23 22:14:33');
INSERT INTO `captcha_tokens` VALUES ('35', 'e6ca9afdb6be25805c79b8fb136b4f7c', 'HDDL', '2026-06-23 22:14:37');
INSERT INTO `captcha_tokens` VALUES ('36', '9a5336e6bf63f4c534e7976868e7428c', 'U6K9', '2026-06-23 22:14:39');
INSERT INTO `captcha_tokens` VALUES ('37', 'c3726a5b68a6df373e31e484b82f2429', 'BWZZ', '2026-06-23 22:16:17');
INSERT INTO `captcha_tokens` VALUES ('38', '0824892fb92a0cb4acd28bf7bbe3e092', 'E6E7', '2026-06-23 22:16:27');
INSERT INTO `captcha_tokens` VALUES ('39', '19cbb4da31a2b59ea417278bb126c6d0', 'QJ3Z', '2026-06-23 22:16:38');
INSERT INTO `captcha_tokens` VALUES ('40', '20044544dbfedf14707fccdc3b797878', 'A3C8', '2026-06-23 22:16:41');
INSERT INTO `captcha_tokens` VALUES ('43', '6dd2a1ce1656d5d676552cfafa1d7792', 'BUQT', '2026-06-23 22:41:06');
INSERT INTO `captcha_tokens` VALUES ('44', '0e64c7f6320ad4ffe1c65d8dbd342f2d', 'CNBX', '2026-06-23 22:41:24');
INSERT INTO `captcha_tokens` VALUES ('46', 'b5ba9c5898362b204e7b25872c3d49e1', 'JXSX', '2026-06-23 22:41:55');
INSERT INTO `captcha_tokens` VALUES ('47', 'a4e8950f40e9e608845b20d713d591ba', 'E3SN', '2026-06-23 22:41:57');
INSERT INTO `captcha_tokens` VALUES ('48', 'eabfbb18cb757061e33fd3c3594d12d1', 'VRBR', '2026-06-23 22:42:05');
INSERT INTO `captcha_tokens` VALUES ('51', '9c5eca49d957f696a3863c97f3982ff9', 'ZF4Z', '2026-06-23 23:19:17');
INSERT INTO `captcha_tokens` VALUES ('53', '07107ca7c0e061b41c0906b1fa5aef0c', '2YYD', '2026-06-23 23:22:14');
INSERT INTO `captcha_tokens` VALUES ('54', 'abde8ffe5f0802138f5032ab47f3ccc6', 'UHZF', '2026-06-23 23:22:52');
INSERT INTO `captcha_tokens` VALUES ('55', '4f725f7d521cf8bdc28e528658d75441', 'G69S', '2026-06-23 23:22:53');
INSERT INTO `captcha_tokens` VALUES ('56', '0b9f478ff69419b54cee26b7d14da4b9', 'QDPA', '2026-06-23 23:23:15');
INSERT INTO `captcha_tokens` VALUES ('57', '14df5cd690441b27c3d153e39adb8c99', 'LFU3', '2026-06-23 23:23:36');
INSERT INTO `captcha_tokens` VALUES ('58', 'f17907797bbae1c5608d52a8ce1646d1', 'UT5Z', '2026-06-23 23:23:53');
INSERT INTO `captcha_tokens` VALUES ('59', 'adb518b37d26d3dfee1bb28fe2e5b69d', 'DR8E', '2026-06-23 23:23:53');
INSERT INTO `captcha_tokens` VALUES ('60', '5dc136a6c285d32f8cf4e0228b6a3c40', '2N2E', '2026-06-23 23:23:59');
INSERT INTO `captcha_tokens` VALUES ('61', 'f732d6cff7d79ef37e49301dab6edef3', 'EU7V', '2026-06-23 23:24:02');
INSERT INTO `captcha_tokens` VALUES ('62', '546e567bb8a20d44e27162e66708bda6', '95MF', '2026-06-23 23:24:15');
INSERT INTO `captcha_tokens` VALUES ('63', '5d29572427849a1eb6e132d00659cd16', 'TS32', '2026-06-23 23:24:21');
INSERT INTO `captcha_tokens` VALUES ('64', 'b97614f10d0a4c347432d47a42a5c1c9', 'D7T5', '2026-06-23 23:24:26');
INSERT INTO `captcha_tokens` VALUES ('66', 'e7353ee44e697aef98a5a92242603d10', 'VD6H', '2026-06-23 23:29:04');
INSERT INTO `captcha_tokens` VALUES ('69', '467b6a526cd948155e261b2f1b2fdb8f', '9QSX', '2026-06-23 23:39:01');
INSERT INTO `captcha_tokens` VALUES ('70', 'e90017e8fd8600aa510d92e7fbe6753d', '8QPP', '2026-06-23 23:40:44');
INSERT INTO `captcha_tokens` VALUES ('78', '28ebf89edb5700f2a7ec0ee71e2c1570', '8VWS', '2026-06-23 23:51:29');

-- ----------------------------
-- Table structure for invite_codes
-- ----------------------------
DROP TABLE IF EXISTS `invite_codes`;
CREATE TABLE `invite_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `max_uses` int(11) DEFAULT '1',
  `used_count` int(11) DEFAULT '0',
  `expires_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of invite_codes
-- ----------------------------
INSERT INTO `invite_codes` VALUES ('1', '70B19BC2', '1', '0', '2026-06-30 20:39:47', '1', '2026-06-23 20:39:47');
INSERT INTO `invite_codes` VALUES ('2', 'FA99F67D', '1', '0', '2026-06-30 20:39:47', '1', '2026-06-23 20:39:47');
INSERT INTO `invite_codes` VALUES ('3', '76ADD58D', '1', '0', '2026-06-30 20:39:47', '1', '2026-06-23 20:39:47');
INSERT INTO `invite_codes` VALUES ('4', '5EA2F611', '1', '0', '2026-06-30 20:39:47', '1', '2026-06-23 20:39:47');
INSERT INTO `invite_codes` VALUES ('5', 'C8FC3484', '1', '0', '2026-06-30 20:39:47', '1', '2026-06-23 20:39:47');

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `detail` text,
  `ip` varchar(45) DEFAULT '',
  `user_agent` varchar(500) DEFAULT '',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of logs
-- ----------------------------
INSERT INTO `logs` VALUES ('132', '1', 'admin_clear_logs', '清除所有操作日志', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:34:21');
INSERT INTO `logs` VALUES ('133', '1', 'save_settings', '更新系统设置', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:35:15');
INSERT INTO `logs` VALUES ('134', '1', 'toggle_role', '用户ID 2 角色改为 member', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:36:02');
INSERT INTO `logs` VALUES ('135', '1', 'save_settings', '更新系统设置', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:36:26');
INSERT INTO `logs` VALUES ('136', '1', 'save_settings', '更新系统设置', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:36:57');
INSERT INTO `logs` VALUES ('137', '1', 'admin_update_project', '编辑作品: 你好', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:38:41');
INSERT INTO `logs` VALUES ('138', '2', 'login', '用户登录', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:38:47');
INSERT INTO `logs` VALUES ('139', '1', 'toggle_status', '用户ID 2 状态切换', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:41:03');
INSERT INTO `logs` VALUES ('140', '1', 'toggle_status', '用户ID 2 状态切换', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:41:26');
INSERT INTO `logs` VALUES ('141', '2', 'login', '用户登录', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:41:34');
INSERT INTO `logs` VALUES ('142', '1', 'toggle_role', '用户ID 2 角色改为 user', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:41:45');
INSERT INTO `logs` VALUES ('143', '1', 'toggle_role', '用户ID 2 角色改为 member', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:46:59');
INSERT INTO `logs` VALUES ('144', '1', 'admin_delete_project', '删除作品: 111', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:47:58');
INSERT INTO `logs` VALUES ('145', '1', 'admin_delete_project', '删除作品: 你好', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:48:05');
INSERT INTO `logs` VALUES ('146', '1', 'admin_delete_project', '删除作品: 444', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:48:07');
INSERT INTO `logs` VALUES ('147', '1', 'admin_delete_project', '删除作品: 444', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:48:09');
INSERT INTO `logs` VALUES ('148', '1', 'admin_delete_project', '删除作品: 11', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:48:10');
INSERT INTO `logs` VALUES ('149', '1', 'admin_delete_project', '删除作品: 2', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:48:12');
INSERT INTO `logs` VALUES ('150', '1', 'admin_delete_project', '删除作品: 1', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 23:48:13');

-- ----------------------------
-- Table structure for member_codes
-- ----------------------------
DROP TABLE IF EXISTS `member_codes`;
CREATE TABLE `member_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `days` int(11) NOT NULL DEFAULT '30',
  `status` enum('unused','used','expired') DEFAULT 'unused',
  `used_by` int(11) DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of member_codes
-- ----------------------------
INSERT INTO `member_codes` VALUES ('1', '26000D38B5C2', '30', 'used', '2', '2026-06-23 19:21:59', '2026-06-22 20:50:59');
INSERT INTO `member_codes` VALUES ('2', '385A6D5C60AC', '30', 'unused', null, null, '2026-06-22 20:50:59');
INSERT INTO `member_codes` VALUES ('3', '36720A46C70D', '30', 'unused', null, null, '2026-06-22 20:50:59');
INSERT INTO `member_codes` VALUES ('4', '8DE291F1ABC5', '30', 'used', '2', '2026-06-23 22:43:06', '2026-06-22 20:50:59');
INSERT INTO `member_codes` VALUES ('5', 'A3A88EA887C1', '30', 'unused', null, null, '2026-06-22 20:50:59');

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_no` varchar(30) NOT NULL,
  `trade_no` varchar(100) DEFAULT '',
  `amount` decimal(10,2) NOT NULL,
  `days` int(11) NOT NULL,
  `plan` varchar(20) DEFAULT 'month',
  `payment_method` varchar(20) DEFAULT 'epay',
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of orders
-- ----------------------------
INSERT INTO `orders` VALUES ('1', '2', 'TW202606231916128595', '', '2.00', '30', 'month', 'epay', 'pending', null, '2026-06-23 19:16:13');
INSERT INTO `orders` VALUES ('2', '2', 'TW202606231934139184', '', '2.00', '30', 'month', 'manual', 'pending', null, '2026-06-23 19:34:13');
INSERT INTO `orders` VALUES ('3', '2', 'TW202606231934182620', '', '2.00', '30', 'month', 'manual', 'pending', null, '2026-06-23 19:34:18');
INSERT INTO `orders` VALUES ('4', '2', 'TW202606231937336806', '', '2.00', '30', 'month', 'epay', 'pending', null, '2026-06-23 19:37:33');

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(50) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of password_resets
-- ----------------------------

-- ----------------------------
-- Table structure for projects
-- ----------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `tags` varchar(255) DEFAULT '',
  `short_code` varchar(20) NOT NULL,
  `file_size` bigint(20) DEFAULT '0',
  `file_count` int(11) DEFAULT '1',
  `expires_at` datetime DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `no_watermark` tinyint(1) DEFAULT '0',
  `is_public` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1' COMMENT '1=活跃,2=过期,3=违规',
  `view_count` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code` (`short_code`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of projects
-- ----------------------------

-- ----------------------------
-- Table structure for rate_limits
-- ----------------------------
DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ip_action` (`ip`,`action`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of rate_limits
-- ----------------------------
INSERT INTO `rate_limits` VALUES ('26', '192.168.1.156', 'upload', '2026-06-23 22:49:59');
INSERT INTO `rate_limits` VALUES ('27', '192.168.1.156', 'upload', '2026-06-23 22:50:10');

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text,
  `group` varchar(30) DEFAULT 'basic',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES ('admin_email', 'admin@tempweb.com', 'basic');
INSERT INTO `settings` VALUES ('allowed_types', 'html,htm,css,js,json,png,jpg,jpeg,gif,svg,ico,woff,woff2,ttf,eot,mp3,mp4,pdf,txt,xml', 'upload');
INSERT INTO `settings` VALUES ('allow_custom_domain', '0', 'security');
INSERT INTO `settings` VALUES ('allow_guest_upload', '1', 'basic');
INSERT INTO `settings` VALUES ('allow_register', '1', 'basic');
INSERT INTO `settings` VALUES ('anti_hotlink', '0', 'security');
INSERT INTO `settings` VALUES ('auto_cleanup', '1', 'upload');
INSERT INTO `settings` VALUES ('banned_files', '.env,.git,.htaccess', 'upload');
INSERT INTO `settings` VALUES ('ban_ips', '', 'security');
INSERT INTO `settings` VALUES ('captcha_login', '1', 'security');
INSERT INTO `settings` VALUES ('captcha_register', '1', 'security');
INSERT INTO `settings` VALUES ('custom_404_html', '', 'custom');
INSERT INTO `settings` VALUES ('custom_css', '', 'custom');
INSERT INTO `settings` VALUES ('custom_expired_html', '', 'custom');
INSERT INTO `settings` VALUES ('custom_head', '', 'custom');
INSERT INTO `settings` VALUES ('custom_home_html', '', 'custom');
INSERT INTO `settings` VALUES ('custom_js', '', 'custom');
INSERT INTO `settings` VALUES ('custom_login_bg', '', 'custom');
INSERT INTO `settings` VALUES ('dark_mode', '0', 'basic');
INSERT INTO `settings` VALUES ('default_expire', '30', 'upload');
INSERT INTO `settings` VALUES ('default_expire_guest', '3', 'upload');
INSERT INTO `settings` VALUES ('default_expire_member', '30', 'upload');
INSERT INTO `settings` VALUES ('email_verify', '0', 'mail');
INSERT INTO `settings` VALUES ('epay_key', '', 'payment');
INSERT INTO `settings` VALUES ('epay_pid', '', 'payment');
INSERT INTO `settings` VALUES ('epay_type', 'alipay', 'payment');
INSERT INTO `settings` VALUES ('epay_url', '', 'payment');
INSERT INTO `settings` VALUES ('footer_links', '[{\"name\":\"百度一下\",\"url\":\"https://www.baidu.com/\"},{\"name\":\"百度一下\",\"url\":\"https://www.baidu.com/\"}]', 'basic');
INSERT INTO `settings` VALUES ('hotlink_whitelist', '', 'security');
INSERT INTO `settings` VALUES ('invite_required', '0', 'basic');
INSERT INTO `settings` VALUES ('login_lock_count', '5', 'security');
INSERT INTO `settings` VALUES ('login_lock_time', '30', 'security');
INSERT INTO `settings` VALUES ('mail_expire_notify', '0', 'mail');
INSERT INTO `settings` VALUES ('mail_forgot', '0', 'mail');
INSERT INTO `settings` VALUES ('mail_from', '', 'mail');
INSERT INTO `settings` VALUES ('mail_from_name', 'TempWeb', 'mail');
INSERT INTO `settings` VALUES ('maintenance_html', '', 'custom');
INSERT INTO `settings` VALUES ('maintenance_mode', '0', 'basic');
INSERT INTO `settings` VALUES ('maintenance_msg', '系统维护中，请稍后访问', 'basic');
INSERT INTO `settings` VALUES ('manual_pay_info', '', 'payment');
INSERT INTO `settings` VALUES ('max_expire', '', 'upload');
INSERT INTO `settings` VALUES ('max_expire_member', '', 'upload');
INSERT INTO `settings` VALUES ('max_file_size', '20971520', 'upload');
INSERT INTO `settings` VALUES ('max_file_size_guest', '20971520', 'upload');
INSERT INTO `settings` VALUES ('max_file_size_member', '52428800', 'upload');
INSERT INTO `settings` VALUES ('max_zip_files', '500', 'upload');
INSERT INTO `settings` VALUES ('max_zip_size', '10485760', 'upload');
INSERT INTO `settings` VALUES ('member_feature', '1', 'basic');
INSERT INTO `settings` VALUES ('member_price_month', '2', 'payment');
INSERT INTO `settings` VALUES ('member_price_year', '2', 'payment');
INSERT INTO `settings` VALUES ('member_purchase_url', 'https://www.baidu.com/', 'basic');
INSERT INTO `settings` VALUES ('non_member_daily_limit', '2000', 'upload');
INSERT INTO `settings` VALUES ('password_page_html', '', 'custom');
INSERT INTO `settings` VALUES ('payment_method', 'epay', 'payment');
INSERT INTO `settings` VALUES ('rate_limit_api', '60', 'security');
INSERT INTO `settings` VALUES ('rate_limit_upload', '10', 'security');
INSERT INTO `settings` VALUES ('require_invite', '0', 'basic');
INSERT INTO `settings` VALUES ('sensitive_words', '', 'security');
INSERT INTO `settings` VALUES ('seo_baidu_id', '', 'seo');
INSERT INTO `settings` VALUES ('seo_description', '', 'seo');
INSERT INTO `settings` VALUES ('seo_google_id', '', 'seo');
INSERT INTO `settings` VALUES ('seo_keywords', '', 'seo');
INSERT INTO `settings` VALUES ('seo_project_title', '{project} - {site}', 'seo');
INSERT INTO `settings` VALUES ('seo_title', '{name} - {site}', 'seo');
INSERT INTO `settings` VALUES ('short_code_length', '6', 'upload');
INSERT INTO `settings` VALUES ('show_announcement_on_home', '1', 'basic');
INSERT INTO `settings` VALUES ('site_desc', '免费HTML/ZIP托管平台', 'basic');
INSERT INTO `settings` VALUES ('site_footer', '&copy; TempWeb - MIT开源协议', 'basic');
INSERT INTO `settings` VALUES ('site_icp', '', 'basic');
INSERT INTO `settings` VALUES ('site_keywords', '临时html,tempweb,html托管,zip托管', 'basic');
INSERT INTO `settings` VALUES ('site_logo', '', 'basic');
INSERT INTO `settings` VALUES ('site_name', '免费HTML/ZIP托管平台', 'basic');
INSERT INTO `settings` VALUES ('site_url', 'http://192.168.1.156:5566', 'basic');
INSERT INTO `settings` VALUES ('smtp_host', '', 'mail');
INSERT INTO `settings` VALUES ('smtp_pass', '', 'mail');
INSERT INTO `settings` VALUES ('smtp_port', '465', 'mail');
INSERT INTO `settings` VALUES ('smtp_ssl', '1', 'mail');
INSERT INTO `settings` VALUES ('smtp_user', '', 'mail');
INSERT INTO `settings` VALUES ('storage_quota', '209715200', 'upload');
INSERT INTO `settings` VALUES ('storage_quota_member', '1073741824', 'upload');
INSERT INTO `settings` VALUES ('upload_password', '', 'security');
INSERT INTO `settings` VALUES ('watermark', '1', 'upload');
INSERT INTO `settings` VALUES ('watermark_link', 'https://www.baidu.com/', 'upload');
INSERT INTO `settings` VALUES ('watermark_style', 'single', 'upload');
INSERT INTO `settings` VALUES ('watermark_text', 'Hosted by TempWeb', 'upload');
INSERT INTO `settings` VALUES ('xss_filter', '1', 'security');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(50) DEFAULT '',
  `role` enum('admin','member','user') DEFAULT 'user',
  `status` tinyint(1) DEFAULT '1',
  `storage_used` bigint(20) DEFAULT '0',
  `storage_quota` bigint(20) DEFAULT '524288000',
  `member_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'admin', 'admin@tempweb.com', '$2y$10$191NTfWDO..bu99JaWl2KOwpfJ3iZnwdOcKrXTMwMzORJJGuRdxK6', '', 'admin', '1', '0', '0', null, '2026-06-22 20:41:16', '2026-06-22 20:41:16');
INSERT INTO `users` VALUES ('2', '123456', '123456@qq.com', '$2y$10$tzktMZEDlNCYRHP1Iy/FM.2s4odsoczSrLrghFgL.j0UNuhR4fM5W', '', 'member', '1', '0', '524288000', '2026-07-23 23:46:59', '2026-06-22 20:51:27', '2026-06-23 23:48:13');
INSERT INTO `users` VALUES ('3', '1234567', '1234567@qq.com', '$2y$10$mbxFOh1djD3mzQImMnqiuOnjFc5SB/efv.v8Gto4gGlxHlBnF6zAu', '', 'user', '1', '0', '524288000', null, '2026-06-23 23:14:38', '2026-06-23 23:14:38');
INSERT INTO `users` VALUES ('4', '1234561', '1234561@qq.com', '$2y$10$WbSVR7tFxnXbP7YzJU3NuusR6BNPraYCRmzsF92ty3mzHIbTLGwCG', '', 'user', '1', '0', '209715200', null, '2026-06-23 23:19:17', '2026-06-23 23:20:26');

-- ----------------------------
-- Table structure for visit_logs
-- ----------------------------
DROP TABLE IF EXISTS `visit_logs`;
CREATE TABLE `visit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT '',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of visit_logs
-- ----------------------------
INSERT INTO `visit_logs` VALUES ('1', '1', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 23:59:54');
INSERT INTO `visit_logs` VALUES ('2', '1', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 00:07:30');
INSERT INTO `visit_logs` VALUES ('3', '2', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 00:08:36');
INSERT INTO `visit_logs` VALUES ('4', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 01:09:45');
INSERT INTO `visit_logs` VALUES ('5', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 19:24:50');
INSERT INTO `visit_logs` VALUES ('6', '5', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 21:49:47');
INSERT INTO `visit_logs` VALUES ('7', '6', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 21:51:42');
INSERT INTO `visit_logs` VALUES ('8', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 21:55:19');
INSERT INTO `visit_logs` VALUES ('9', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:28:12');
INSERT INTO `visit_logs` VALUES ('10', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:28:18');
INSERT INTO `visit_logs` VALUES ('11', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:28:38');
INSERT INTO `visit_logs` VALUES ('12', '2', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2026-06-23 22:32:50');
INSERT INTO `visit_logs` VALUES ('13', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:41:41');
INSERT INTO `visit_logs` VALUES ('14', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:41:48');
INSERT INTO `visit_logs` VALUES ('15', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:42:49');
INSERT INTO `visit_logs` VALUES ('16', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:43:13');
INSERT INTO `visit_logs` VALUES ('17', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:45:23');
INSERT INTO `visit_logs` VALUES ('18', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:45:24');
INSERT INTO `visit_logs` VALUES ('19', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:49:46');
INSERT INTO `visit_logs` VALUES ('20', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:49:46');
INSERT INTO `visit_logs` VALUES ('21', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 22:49:46');
INSERT INTO `visit_logs` VALUES ('22', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:03:24');
INSERT INTO `visit_logs` VALUES ('23', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:03:24');
INSERT INTO `visit_logs` VALUES ('24', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:03:24');
INSERT INTO `visit_logs` VALUES ('25', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:03:36');
INSERT INTO `visit_logs` VALUES ('26', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:03:49');
INSERT INTO `visit_logs` VALUES ('27', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:07:17');
INSERT INTO `visit_logs` VALUES ('28', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:07:17');
INSERT INTO `visit_logs` VALUES ('29', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:07:17');
INSERT INTO `visit_logs` VALUES ('30', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:07:58');
INSERT INTO `visit_logs` VALUES ('31', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:07:58');
INSERT INTO `visit_logs` VALUES ('32', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:07:58');
INSERT INTO `visit_logs` VALUES ('33', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:20:00');
INSERT INTO `visit_logs` VALUES ('34', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:20:00');
INSERT INTO `visit_logs` VALUES ('35', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:20:00');
INSERT INTO `visit_logs` VALUES ('36', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:20:02');
INSERT INTO `visit_logs` VALUES ('37', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:20:02');
INSERT INTO `visit_logs` VALUES ('38', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:20:02');
INSERT INTO `visit_logs` VALUES ('39', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:25:41');
INSERT INTO `visit_logs` VALUES ('40', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:30:58');
INSERT INTO `visit_logs` VALUES ('41', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:30:58');
INSERT INTO `visit_logs` VALUES ('42', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:07');
INSERT INTO `visit_logs` VALUES ('43', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:07');
INSERT INTO `visit_logs` VALUES ('44', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:08');
INSERT INTO `visit_logs` VALUES ('45', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:08');
INSERT INTO `visit_logs` VALUES ('46', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:10');
INSERT INTO `visit_logs` VALUES ('47', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:10');
INSERT INTO `visit_logs` VALUES ('48', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:18');
INSERT INTO `visit_logs` VALUES ('49', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:19');
INSERT INTO `visit_logs` VALUES ('50', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:31:30');
INSERT INTO `visit_logs` VALUES ('51', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:32:31');
INSERT INTO `visit_logs` VALUES ('52', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:32:42');
INSERT INTO `visit_logs` VALUES ('53', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:32:44');
INSERT INTO `visit_logs` VALUES ('54', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:32:48');
INSERT INTO `visit_logs` VALUES ('55', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:33:49');
INSERT INTO `visit_logs` VALUES ('56', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:33:49');
INSERT INTO `visit_logs` VALUES ('57', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:33:49');
INSERT INTO `visit_logs` VALUES ('58', '8', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:36:17');
INSERT INTO `visit_logs` VALUES ('59', '7', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:36:19');
INSERT INTO `visit_logs` VALUES ('60', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:40:03');
INSERT INTO `visit_logs` VALUES ('61', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:40:03');
INSERT INTO `visit_logs` VALUES ('62', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:40:04');
INSERT INTO `visit_logs` VALUES ('63', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:40:04');
INSERT INTO `visit_logs` VALUES ('64', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:40:11');
INSERT INTO `visit_logs` VALUES ('65', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:40:11');
INSERT INTO `visit_logs` VALUES ('66', '3', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:43:02');
INSERT INTO `visit_logs` VALUES ('67', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:43:02');
INSERT INTO `visit_logs` VALUES ('68', '4', '192.168.1.156', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 23:46:04');
