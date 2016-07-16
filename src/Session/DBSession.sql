CREATE DATABASE `sbdc2016` ;
CREATE USER 'sbdc2016'@'localhost' IDENTIFIED BY 'sbdc2016!..'; 
GRANT SELECT,INSERT,UPDATE,DELETE ON `sbdc2016`.* TO 'sbdc2016'@'localhost';
GRANT ALL PRIVILEGES ON *.* TO sbdc2016@'%' IDENTIFIED BY 'sbdc2016!..';
FLUSH PRIVILEGES;
#---------------------------------------------------------------
USE `sessionsDB`;
 #DROP TABLE IF EXISTS `sessions`;
#---------------------------------------------------------------
CREATE TABLE `sessions` (
 `no` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
 `id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '세션ID',
 `address` VARCHAR(15) NOT NULL DEFAULT '' COMMENT '세션접속IP',
 `agent` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '세션접속Agent',
 `userid` VARCHAR(64) DEFAULT NULL COMMENT '사용자ID',
 `preexistence` INT(11) DEFAULT NULL COMMENT '존재여부',
 `privilege` TEXT COMMENT '세션정보',
 `server` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '접속서버정보',
 `request` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '요청정보',
 `referer` VARCHAR(255) DEFAULT NULL COMMENT '참조정보',
 `timer` FLOAT NOT NULL DEFAULT '0' COMMENT '타이머',
 `created` INT(11) NOT NULL DEFAULT '0' COMMENT '생성시간',
 `updated` INT(11) NOT NULL DEFAULT '0' COMMENT '수정시간',
 `session_key` TEXT NOT NULL COMMENT '세션KEY',
 PRIMARY KEY (`no`), UNIQUE KEY `idx_sessions_id` (`id`), KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
