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
    <title>个人资料 - 东海学院网络靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1><a href="dashboard.php">东海学院网络靶场 控制板</a></h1>
            <div class="user-menu">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['nickname'] ?: $_SESSION['username']); ?></span>
                <a href="profile.php" class="btn-profile">个人资料</a>
                <a href="logout.php" class="btn-logout">退出登录</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="profile-layout">
            <div class="profile-sidebar">
                <div class="profile-avatar-placeholder">头像</div>
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
            </div>
            <div class="profile-main">
                <h2>个人资料</h2>
                <div class="profile-info">
                    <div class="info-item">
                        <strong>昵称:</strong>
                        <span><?php echo htmlspecialchars($user['nickname'] ?: '未设置'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>生日:</strong>
                        <span><?php echo htmlspecialchars($user['birthdate'] ?: '未设置'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>性别:</strong>
                        <span><?php echo htmlspecialchars($user['gender'] ?: '未设置'); ?></span>
                    </div>
                    <div class="info-item bio-item">
                        <strong>个人简介:</strong>
                        <div class="bio-box">
                            <?php echo htmlspecialchars($user['bio'] ?: '这个人很懒，什么都没有留下...'); ?>
                        </div>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 25px;">
                    <a href="edit_profile.php" class="btn">编辑资料</a>
                    <a href="dashboard.php" class="btn-cancel">返回控制板</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
        <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
    </footer>
</body>
</html>