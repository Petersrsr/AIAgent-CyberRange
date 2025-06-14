<?php
/**
 * profile.php
 * 显示用户个人资料的页面
 */

// 开启 Session
session_start();
require_once 'db.php';

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// 从数据库获取完整的用户信息
$stmt = $conn->prepare("SELECT username, nickname, birthdate, gender, bio FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人资料 - AI靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1><a href="dashboard.php" style="color: white; text-decoration: none;">AI靶场 控制板</a></h1>
            <div class="user-menu">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['nickname'] ?: $_SESSION['username']); ?></span>
                <a href="profile.php" class="btn-profile">个人资料</a>
                <a href="logout.php" class="btn-logout">退出登录</a>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <h2>个人资料</h2>
        <div class="profile-info">
            <p><strong>用户名:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>昵称:</strong> <?php echo htmlspecialchars($user['nickname'] ?: '未设置'); ?></p>
            <p><strong>生日:</strong> <?php echo htmlspecialchars($user['birthdate'] ?: '未设置'); ?></p>
            <p><strong>性别:</strong> <?php echo htmlspecialchars($user['gender'] ?: '未设置'); ?></p>
            <p><strong>个人简介:</strong></p>
            <div class="bio-box">
                <?php echo nl2br(htmlspecialchars($user['bio'] ?: '未设置')); ?>
            </div>
        </div>
        <div class="form-actions">
            <a href="edit_profile.php" class="btn">编辑资料</a>
            <a href="dashboard.php" class="btn-cancel">返回控制板</a>
        </div>
    </div>
</body>
</html>