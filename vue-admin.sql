/*
Navicat MySQL Data Transfer

Source Server         : php
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : vue-admin

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-12-08 21:25:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lmx_admin
-- ----------------------------
DROP TABLE IF EXISTS `lmx_admin`;
CREATE TABLE `lmx_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；sp_password加密',
  `tel` varchar(50) NOT NULL DEFAULT '' COMMENT '用户手机号',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '登录邮箱',
  `avatar` varchar(255) DEFAULT NULL COMMENT '用户头像',
  `sex` smallint(1) DEFAULT '0' COMMENT '性别；0：保密，1：男；2：女',
  `last_login_ip` varchar(16) DEFAULT NULL COMMENT '最后登录ip',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常 ；2：未验证',
  PRIMARY KEY (`id`),
  KEY `user_login_key` (`username`),
  KEY `user_nicename` (`tel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Records of lmx_admin
-- ----------------------------
INSERT INTO `lmx_admin` VALUES ('1', 'admin', 'c3284d0f94606de1fd2af172aba15bf3', 'admin', 'lmxdawn@gmail.com', null, '0', '127.0.0.1', '1493103488', '1487868050', '1');

-- ----------------------------
-- Table structure for lmx_auth_access
-- ----------------------------
DROP TABLE IF EXISTS `lmx_auth_access`;
CREATE TABLE `lmx_auth_access` (
  `role_id` int(11) unsigned NOT NULL COMMENT '角色',
  `auth_rule_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限id',
  `type` varchar(30) DEFAULT NULL COMMENT '权限规则分类，请加应用前缀,如admin_',
  KEY `role_id` (`role_id`),
  KEY `rule_name` (`auth_rule_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='权限授权表';

-- ----------------------------
-- Records of lmx_auth_access
-- ----------------------------

-- ----------------------------
-- Table structure for lmx_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `lmx_auth_rule`;
CREATE TABLE `lmx_auth_rule` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则编号',
  `pid` int(11) DEFAULT '0' COMMENT '父级id',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一标识',
  `title` char(20) DEFAULT '' COMMENT '规则中文名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `condition` char(100) DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `listorder` int(10) DEFAULT '0' COMMENT '排序，优先级，越小优先级越高',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='规则表';

-- ----------------------------
-- Records of lmx_auth_rule
-- ----------------------------
INSERT INTO `lmx_auth_rule` VALUES ('1', '0', 'user_manage', '用户管理', '1', '', '999', '1514526793', '1514526793');
INSERT INTO `lmx_auth_rule` VALUES ('2', '1', 'user_manage/admin', '管理组', '1', '', '999', '1514526814', '1514526814');
INSERT INTO `lmx_auth_rule` VALUES ('3', '2', 'admin/admin/index', '管理员管理', '1', '', '999', '1514526827', '1514526827');
INSERT INTO `lmx_auth_rule` VALUES ('4', '3', 'admin/admin/save', '添加管理员', '1', '', '999', '1514526964', '1514526964');
INSERT INTO `lmx_auth_rule` VALUES ('5', '3', 'admin/admin/edit', '编辑管理员', '1', '', '999', '1514527000', '1514527000');
INSERT INTO `lmx_auth_rule` VALUES ('6', '3', 'admin/admin/delete', '删除管理员', '1', '', '999', '1514527028', '1514527028');
INSERT INTO `lmx_auth_rule` VALUES ('7', '2', 'admin/role/index', '角色管理', '1', '', '999', '1514527048', '1514527048');
INSERT INTO `lmx_auth_rule` VALUES ('8', '7', 'admin/role/save', '添加角色', '1', '', '999', '1514527080', '1514527080');
INSERT INTO `lmx_auth_rule` VALUES ('9', '7', 'admin/role/edit', '编辑角色', '1', '', '999', '1514527090', '1514527090');
INSERT INTO `lmx_auth_rule` VALUES ('10', '7', 'admin/role/delete', '删除角色', '1', '', '999', '1514527111', '1514527111');
INSERT INTO `lmx_auth_rule` VALUES ('11', '7', 'admin/role/auth', '角色授权', '1', '', '999', '1514527131', '1514527131');
INSERT INTO `lmx_auth_rule` VALUES ('12', '2', 'admin/authrule/index', '权限管理', '1', '', '999', '1514527153', '1514527153');
INSERT INTO `lmx_auth_rule` VALUES ('13', '12', 'admin/authrule/save', '添加权限', '1', '', '999', '1514527182', '1514527182');
INSERT INTO `lmx_auth_rule` VALUES ('14', '12', 'admin/authrule/edit', '编辑权限', '1', '', '999', '1514527195', '1514527209');
INSERT INTO `lmx_auth_rule` VALUES ('15', '12', 'admin/authrule/delete', '删除权限', '1', '', '999', '1514527223', '1514527223');


-- ----------------------------
-- Table structure for lmx_role
-- ----------------------------
DROP TABLE IF EXISTS `lmx_role`;
CREATE TABLE `lmx_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `pid` smallint(6) DEFAULT NULL COMMENT '父角色ID',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` int(3) NOT NULL DEFAULT '0' COMMENT '排序，优先级，越小优先级越高',
  PRIMARY KEY (`id`),
  KEY `parentId` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='角色表';

-- ----------------------------
-- Records of lmx_role
-- ----------------------------
INSERT INTO `lmx_role` VALUES ('1', '超级管理员', '0', '1', '拥有网站最高管理员权限！', '1329633709', '1329633709', '0');

-- ----------------------------
-- Table structure for lmx_role_admin
-- ----------------------------
DROP TABLE IF EXISTS `lmx_role_admin`;
CREATE TABLE `lmx_role_admin` (
  `role_id` int(11) unsigned DEFAULT '0' COMMENT '角色 id',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员id',
  KEY `group_id` (`role_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户角色对应表';

-- ----------------------------
-- Table structure for lmx_file_resource
-- ----------------------------
DROP TABLE IF EXISTS `lmx_file_resource`;
CREATE TABLE `lmx_file_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '资源id',
  `tag_id` int(11) DEFAULT '0' COMMENT '资源分组id',
  `type` tinyint(4) DEFAULT '0' COMMENT '资源的类型（0：图片）',
  `filename` varchar(255) DEFAULT NULL COMMENT '资源的原名',
  `path` varchar(255) DEFAULT NULL COMMENT '资源的路径（不加 域名的地址）',
  `size` int(11) DEFAULT '0' COMMENT '大小',
  `ext` varchar(10) DEFAULT NULL COMMENT '资源的文件后缀',
  `create_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资源表';

-- ----------------------------
-- Table structure for lmx_file_resource_tag
-- ----------------------------
DROP TABLE IF EXISTS `lmx_file_resource_tag`;
CREATE TABLE `lmx_file_resource_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '资源分组的id',
  `tag` varchar(255) DEFAULT NULL COMMENT '资源分组的tag',
  `create_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资源的分组表';
