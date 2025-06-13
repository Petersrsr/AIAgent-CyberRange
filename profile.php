<?php
/**
 * profile.php
 * 显示用户个人资料的页面
 */

// 开启 Session
session_start();

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人资料 - AI靶场</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1><a href="dashboard.php" style="color: white; text-decoration: none;">AI靶场 控制板</a></h1>
            <div class="user-menu">
                <span>欢迎, <?php echo htmlspecialchars($username); ?></span>
                <a href="profile.php" class="btn-profile">个人资料</a>
                <a href="logout.php" class="btn-logout">退出登录</a>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <h2>个人资料</h2>
        <div class="profile-info">
            <p><strong>用户名:</strong> <?php echo htmlspecialchars($username); ?></p>
            <!-- 未来可以添加更多个人信息，如注册时间等 -->
        </div>
        <a href="dashboard.php" class="btn">返回控制板</a>
    </div>
</body>
</html>