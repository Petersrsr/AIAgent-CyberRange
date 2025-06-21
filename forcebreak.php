<?php
/**
 * forcebreak.php
 * 暴力破解漏洞靶场页面
 */

// 开启 Session
session_start();

// 检查用户是否登录，如果未登录，则重定向到登录页面
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 获取当前登录用户名
$username = $_SESSION['username'];

// 设置默认用户名和密码（演示用，实际应从数据库获取）
$valid_user = 'admin';
$valid_pass = 'password';

// 处理表单提交
$login_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单输入的用户名和密码
    $input_user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $input_pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($input_user === '') {
        $login_message = '<span style="color:red;">用户名未输入！</span>';
    } elseif ($input_pass === '') {
        $login_message = '<span style="color:red;">密码未输入！</span>';
    } elseif ($input_user !== $valid_user) {
        $login_message = '<span style="color:red;">用户名错误！</span>';
    } elseif ($input_pass !== $valid_pass) {
        $login_message = '<span style="color:red;">密码错误！</span>';
    } else {
        $login_message = '<span style="color:green;">登录成功！</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>暴力破解靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .file-inclusion-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ccc;
        }
        
        .file-form-row {
            margin-bottom: 15px;
        }
        
        .file-form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .file-form-row input[type="text"],
        .file-form-row input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .file-form-btn {
            background: #1890ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .file-form-btn:hover {
            background: #40a9ff;
        }
        
        .default-credentials {
            margin-top: 15px;
            padding: 10px;
            background: #f0f8ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            text-align: center;
        }
        
        .default-credentials span {
            margin: 0 10px;
            font-size: 14px;
        }
        
        .file-inclusion-advice {
            margin-top: 18px;
            color: #b36b00;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 14px;
            text-align: left;
        }
        
        .disclaimer {
            text-align: center;
            background: #fff2e8;
            color: #d48806;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ffd591;
        }
        
        .copyright-footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background: #f0f2f5;
            text-align: center;
            color: #222;
            font-size: 12px;
            margin: 0;
            padding: 10px 0 8px 0;
            letter-spacing: 1px;
            z-index: 100;
            border-top: 1px solid #e0e0e0;
        }
        
        body {
            padding-bottom: 48px;
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>暴力破解靶场 (Brute Force)</h1>
            <div class="user-menu">
                <a href="dashboard.php" class="btn-home">返回首页</a>
                <a href="help.php" class="btn-help">帮助</a>
                <a href="command.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <!-- 声明文字 -->
    <div class="disclaimer">
        声明：该技术只用于靶场练习
    </div>

    <!-- 暴力破解表单 -->
    <div class="file-inclusion-container">
        <h2 style="text-align:center;">暴力破解登录 (Brute Force)</h2>
        <form method="post" autocomplete="off">
            <div class="file-form-row">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" 
                       placeholder="请输入用户名" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="file-form-row">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password" 
                       placeholder="请输入密码">
            </div>
            <button type="submit" class="file-form-btn">登录</button>
        </form>
        
        <!-- 操作结果提示信息 -->
        <div style="margin-top:15px;text-align:center;">
            <?php if ($login_message) echo $login_message; ?>
        </div>
        
        <!-- 默认账号信息 -->
        <div class="default-credentials">
            <span>默认用户名: <strong>admin</strong></span>
            <span>默认密码: <strong>password</strong></span>
        </div>
        
        <!-- 漏洞说明 -->
        <div class="file-inclusion-advice">
            <strong>建议：</strong>暴力破解（Brute Force）是一种常见的攻击方式，通过不断尝试用户名和密码组合来获取账户访问权限。<br>
            <span style="color:#d48806;">请注意：实际环境中应采取措施防止暴力破解，如验证码、账户锁定、IP限制等。</span>
        </div>
    </div>

    <!-- 页脚 -->
    <div class="copyright-footer">
        版权归东海职业技术学院所有
    </div>
</body>
</html>
