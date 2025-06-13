<?php
/**
 * db.php
 * 数据库连接脚本
 */

// 数据库配置
$db_host = '127.0.0.1';       // 数据库主机，通常是 localhost 或 127.0.0.1
$db_user = 'aibachang';       // 数据库用户名
$db_pass = 'aibachang';       // 数据库密码
$db_name = 'aibachang';       // 数据库名称

// 创建数据库连接
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// 设置字符集为 utf8mb4，以支持表情符号等
mysqli_set_charset($conn, "utf8mb4");

// 检查连接是否成功
if ($conn->connect_error) {
    // 如果连接失败，则输出错误信息并终止脚本
    die("数据库连接失败: " . $conn->connect_error);
} 