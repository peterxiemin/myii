Create Table: CREATE TABLE `env` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `env_name` varchar(100) DEFAULT NULL COMMENT '环境名称',
  `branch_name` varchar(100) DEFAULT NULL COMMENT '分支名称',
  `hostname` varchar(100) NOT NULL DEFAULT '' COMMENT '主机名',
  `path` varchar(200) DEFAULT NULL COMMENT '部署目录',
  `port` int(11) DEFAULT NULL COMMENT '服务端口',
  `agent_url` varchar(200) DEFAULT NULL COMMENT '代理地址',
  `discription` varchar(200) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8


alter table env add column status tinyint(2) NOT NULL default 0 COMMENT '分支环境状态';