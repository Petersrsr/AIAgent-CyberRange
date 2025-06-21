<?php
/**
 * csrf.php
 * CSRF跨站请求伪造靶场页面
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

// CSRF页面核心逻辑
// 这里我们模拟一个简单的密码修改功能

// 处理表单提交
// 让提示信息只在POST时显示，刷新(GET)时自动清空
$csrf_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单输入的新密码
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if ($new_password === '') {
        $csrf_message = '<span style="color:red;">新密码未输入！</span>';
    } elseif ($confirm_password === '') {
        $csrf_message = '<span style="color:red;">确认密码未输入！</span>';
    } elseif ($new_password !== $confirm_password) {
        $csrf_message = '<span style="color:red;">两次密码输入不一致！</span>';
    } elseif (strlen($new_password) < 6) {
        $csrf_message = '<span style="color:red;">密码长度至少6位！</span>';
    } else {
        // 模拟密码修改成功
        $csrf_message = '<span style="color:green;">密码修改成功！新密码：' . htmlspecialchars($new_password) . '</span>';
    }
}
// 如果是GET请求，$csrf_message 保持为空，页面刷新后不会显示上次的提示
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF跨站请求伪造靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .csrf-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ccc;
        }
        
        .csrf-form-row {
            margin-bottom: 15px;
        }
        
        .csrf-form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .csrf-form-row input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .csrf-form-btn {
            background: #1890ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .csrf-form-btn:hover {
            background: #40a9ff;
        }
        
        .csrf-advice {
            margin-top: 18px;
            color: #b36b00;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 14px;
            text-align: left;
        }
        
        /* ===============================
           声明文字样式 - 居中显示
        =============================== */
        .disclaimer {
            text-align: center;
            background: #fff2e8;
            color: #d48806;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ffd591;
        }
        
        /* ===============================
           页脚样式 - 固定底部
        =============================== */
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
        
        /* 为了避免底部声明遮挡内容，给body加padding-bottom */
        body {
            padding-bottom: 48px;
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>CSRF跨站请求伪造靶场(CSRF)</h1>
            <div class="user-menu">
                <a href="dashboard.php" class="btn-home">返回首页</a>
                <a href="help.php" class="btn-help">帮助</a>
                <a href="command.php" class="btn-prev">上一关</a>
                <a href="file.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <!-- 声明文字 -->
    <div class="disclaimer">
        声明：该技术只用于靶场练习
    </div>

    <!-- CSRF密码修改表单 -->
    <div class="csrf-container">
        <h2 style="text-align:center;">修改密码(CSRF)</h2>
        <form method="post" autocomplete="off">
            <div class="csrf-form-row">
                <label for="new_password">新密码：</label>
                <input type="password" id="new_password" name="new_password" placeholder="请输入新密码" required>
            </div>
            <div class="csrf-form-row">
                <label for="confirm_password">确认密码：</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="请再次输入新密码" required>
            </div>
            <button type="submit" class="csrf-form-btn">修改密码</button>
        </form>
        
        <!-- 修改结果提示信息 -->
        <div style="margin-top:15px;text-align:center;">
            <?php if ($csrf_message) echo $csrf_message; ?>
        </div>
        
        <!-- CSRF说明信息 -->
        <div class="csrf-advice">
            <b>CSRF攻击说明：</b>跨站请求伪造（CSRF）是一种攻击方式，攻击者诱导已认证用户执行非预期的操作。<br>
            <span style="color:#d48806;">请注意：实际环境中应使用CSRF Token、SameSite Cookie等防护措施。</span>
        </div>
    </div>

    <!-- 页脚 -->
    <div class="copyright-footer">
        版权归东海职业技术学院所有
    </div>
</body>
</html>