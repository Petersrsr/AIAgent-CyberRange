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
    <title>控制板 - 东海学院网络靶场</title>
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

    <div class="dashboard-container">
        <h2>选择一个靶场开始挑战</h2>
        <div class="features-grid">
            <!-- 靶场功能卡片 -->
            <div class="feature-card">
                <h3>暴力破解 (Brute Force)</h3>
                <p>模拟一个登录框，学习如何使用自动化工具，通过不断尝试来破解用户密码。</p>
                <a href="vulnerabilities/forcebreak.php" class="btn">进入靶场</a>
            </div>

            <div class="feature-card">
                <h3>命令注入 (Command Injection)</h3>
                <p>一个模拟Ping功能的工具，学习如何利用输入验证不当，执行未授权的系统命令。</p>
                <a href="vulnerabilities/command.php" class="btn">进入靶场</a>
            </div>

            <div class="feature-card">
                <h3>跨站请求伪造 (CSRF)</h3>
                <p>一个模拟修改密码的页面，学习攻击者如何诱导用户在不知情的情况下执行操作。</p>
                <a href="vulnerabilities/csrf.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>文件包含 (File Inclusion)</h3>
                <p>一个文件查看功能，学习如何利用此功能读取甚至执行服务器上的未授权文件。</p>
                <a href="vulnerabilities/file.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>文件上传 (File Upload)</h3>
                <p>一个文件上传功能，学习如何绕过验证上传WebShell，从而控制服务器。</p>
                <a href="vulnerabilities/upload.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>不安全的验证码 (Insecure CAPTCHA)</h3>
                <p>一个存在逻辑漏洞的验证码功能，学习如何绕过或滥用验证码保护机制。</p>
                <a href="vulnerabilities/insecure.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>SQL注入 (SQL Injection)</h3>
                <p>最经典的注入漏洞，学习如何通过构造SQL查询来获取、修改或删除数据库中的数据。</p>
                <a href="vulnerabilities/injection.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>SQL盲注 (SQLi - Blind)</h3>
                <p>在页面没有明确错误回显的情况下，学习如何利用布尔逻辑或时间延迟来推断数据。</p>
                <a href="vulnerabilities/blind.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>反射型XSS (Reflected XSS)</h3>
                <p>恶意脚本被注入到URL中，当用户点击链接时，脚本在浏览器中执行。</p>
                <a href="vulnerabilities/Reflected.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>存储型XSS (Stored XSS)</h3>
                <p>恶意脚本被存储在服务器上（如文章或评论中），所有访问该页面的用户都会受到攻击。</p>
                <a href="vulnerabilities/xss.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>DOM型XSS (DOM Based XSS)</h3>
                <p>一种更为高级的XSS，在不与服务器交互的情况下，通过修改DOM结构触发攻击。</p>
                <a href="vulnerabilities/Dom.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>弱会话ID (Weak Session IDs)</h3>
                <p>学习服务器生成的会话ID的规律，并尝试预测有效的会话ID来劫持其他用户。</p>
                <a href="vulnerabilities/weak.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>绕过内容安全策略 (CSP)</h3>
                <p>此内容是通过绕过内容安全策略来执行非预期操作的方法。</p>
                <a href="vulnerabilities/csp.php" class="btn">进入靶场</a>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
        <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
    </footer>
</body>
</html>