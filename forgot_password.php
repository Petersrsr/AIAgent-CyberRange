<?php
/**
 * forgot_password.php
 * 忘记密码页面，启动密码重置流程
 */

session_start();
require_once 'db.php'; // 引入数据库连接

$message = '';
$error = '';

// 如果用户已登录，则重定向到控制板
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];

    if (empty($username)) {
        $error = "请输入您的用户名。";
    } else {
        // 检查数据库中是否存在该用户
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // 用户存在，设置成功消息
            // TODO: 此处应集成邮件发送功能
            $message = "密码重置链接已发送（模拟），请检查您的邮箱。";
        } else {
            // 用户不存在，明确提示
            $error = "该用户不存在。";
        }
        $stmt->close();
    }
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>忘记密码 - 东海学院网络靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>重置密码 - 东海学院网络靶场</h2>
        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">请输入您的用户名。我们将向您的注册邮箱发送一个链接来重置密码。</p>

        <?php if ($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="forgot_password.php" method="post">
            <div class="input-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <button type="submit" class="btn">发送重置链接</button>
            </div>
            <div class="register-link">
                <a href="login.php">返回登录</a>
            </div>
        </form>
    </div>

    <footer class="site-footer">
        <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
        <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
    </footer>
</body>
</html> 