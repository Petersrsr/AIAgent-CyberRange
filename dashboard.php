<?php
/**
 * dashboard.php
 * 用户登录后的控制板页面
 */

// 开启 Session
session_start();

// 检查用户是否登录，如果未登录，则重定向到登录页面
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
    <title>控制板 - AI靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>AI靶场 控制板</h1>
            <div class="user-menu">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['nickname'] ?: $_SESSION['username']); ?></span>
                <a href="profile.php" class="btn-profile">个人资料</a>
                <a href="logout.php" class="btn-logout">退出登录</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <h2>选择一个靶场开始挑战</h2>
        <div class="features-grid">
            <!-- 靶场功能卡片 -->
            <div class="feature-card">
                <h3>靶场功能一</h3>
                <p>这里是关于功能一的简要介绍。</p>
                <button class="btn" disabled>敬请期待</button>
            </div>

            <div class="feature-card">
                <h3>靶场功能二</h3>
                <p>这里是关于功能二的简要介绍。</p>
                <button class="btn" disabled>敬请期待</button>
            </div>

            <div class="feature-card">
                <h3>靶场功能三</h3>
                <p>这里是关于功能三的简要介绍。</p>
                <button class="btn" disabled>敬请期待</button>
            </div>
             <div class="feature-card">
                <h3>靶场功能四</h3>
                <p>这里是关于功能四的简要介绍。</p>
                <button class="btn" disabled>敬请期待</button>
            </div>
        </div>
    </div>
</body>
</html>