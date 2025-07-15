-- ----------------------------
-- 数据库: `aibachang`
-- ----------------------------
-- CREATE DATABASE IF NOT EXISTS `aibachang` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `aibachang`;

-- ----------------------------
-- 表结构 `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码（MD5加密）',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '昵称',
  `birthdate` date NULL DEFAULT NULL COMMENT '生日',
  `gender` enum('男','女','保密') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '保密' COMMENT '性别',
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '个人简介',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_username`(`username`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- 插入示例数据
-- 密码 '123456' 的 MD5 值为 'e10adc3949ba59abbe56e057f20f883e'
-- ----------------------------
INSERT INTO `users` (`username`, `password`) VALUES ('admin', 'e10adc3949ba59abbe56e057f20f883e'); 
INSERT INTO `users` (`username`, `password`) VALUES ('luoshuhong', 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `users` (`username`, `password`) VALUES ('user1', 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `users` (`username`, `password`) VALUES ('user2', 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `users` (`username`, `password`) VALUES ('user3', 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `users` (`username`, `password`) VALUES ('user4', 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `users` (`username`, `password`) VALUES ('user5', 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `users` (`username`, `password`) VALUES ('user6', 'e10adc3949ba59abbe56e057f20f883e'); 

-- ----------------------------
-- 表结构 `challenge_records`
-- ----------------------------
CREATE TABLE IF NOT EXISTS challenge_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user VARCHAR(50) NOT NULL,
  challenge VARCHAR(100) NOT NULL,
  level ENUM('easy','medium','hard','impossible') NOT NULL,
  completed_at DATETIME NOT NULL,
  time_used INT DEFAULT 0,
  error_count INT NOT NULL DEFAULT 0
); 
ALTER TABLE challenge_records ADD COLUMN action_detail VARCHAR(255) DEFAULT ''; 