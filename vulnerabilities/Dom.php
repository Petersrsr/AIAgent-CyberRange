<?php
/**
 * dom_xss.php
 * Dom型XSS靶场页面
 */

// 开启 Session
session_start();

// 检查用户是否登录，如果未登录，则重定向到登录页面
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// 获取当前登录用户名
$username = $_SESSION['username'];

// 默认难度级别
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;

// 如果是GET请求并且带有level参数，则重置POST数据（即切换难度时重置行为）
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['level'])) {
    $_POST = [];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dom型XSS靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .xss-echo { color: #d46b08; font-weight: bold; }
        .result-box { margin-top: 15px; font-size: 1.1em; }
        .advice-box { margin-top: 30px; }
        .form-row { margin-bottom: 12px; }
    </style>
    <script>
        // DOM型XSS核心逻辑
        function getQueryVariable(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                if(pair[0] == variable){return decodeURIComponent(pair[1] || '');}
            }
            return "";
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 难度切换
            var levelLinks = document.querySelectorAll('.level-selector a');
            levelLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var form = document.querySelector('form[method="get"]');
                    if (form) form.reset();
                });
            });

            // DOM型XSS回显
            var level = <?php echo json_encode($level); ?>;
            var domResult = document.getElementById('dom-xss-result');
            var msg = getQueryVariable('message');
            if (msg && domResult) {
                if (level == 1) {
                    // 直接innerHTML
                    domResult.innerHTML = msg;
                } else if (level == 2) {
                    // 尝试移除script标签
                    domResult.innerHTML = msg.replace(/<script.*?>.*?<\/script>/gi, '');
                } else if (level == 3) {
                    // 仅允许字母数字和常见标点
                    if (/^[a-zA-Z0-9\s\.\,\!\?]+$/.test(msg)) {
                        domResult.innerHTML = msg;
                    } else {
                        domResult.innerHTML = "<span style='color:red;'>只允许字母、数字和常见标点。</span>";
                    }
                } else if (level == 4) {
                    // 完全转义
                    domResult.textContent = msg;
                }
            }
        });
    </script>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Dom型XSS靶场(DOM XSS)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="xss.php" class="btn-prev">上一关</a>
                <a href="Weak.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="lab-container">
            <h2>留言板 (Dom型XSS)</h2>
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            <form method="get" action="" autocomplete="off">
                <input type="hidden" name="level" value="<?php echo $level; ?>">
                <div class="form-row">
                    <label for="message">输入留言内容:</label>
                    <input type="text" id="message" name="message" value="<?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : ''; ?>">
                </div>
                <button type="submit" class="form-btn">提交留言</button>
            </form>
            <!-- DOM型XSS回显区域 -->
            <div class="result-box" id="dom-xss-result"></div>
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能为一个留言板，存在DOM型XSS漏洞，攻击者可构造恶意URL，诱导用户访问后执行任意脚本。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可直接构造如 <code>?message=&lt;img src=1 onerror=alert(1)&gt;</code>、<code>?message=&lt;script&gt;alert(1)&lt;/script&gt;</code> 等。';
                        if ($level == 2) echo '当前级别过滤了部分危险标签，可尝试事件属性、svg等绕过。';
                        if ($level == 3) echo '当前级别仅允许字母数字和常见标点，尝试编码绕过。';
                        if ($level == 4) echo '当前级别对输入进行了严格转义，无法注入。';
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
    <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
</footer>
</body>
</html> 