<?php
/**
 * register.php
 * 处理用户注册请求，并显示注册表单
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
$success_message = '';

// 判断是否是 POST 请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = '所有字段都不能为空';
    } elseif (!ctype_alnum($username)) {
        $error_message = '用户名只能包含字母和数字';
    } elseif (strlen($password) < 6 || strlen($password) > 20) {
        $error_message = '密码长度必须在6到20位之间';
    } elseif ($password !== $confirm_password) {
        $error_message = '两次输入的密码不一致';
    } else {
        // 检查用户名是否已存在
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = '该用户名已被注册';
        } else {
            // 用户名可用，插入新用户
            $hashed_password = md5($password);
            $insert_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ss", $username, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $success_message = '注册成功！现在您可以 <a href="login.php">登录</a>。';
            } else {
                $error_message = '注册失败，请稍后再试';
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 东海学院网络靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>注册 - 东海学院网络靶场</h2>
        <form action="register.php" method="post">
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="username">用户名 (字母和数字)</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="input-group">
                <label for="password">密码 (6-20位)</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">确认密码</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">注册</button>
            <div class="register-link">
                <p>已有账户？ <a href="login.php">返回登录</a></p>
            </div>
        </form>
    </div>
</body>
</html>