/*
 Navicat Premium Data Transfer

 Source Server Type    : MySQL
 Source Server Version : 50723

 Target Server Type    : MySQL
 Target Server Version : 50723
 File Encoding         : 65001

 Date: 25/10/2018 23:01:05
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for gym_admin
-- ----------------------------
DROP TABLE IF EXISTS `gym_admin`;
CREATE TABLE `gym_admin`  (
  `admin_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `uname` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '管理员登录名',
  `pwd` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '管理员密码',
  `mobile` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '管理员手机号',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未启用1已启用2已删除',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) NOT NULL DEFAULT 0 COMMENT '更新的用户ID',
  PRIMARY KEY (`admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_banner
-- ----------------------------
DROP TABLE IF EXISTS `gym_banner`;
CREATE TABLE `gym_banner`  (
  `banner_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Banner的ID',
  `banner_img` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'Banner的地址',
  `nav_type` tinyint(4) NOT NULL COMMENT '跳转方式1project',
  `nav_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '跳转对应的ID',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Banner排序',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新的用户ID',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1展示2不展示3删除',
  PRIMARY KEY (`banner_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_clause
-- ----------------------------
DROP TABLE IF EXISTS `gym_clause`;
CREATE TABLE `gym_clause`  (
  `clause_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户协议',
  PRIMARY KEY (`clause_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_clock
-- ----------------------------
DROP TABLE IF EXISTS `gym_clock`;
CREATE TABLE `gym_clock`  (
  `clock_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '打卡对应的用户ID',
  `user_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '用户类别',
  `course_id` int(11) UNSIGNED NOT NULL COMMENT '课程id',
  `clock_start_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打卡开始时间',
  `clock_end_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打卡结束时间',
  `clock_start_location` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打卡开始时所在地点',
  `clock_end_location` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '打卡结束时所在地点',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新的时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1开始打卡2结束打卡3删除',
  PRIMARY KEY (`clock_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_clock_count
-- ----------------------------
DROP TABLE IF EXISTS `gym_clock_count`;
CREATE TABLE `gym_clock_count`  (
  `idx` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `user_type` tinyint(4) NOT NULL COMMENT '用户类别',
  `non_stop_count` int(11) NOT NULL DEFAULT 1 COMMENT '连续打卡次数',
  `last_clock_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '上次打卡时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 统计中 2已结束统计',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_coach
-- ----------------------------
DROP TABLE IF EXISTS `gym_coach`;
CREATE TABLE `gym_coach`  (
  `coach_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '教练在user表中对应的ID',
  `coach_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `coach_phone` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '教练电话',
  `coach_gender` tinyint(4) NOT NULL DEFAULT 0 COMMENT '教练性别0女1男',
  `coach_birth` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '教练生日',
  `coach_img` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '教练头像',
  `coach_desc` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '教练简介',
  `auth_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '认证时的名称',
  `auth_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '教练认证的时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1未认证2已认证3已删除',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '更新信息的管理员账号',
  PRIMARY KEY (`coach_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_course
-- ----------------------------
DROP TABLE IF EXISTS `gym_course`;
CREATE TABLE `gym_course`  (
  `course_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '课程名称',
  `course_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '课程价格',
  `course_period` smallint(6) NOT NULL COMMENT '一节课的课时（单位min）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1显示2不显示3删除',
  PRIMARY KEY (`course_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_feedback
-- ----------------------------
DROP TABLE IF EXISTS `gym_feedback`;
CREATE TABLE `gym_feedback`  (
  `idx` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `content` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户反馈内容',
  `img` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '反馈的图片地址',
  `reply` varchar(140) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '回复的内容',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `reply_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '回复的时间',
  `reply_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回复的管理员ID',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1待回复2已回复3已删除',
  `user_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '用户类别1会员2教练',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_formid
-- ----------------------------
DROP TABLE IF EXISTS `gym_formid`;
CREATE TABLE `gym_formid`  (
  `idx` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL,
  `openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `formid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_expire` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未过期1已过期',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `used_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_menu
-- ----------------------------
DROP TABLE IF EXISTS `gym_menu`;
CREATE TABLE `gym_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `parent_id` tinyint(4) NOT NULL COMMENT '父级id',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '选项名称',
  `is_admin` int(2) NOT NULL DEFAULT 0 COMMENT '是否超级管理员0否 1是',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_mini_setting
-- ----------------------------
DROP TABLE IF EXISTS `gym_mini_setting`;
CREATE TABLE `gym_mini_setting`  (
  `setting_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mini_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序名称',
  `service_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '客服电话',
  `share_text` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小程序分享的文字',
  `store_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '门店信息',
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '门店坐标位置',
  `about_us` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '关于我们 图片',
  `about_us_video` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '关于我们 视频',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新的管理员ID',
  PRIMARY KEY (`setting_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_msg
-- ----------------------------
DROP TABLE IF EXISTS `gym_msg`;
CREATE TABLE `gym_msg`  (
  `msg_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `msg_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0公告1对指定用户发送',
  `msg_content` varchar(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '消息内容',
  `msg_img` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `target_uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '目标对象的uid 当type为1时有效',
  `target_openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '目标对象的openid 当type为1时有效',
  `send_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发送时间',
  `send_by` int(11) NOT NULL DEFAULT 0 COMMENT '发送者的ID',
  `created_at` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1不发送 2发送 3删除',
  PRIMARY KEY (`msg_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_power
-- ----------------------------
DROP TABLE IF EXISTS `gym_power`;
CREATE TABLE `gym_power`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `admin_id` int(4) NOT NULL COMMENT '管理员id',
  `menu_id` varchar(90) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '菜单id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_project
-- ----------------------------
DROP TABLE IF EXISTS `gym_project`;
CREATE TABLE `gym_project`  (
  `project_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '项目名称',
  `project_img` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '项目封面图',
  `project_video` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '项目视频地址',
  `project_desc` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '项目详情(图文)',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_by` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `update_at` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新的用户ID',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1展示2不展示3删除',
  `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '项目排序0-100 大者优先',
  PRIMARY KEY (`project_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_sms_log
-- ----------------------------
DROP TABLE IF EXISTS `gym_sms_log`;
CREATE TABLE `gym_sms_log`  (
  `sms_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户openid',
  `mobile` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `code` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '验证码',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '发送时间',
  PRIMARY KEY (`sms_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_user
-- ----------------------------
DROP TABLE IF EXISTS `gym_user`;
CREATE TABLE `gym_user`  (
  `uid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户openid',
  `user_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_gender` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0女1男 用户认证时所选择的性别 学生',
  `user_nickname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `user_avatar_url` varchar(130) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户头像地址',
  `user_city` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '微信设置城市',
  `user_province` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '微信设置省份',
  `user_country` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '微信设置国家',
  `user_language` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '微信设置语言',
  `user_mobile` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `user_birth` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '生日2018-09-07',
  `user_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0管理员1会员2教练',
  `auth_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户认证时的名字',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `update_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `auth_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '认证时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1未认证2已认证3已删除',
  PRIMARY KEY (`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for gym_user_course
-- ----------------------------
DROP TABLE IF EXISTS `gym_user_course`;
CREATE TABLE `gym_user_course`  (
  `idx` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `course_id` int(11) UNSIGNED NOT NULL COMMENT '课程id',
  `course_left_times` smallint(6) NOT NULL DEFAULT 0 COMMENT '课程剩余打卡次数',
  `start_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '课程开始时间',
  `end_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '课程结束时间',
  `created_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时间',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建者',
  `updated_at` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新时间',
  `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新者',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1正常2打卡结束3超时4已删除',
  PRIMARY KEY (`idx`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
