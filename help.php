<?php
/**
 * help.php
 * 帮助中心页面，提供各个靶场的说明
 */
session_start();

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>帮助中心 - 东海学院网络靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .help-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .help-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .help-section h3 {
            color: #007bff;
            margin-top: 0;
        }
        .help-section p {
            color: #444;
            line-height: 1.8;
        }
    </style>
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
        <div class="profile-card">
            <h2>帮助中心</h2>
            
            <div class="help-section">
                <h3>暴力破解 (Brute Force)</h3>
                <p>
                    <strong>这是什么？</strong> 暴力破解是一种密码破解方法，通过系统地、穷举地尝试所有可能的密码组合来获取访问权限。<br>
                    <strong>练习目标：</strong> 在本靶场中，您将尝试对一个登录表单进行自动化攻击，以找出正确的用户名和密码。不同难度级别会引入不同的防御机制，如延迟或令牌。
                </p>
            </div>

            <div class="help-section">
                <h3>命令注入 (Command Injection)</h3>
                <p>
                    <strong>这是什么？</strong> 命令注入是一种安全漏洞，允许攻击者在Web服务器上执行任意的操作系统命令。这通常发生在应用程序将用户输入直接传递给系统shell时。<br>
                    <strong>练习目标：</strong> 本靶场模拟一个 `ping` 工具。您需要尝试绕过输入过滤，执行除了 `ping` 以外的其他系统命令。
                </p>
            </div>

            <div class="help-section">
                <h3>跨站请求伪造 (CSRF)</h3>
                <p>
                    <strong>这是什么？</strong> CSRF 是一种迫使已登录的用户在不知情的情况下，执行非本意的操作的攻击。攻击者可以诱导用户点击一个链接，从而以用户的名义执行如修改密码、转账等操作。<br>
                    <strong>练习目标：</strong> 本靶场提供一个修改密码的表单。您需要理解并尝试在没有有效防御的情况下，如何构造一个恶意请求来利用此漏洞。
                </p>
            </div>

            <div class="help-section">
                <h3>文件包含 (File Inclusion)</h3>
                <p>
                    <strong>这是什么？</strong> 文件包含漏洞允许攻击者在服务器上包含并执行或显示文件。这可以用于读取敏感文件（如配置文件），甚至在特定条件下执行远程代码。<br>
                    <strong>练习目标：</strong> 您需要通过操纵URL中的 `page` 参数，尝试包含服务器上的其他文件，以理解该漏洞的原理和不同级别下的过滤绕过方法。
                </p>
            </div>

            <div class="form-actions" style="margin-top: 25px;">
                <a href="dashboard.php" class="btn-cancel">返回控制板</a>
            </div>

        </div>
    </div>

    <footer class="site-footer">
        <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
        <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
    </footer>
</body>
</html> 