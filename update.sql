



-- 11-8

ALTER TABLE `s_goods` ADD `user_telegram` VARCHAR(100) NOT NULL COMMENT 'telegram账号' AFTER `goods_type`;




-- 11-6

-- 担保人多选
CREATE TABLE `s_goods_assure_style` (
  `goods_assure_style_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品编号',
  `assure_style_id` int(11) DEFAULT NULL COMMENT '担保人',
  PRIMARY KEY (`goods_assure_style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- 11-5

-- 用户表增加telegram联系方式
ALTER TABLE `s_user` ADD `user_telegram` VARCHAR(100) NULL COMMENT 'telegram账号' AFTER `locking_money`;

-- 商品表增加担保方式

ALTER TABLE `s_goods` ADD `assure_style` VARCHAR(20) NULL COMMENT '担保方式' AFTER `upd_time`;
-- 商品表增加供需类型
ALTER TABLE `s_goods` ADD `goods_type` INT(11) NOT NULL COMMENT '1 需求 2 供应' AFTER `assure_style`;





-- 11-3
CREATE TABLE `s_receipt_log` (
  `receipt_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `reacharge_usdt_address` varchar(255) DEFAULT NULL COMMENT '充值地址',
  `by_reacharge_usdt_address` varchar(255) DEFAULT NULL COMMENT '被充值地址',
  `receipt_log_value` varchar(255) DEFAULT NULL COMMENT '交易金额',
  `receipt_log_hash` varchar(255) DEFAULT NULL COMMENT '交易哈希',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `timeStamp` int(11) DEFAULT NULL COMMENT '时间戳',
  `receipt_log_text` text COMMENT '对象',
  PRIMARY KEY (`receipt_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;







-- 11-1
CREATE TABLE `s_plugins_wallet_recharge` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `wallet_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '钱包id',
  `recharge_no` char(60) NOT NULL DEFAULT '' COMMENT '充值单号',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0未支付, 1已支付）',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金额',
  `pay_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `payment_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式id',
  `payment` char(60) NOT NULL DEFAULT '' COMMENT '支付方式标记',
  `payment_name` char(60) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `reacharge_usdt_address` varchar(50) NOT NULL COMMENT '充值的usdt地址',
  `by_reacharge_usdt_address` varchar(50) NOT NULL COMMENT '被充值的usdt地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `recharge_no` (`recharge_no`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='钱包充值 - 应用';



INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES
(443, 0, '钱包管理', 'recharge', 'default', 20, 1, '', 1603886844),
(444, 443, '充值列表', 'plugins', 'index/pluginsname/wallet/pluginscontrol/recharge/pluginsaction/index', 0, 1, '', 1603888741),
(445, 443, '收款账户', 'Receipt', 'index', 0, 1, '', 1604151833),
(446, 443, '收款账户新增/编辑页面', 'receipt', 'saveinfo', 0, 0, '', 1604155006),
(447, 443, '收款账户新增/编辑', 'Receipt', 'save', 0, 0, '', 1604158786),
(448, 443, '收款账户详情', 'Receipt', 'Detail', 0, 0, '', 1604159201),
(449, 443, '收款账户删除', 'Receipt', 'Delete', 0, 0, '', 1604159524),
(450, 443, '收款账户状态更新', 'Receipt', 'StatusUpdate', 0, 0, '', 1604159784);

UPDATE `s_power` SET `is_show` = '0' WHERE `s_power`.`id` = 340;


INSERT INTO `s_config` (`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES (NULL, '美元', '钱包单位', '钱包单位', '请输入', 'home', 'home_site_wallet_units', '0');



-- 2020/11/2增加用户支付密码字段
ALTER TABLE `shopxo`.`s_user`
ADD COLUMN `pay_pwd` varchar(100) NOT NULL DEFAULT '' COMMENT '用户支付密码' AFTER `upd_time`


INSERT INTO `shopxo`.`s_config`(`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`) VALUES (167, '13556609715,8323215894', '在线客服', '支持输入多个联系方式以'#'分隔', '请输入', 'home', 'home_kefu')

ALTER TABLE `shopxo`.`s_goods_category`
ADD COLUMN `rate` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '费率' AFTER `upd_time`


ALTER TABLE `shopxo`.`s_goods`
ADD COLUMN `deposit` decimal(8, 2) NOT NULL DEFAULT 0.00 COMMENT '需要押金' AFTER `upd_time`




-- 11-9
-- 商品价格字段
ALTER TABLE `s_goods` ADD `goods_price` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '商品价格' AFTER `user_telegram`;
-- 商品表增加商品可靠度字段
ALTER TABLE `s_goods` ADD `goods_reliability` INT(3) NOT NULL DEFAULT '0' COMMENT '商品可靠度' AFTER `goods_price`;


---新增商品是否支持担保  支持担保  值如何商品担保表
ALTER TABLE `shopxo`.`s_goods`
DROP COLUMN `is_assure`,
ADD COLUMN `is_assure` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否支持担保 1：支持担保 0:不担保' AFTER `deposit`



-- 11-13

CREATE TABLE `s_service` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `icon` char(255) NOT NULL DEFAULT '' COMMENT 'icon图标',
  `name` char(30) NOT NULL COMMENT '名称',
  `service_telegram` char(50) NOT NULL COMMENT 'telegram账号',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（0否，1是）',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '顺序',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `is_enable` (`is_enable`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='客服列表';

INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES
(451, 222, '客服管理', 'Service', 'Index', 0, 1, '', 1605193113),
(452, 222, '客服添加/编辑', 'Service', 'Save', 0, 0, '', 1605193113),
(453, 222, '客服删除', 'Service', 'Delete', 0, 0, '', 1605193113);


ALTER TABLE `shopxo`.`s_goods_category`
ADD COLUMN `level` tinyint(1) NOT NULL DEFAULT 1 COMMENT '层级' AFTER `rate`

ALTER TABLE `shopxo`.`s_article_category`
ADD COLUMN `is_news_cate` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否是新闻特有分类' AFTER `upd_time`

ALTER TABLE `shopxo`.`s_article`
ADD COLUMN `img` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图 新闻咨询特有的' AFTER `upd_time`


-- 虚拟货币最新汇率
CREATE TABLE `s_rate_coin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `coin_type` char(30) NOT NULL DEFAULT 'usdt' COMMENT '币种',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cny_rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '对应人民币汇率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟货币最新购买汇率';


ALTER TABLE `shopxo`.`s_goods`
ADD COLUMN `place_area` varchar(30) NOT NULL DEFAULT '' COMMENT '同台地区中文 tip:以这个为准' AFTER `is_assure`



-- 如果不存在这个表就执行
CREATE TABLE `s_plugins_wallet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常, 1异常, 2已注销）',
  `normal_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效金额（包含赠送金额）',
  `frozen_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `give_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额（所有赠送金额总计）',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='钱包 - 应用';


ALTER TABLE `shopxo`.`s_user`
ADD COLUMN `register_ip` varchar(30) NOT NULL DEFAULT '' COMMENT '注册ip' AFTER `user_telegram`


-- tg用户投诉表
CREATE TABLE `s_tg_complaint` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tg_username` varchar(255) NOT NULL DEFAULT '' COMMENT 'tg用户名',
  `score` tinyint(1) unsigned NOT NULL DEFAULT '80' COMMENT '信誉得分',
  `reason` varchar(255) DEFAULT NULL COMMENT '理由',
  `talk_logs` text COMMENT '聊天记录',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tg_username` (`tg_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tg用户投诉表';



ALTER TABLE `shopxo`.`s_goods`
ADD COLUMN `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '产品发布用户id' AFTER `goods_rate`




ALTER TABLE `shopxo`.`s_user`
ADD COLUMN `user_reliability` tinyint(1) UNSIGNED NOT NULL DEFAULT 6 COMMENT '用户等级0到10' AFTER `register_ip`


ALTER TABLE `shopxo`.`s_goods_category`
ADD COLUMN `limit_moneys` varchar(150) NOT NULL DEFAULT '' COMMENT '限制金额' AFTER `level`


ALTER TABLE `shopxo`.`s_goods_category`
MODIFY COLUMN `day_order_count` smallint(5) NOT NULL DEFAULT 0 COMMENT '日单量' AFTER `limit_moneys`



ALTER TABLE `shopxo`.`s_goods_category`
ADD COLUMN `is_one_day` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否24小时' AFTER `day_order_count`



ALTER TABLE `shopxo`.`s_goods`
ADD COLUMN `check_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '商品审核状态 0：待审核 1审核通过  2审核拒绝' AFTER `uid`


alter table s_goods add column `limit_moneys` varchar(150) NOT NULL DEFAULT '' COMMENT '限制金额'
alter table s_goods add`day_order_count` smallint(5) NOT NULL DEFAULT '0' COMMENT '日单量'
alter table s_goods add`is_one_day` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否24小时'




-- 12-11

INSERT INTO `s_config` (`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES (NULL, '123123', 'TelegramBotToken', 'TelegramBotToken', '请输入', 'common', 'common_site_telegram_bot_token', '0');
INSERT INTO `s_config` (`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES (NULL, '123123', 'TelegramBot群组ID', 'TelegramBot群组ID', '请输入', 'common', 'common_site_telegram_bot_chat_id', '0');


-- 12-11
  ALTER TABLE `s_goods`
ADD COLUMN `check_success_time`  int(11) NULL DEFAULT 0 COMMENT '审核成功时间' AFTER `day_order_count`

-- 12-12

INSERT INTO `s_config` (`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES (NULL, '这是一个消息备注', 'Telegram消息底部显示', 'Telegram消息底部显示', '请输入', 'common', 'common_site_telegram_bot_message_remarks', '0');


-- 12-13
ALTER TABLE `s_goods`
ADD COLUMN `goods_type_info`  varchar(255) NULL COMMENT '供需说明' AFTER `check_success_time`

-- 12-15
INSERT INTO `s_config` (`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES (NULL, '123123', 'Telegram机器人名称', 'Telegram机器人名称', '请输入', 'common', 'common_site_telegram_bot_name', '0');

CREATE TABLE `s_exposure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `exposure_name` varchar(255) NOT NULL COMMENT '名称',
  `exposure_gateway` varchar(255) NOT NULL COMMENT '网关',
  `integral` int(11) NOT NULL DEFAULT '0' COMMENT '信誉积分',
  `add_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `upd_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `is_delete_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;




INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES ('455', '0', '曝光管理', 'Exposure', 'default', '10', '1', 'icon-application', '1608040070');
INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES ('456', '455', '曝光列表', 'Exposure', 'index', '0', '1', '', '1608040168');
INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES ('457', '455', '曝光添加/编辑页面', 'Exposure', 'SaveInfo', '0', '0', '', '1608040206');
INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES ('458', '455', '曝光添加/编辑', 'Exposure', 'Save', '0', '0', '', '1608040244');
INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES ('459', '455', '曝光删除', 'Exposure', 'Delete', '0', '0', '', '1608040276');
INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES ('460', '455', '曝光详情', 'Exposure', 'Detail', '0', '0', '', '1608040300');




-- 12-18

ALTER TABLE `s_exposure`
ADD COLUMN `tg_uid`  int(11) NOT NULL COMMENT '被投诉者id' AFTER `is_delete_time`,
ADD COLUMN `tg_user`  varchar(255) NOT NULL COMMENT '被投诉者账号' AFTER `tg_uid`,
ADD COLUMN `complaint_reason`  enum('3','2','1') NOT NULL DEFAULT '1' COMMENT '投诉原因 1  骗钱 2 忽悠 3 不真实' AFTER `tg_user`,
ADD COLUMN `complaint_uid`  int(11) NOT NULL COMMENT '投诉人id' AFTER `complaint_reason`,
ADD COLUMN `complaint_username`  varchar(255) NULL COMMENT '投诉人账号' AFTER `complaint_uid`;

-- 12-22

ALTER TABLE `s_user`
ADD COLUMN `user_telegram_id`  varchar(20) NULL COMMENT 'telegram账号id' AFTER `user_reliability`;



ALTER TABLE `s_admin`
ADD COLUMN `user_telegram_id`  varchar(20) NULL COMMENT 'telegram账号id' AFTER `upd_time`,
ADD COLUMN `user_telegram`  varchar(100) NULL COMMENT 'telegram账号' AFTER `user_telegram_id`

-- 12-23

CREATE TABLE `s_exposurepay` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `exposurepay_name` varchar(255) NOT NULL COMMENT '名称',
  `exposurepay_gateway` varchar(255) NOT NULL COMMENT '网关',
  `add_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `upd_time` int(11) DEFAULT NULL COMMENT '修改时间',
  `is_delete_time` int(11) DEFAULT '0',
  `complaint_reason` varchar(255) NOT NULL COMMENT '投诉原因',
  `complaint_uid` int(11) NOT NULL COMMENT '投诉人id',
  `complaint_username` varchar(255) DEFAULT NULL COMMENT '投诉人账号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;


INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (null, '455', '曝光支付列表', 'Exposurepay', 'index', '0', '1', '', '1608738082');
INSERT INTO `s_power` (`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (null, '455', '曝光支付删除', 'Exposurepay', 'Delete', '0', '0', '', '1608738125');


#tg广告推送配置
CREATE TABLE `s_tg_ad` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad_content` varchar(255) NOT NULL COMMENT '广告内容',
  `push_time` varchar(10) NOT NULL COMMENT '每日广告推送时间',
  `is_able` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `add_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `n_isable` (`is_able`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;


ALTER TABLE `shopxo`.`s_tg_ads`
MODIFY COLUMN `push_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT



INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (480, 0, 'TG广告管理', 'TgAd', 'default', 11, 1, 'icon-application', 1608040070);
INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (481, 480, '广告列表', 'TgAd', 'index', 0, 1, '', 1608040168);
INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (482, 480, 'TG广告添加/编辑页面', 'TgAd', 'SaveInfo', 0, 0, '', 1608040168);
INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (483, 480, 'TG广告删除', 'TgAd', 'Delete', 0, 0, '', 1608040168);
INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (484, 480, 'TG广告添加/编辑', 'TgAd', 'Save', 0, 0, '', 1608040168);
INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (485, 480, 'TG广告转换启动', 'TgAd', 'StatusUpdate', 0, 0, '', 1608040168);


ALTER TABLE `shopxo`.`s_exposure`
MODIFY COLUMN `complaint_reason` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '投诉原因' AFTER `tg_user`


ALTER TABLE `shopxo`.`s_user`
ADD COLUMN `say_times` int(11) NOT NULL DEFAULT 0 COMMENT '发言次数' AFTER `user_telegram_id`


ALTER TABLE `shopxo`.`s_user`
ADD COLUMN `desc` varchar(255) NOT NULL COMMENT '用户自我备注' AFTER `say_times`

INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (487, 81, '机器人广播消息', 'Tgbot', 'sendMessage', 40, 0, '', 1486561615);
INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (486, 81, 'Tg机器人管理', 'Tgbot', 'Index', 40, 1, '', 1486561615);




CREATE TABLE `s_tg_banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `contents` varchar(255) NOT NULL DEFAULT '' COMMENT '广告内容',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `expire_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `expire_time` (`expire_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='tg广告推送内容';




INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (455, 222, 'TG广告', 'TgBanner', 'Index', 0, 1, '', 1486183367);


INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (456, 222, 'TG广告添加/编辑', 'TgBanner', 'saveinfo', 0, 0, '', 1486183367);


INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (457, 222, 'TG广告保存', 'TgBanner', 'Save', 0, 0, '', 0);

INSERT INTO `shopxo`.`s_power`(`id`, `pid`, `name`, `control`, `action`, `sort`, `is_show`, `icon`, `add_time`) VALUES (458, 222, 'TG广告删除', 'TgBanner', 'Delete', 0, 0, '', 0);



----tg广告机器人token
INSERT INTO `shopxo`.`s_config`( `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES ('1818959975:AAFnBiQDR4AdM6toiMwIcgXjutptgBI62zg', 'tg广告机器人', 'tg广告机器人', '', '', 'tgbanner_bot_token', 0);



INSERT INTO `shopxo`.`s_config`(`id`, `value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES (172, 'fuck,黄色,尼玛', 'TG广告词黑名单', 'TG广告词黑名单', '请输入', 'home', 'home_tg_illegal_keyworlds', 1623741523);



CREATE TABLE `s_tg_white_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `tg_id` char(255) NOT NULL DEFAULT '' COMMENT 'tgid',
  `tg_username` char(30) NOT NULL COMMENT 'tg用户名',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='Tg群成员白名单';


CREATE TABLE `s_mch_visite_url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `url` char(60) NOT NULL DEFAULT '' COMMENT '访问链接',
  `is_used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '链接状态是否使用',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `updated_at` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `is_used` (`is_used`),
  KEY `add_time` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='商户交易链接表';






CREATE TABLE `s_mch_visite_url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `url` char(60) NOT NULL DEFAULT '' COMMENT '访问链接',
  `is_used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '链接状态是否使用',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `updated_at` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `is_used` (`is_used`),
  KEY `add_time` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='商户交易链接表';





INSERT INTO s_config (`value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES ('1773472906:AAHP1BlJaCRrjUGbJtB2tj1RI2pbtDJipbg', '订单报警机器人token', '', '', '', 'tg_order_warning_robot_token', 0);
INSERT INTO  s_config(`value`, `name`, `describe`, `error_tips`, `type`, `only_tag`, `upd_time`) VALUES ('111', '订单报警机器人所在群组', '', '', '', 'tg_order_warning_rebot_in_chat', 0);





ALTER TABLE `shopxo`.`s_user`
ADD COLUMN `pid` int(11) NOT NULL COMMENT '上级id' AFTER `user_reliability`