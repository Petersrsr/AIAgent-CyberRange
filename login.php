<?php
/**
 * login.php
 * 处理用户登录请求，并显示登录表单
 */

// 开启 Session
session_start();

// 如果用户已登录，直接跳转到控制板页面
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

// 引入数据库连接文件
require_once 'db.php';

$error_message = '';

// 判断是否是 POST 请求（即用户提交了表单）
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取用户输入的用户名和密码
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 检查输入是否为空
    if (empty($username) || empty($password)) {
        $error_message = '用户名和密码不能为空';
    } else {
        // 对密码进行 MD5 加密
        $hashed_password = md5($password);

        // 使用预处理语句 (Prepared Statement) 来防止 SQL 注入，更安全
        $sql = "SELECT id, username, nickname FROM users WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param("ss", $username, $hashed_password);
        
        $stmt->execute();
        
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // 登录成功
            $user = $result->fetch_assoc();
            
            // 将用户名和昵称存储到 Session 中
            $_SESSION['username'] = $user['username'];
            $_SESSION['nickname'] = $user['nickname'];
            
            // 重定向到控制板页面
            header("Location: dashboard.php");
            exit();
        } else {
            // 登录失败
            $error_message = '用户名或密码错误';
        }

        $stmt->close();
    }
}

// 关闭数据库连接 (此行将被移除)
// $conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 东海学院网络靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>登录 - 东海学院网络靶场</h2>
        <form action="login.php" method="post">
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <button type="submit" class="btn">登录</button>
            </div>
            <div class="register-link">
                <a href="forgot_password.php">忘记密码?</a> | <a href="register.php">注册新账户</a>
            </div>
        </form>
    </div>

    <footer class="site-footer">
        <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
        <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
    </footer>
</body>
</html>