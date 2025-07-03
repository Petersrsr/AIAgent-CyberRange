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

            <div class="help-section">
                <h3>文件上传 (File Upload)</h3>
                <p>
                    <strong>这是什么？</strong> 文件上传漏洞是指应用程序未对上传的文件类型、内容或扩展名进行严格校验，导致攻击者可以上传恶意文件（如WebShell），进而控制服务器。<br>
                    <strong>练习目标：</strong> 在本靶场中，您需要尝试上传不同类型的文件，绕过文件类型、扩展名或内容的限制，最终实现上传并访问恶意脚本文件。
                </p>
            </div>

            <div class="help-section">
                <h3>不安全的验证码 (Insecure CAPTCHA)</h3>
                <p>
                    <strong>这是什么？</strong> 不安全的验证码是指验证码生成或校验存在逻辑漏洞，攻击者可以通过重放、预测、绕过等方式轻松通过验证。<br>
                    <strong>练习目标：</strong> 本靶场提供一个带有验证码的表单。您需要分析验证码的生成和校验机制，尝试绕过验证码保护，自动化提交表单。
                </p>
            </div>

            <div class="help-section">
                <h3>SQL注入 (SQL Injection)</h3>
                <p>
                    <strong>这是什么？</strong> SQL注入是一种常见的安全漏洞，攻击者通过在输入中注入恶意SQL语句，操纵数据库查询，获取、修改或删除数据。<br>
                    <strong>练习目标：</strong> 在本靶场中，您需要尝试构造特殊的输入，获取数据库中的敏感信息。不同难度下会有不同的过滤或防护措施，体验注入与防御的攻防过程。
                </p>
            </div>

            <div class="help-section">
                <h3>SQL盲注 (SQLi - Blind)</h3>
                <p>
                    <strong>这是什么？</strong> SQL盲注是一种特殊的SQL注入，页面不会直接返回查询结果或错误信息，攻击者只能通过页面的行为（如布尔值、延时）间接推断数据。<br>
                    <strong>练习目标：</strong> 本靶场模拟了无回显的注入环境。您需要利用布尔型或时间型盲注技巧，逐步推断出数据库中的敏感信息。
                </p>
            </div>

            <div class="help-section">
                <h3>反射型XSS (Reflected XSS)</h3>
                <p>
                    <strong>这是什么？</strong> 反射型XSS是指攻击者将恶意脚本注入到URL参数中，受害者点击链接后，脚本被反射到页面并执行。<br>
                    <strong>练习目标：</strong> 在本靶场中，您需要构造带有恶意脚本的URL，诱导用户访问，观察脚本是否被执行，并尝试绕过不同级别的过滤。
                </p>
            </div>

            <div class="help-section">
                <h3>存储型XSS (Stored XSS)</h3>
                <p>
                    <strong>这是什么？</strong> 存储型XSS是指恶意脚本被存储在服务器（如评论、文章等）中，所有访问该内容的用户都会被攻击。<br>
                    <strong>练习目标：</strong> 本靶场允许您提交内容到服务器。尝试注入脚本并刷新页面，观察脚本是否被执行，体验不同防护级别下的效果。
                </p>
            </div>

            <div class="help-section">
                <h3>DOM型XSS (DOM Based XSS)</h3>
                <p>
                    <strong>这是什么？</strong> DOM型XSS是指恶意脚本通过前端JavaScript操作DOM节点实现注入和执行，通常不与服务器交互。<br>
                    <strong>练习目标：</strong> 在本靶场中，您需要分析和利用前端JavaScript代码的漏洞，通过修改URL片段或参数，实现脚本注入和执行。
                </p>
            </div>

            <div class="help-section">
                <h3>弱会话ID (Weak Session IDs)</h3>
                <p>
                    <strong>这是什么？</strong> 弱会话ID是指服务器生成的会话标识符（Session ID）过于简单或可预测，攻击者可以通过猜测或暴力破解获取他人会话。<br>
                    <strong>练习目标：</strong> 本靶场演示了不同强度的会话ID生成方式。您可以尝试预测、伪造或暴力破解会话ID，体验会话劫持的过程。
                </p>
            </div>

            <div class="help-section">
                <h3>绕过内容安全策略 (CSP)</h3>
                <p>
                    <strong>这是什么？</strong> 内容安全策略（CSP）是一种Web安全机制，用于限制页面可加载的资源类型和来源，防止XSS等攻击。<br>
                    <strong>练习目标：</strong> 在本靶场中，您可以尝试注入不同类型的脚本，观察在不同CSP策略下，哪些脚本能够被执行，哪些被阻止，学习CSP的防护原理与绕过技巧。
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