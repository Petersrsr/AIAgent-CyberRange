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
                <h3>暴力破解</h3>
                <p>暴力破解通过尝试所有可能的密码组合来破解密码的方法。</p>
                <a href="forcebreak.php" class="btn">进入靶场</a>
            </div>

            <div class="feature-card">
                <h3>命令注入</h3>
                <p>命令注入是一种通过注入恶意命令来执行非预期操作的方法。</p>
                <a href="command.php" class="btn">进入靶场</a>
            </div>

            <div class="feature-card">
                <h3>CSRF跨站请求伪造</h3>
                <p>csrf是一种通过伪造请求来执行非预期操作的方法。</p>
                <a href="csrf.php" class="btn">进入靶场</a>
            </div>
             <div class="feature-card">
                <h3>文件包含</h3>
                <p>文件包含是通过包含恶意文件来执行非预期操作的方法。</p>
                <a href="file.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>文件上传</h3>
                <p>文件上传是一种通过上传恶意文件来执行非预期操作的方法。</p>
                <a href="upload.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>不安全文件下载</h3>
                <p>不安全文件下载是通过下载恶意文件来执行非预期操作的方法。</p>
                <a href="download.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>SQL注入</h3>
                <p>SQL注入是通过注入恶意SQL语句来执行非预期操作的方法。</p>
                <a href="sql.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>宽字节注入</h3>
                <p>宽字节注入是通过注入恶意SQL语句来执行非预期操作的方法。</p>
                <a href="wide.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>XSS跨站脚本攻击</h3>
                <p>XSS跨站脚本攻击是通过注入恶意脚本执行非预期操作的方法。</p>
                <a href="xss.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>XXE注入</h3>
                <p>XXE注入是通过注入恶意XML数据执行非预期操作的方法。</p>
                <a href="xxe.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>敏感信息泄露</h3>
                <p>敏感信息泄露是通过泄露敏感信息来执行非预期操作的方法。</p>
                <a href="sensitive.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>绕过内容安全策略</h3>
                <p>此内容是通过绕过内容安全策略来执行非预期操作的方法。</p>
                <a href="csp.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>URL重定向</h3>
                <p>URL重定向是通过重定向到恶意URL来执行非预期操作的方法。</p>
                <a href="redirect.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>SSRF</h3>
                <p>SSRF是通过SSRF来执行非预期操作的方法。</p>
                <a href="ssrf.php" class="btn">进入靶场</a>
            </div>
            <div class="feature-card">
                <h3>OVER PERMISSION</h3>
                <p>越权操作是通过绕过权限来执行非预期操作的方法。</p>
                <a href="over.php" class="btn">进入靶场</a>
            </div>
             <div class="feature-card">
                <h3>目录遍历</h3>
                <p>目录遍历是通过遍历目录来执行非预期操作的方法。</p>
                <a href="traversal.php" class="btn">进入靶场</a>
            </div> 
            
            
            
        </div>
    </div>
</body>
</html>