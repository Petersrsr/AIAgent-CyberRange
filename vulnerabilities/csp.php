<?php
/**
 * csp.php
 * CSP靶场页面
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

// ---- 靶场逻辑 ----

// 默认难度级别
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;

$csp_result = '';
$csp_message = '';
$blocked_message = '';

// 不同级别的CSP策略
function get_csp_header($level) {
    switch ($level) {
        case 1: // Level 1: 无CSP
            return "";
        case 2: // Level 2: 允许内联脚本
            return "Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline';";
        case 3: // Level 3: 禁止内联脚本
            return "Content-Security-Policy: default-src 'self'; script-src 'self';";
        case 4: // Level 4: 严格CSP
            return "Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'none';";
        default:
            return "";
    }
}

// 设置CSP头
$csp_header = get_csp_header($level);
if ($csp_header) {
    header($csp_header);
}

// 处理用户输入
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $custom_script = isset($_POST['custom_script']) ? $_POST['custom_script'] : '';
    $csp_result = $custom_script;
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSP靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .result-box { margin-top: 15px; padding: 10px; background: #f6f6f6; border: 1px solid #eee; }
        .advice-box { margin-top: 20px; background: #fffbe6; border: 1px solid #ffe58f; padding: 12px 18px; }
        .level-selector a.active { background: #1890ff; color: #fff; }
        .custom-script-area { width: 100%; min-height: 60px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>CSP靶场(Content Security Policy)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="weak.php" class="btn-prev">上一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- CSP表单 -->
        <div class="lab-container">
            <h2>自定义脚本注入测试</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：无CSP</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：允许内联</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：禁止内联</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：严格CSP</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="custom_script">输入你想注入的HTML/JS代码:</label>
                    <textarea id="custom_script" name="custom_script" class="custom-script-area" placeholder="如：&lt;script&gt;alert(1)&lt;/script&gt;"><?php echo isset($_POST['custom_script']) ? htmlspecialchars($_POST['custom_script']) : ''; ?></textarea>
                </div>
                <button type="submit" class="form-btn">提交测试</button>
            </form>
            
            <!-- 注入结果显示 -->
            <?php if ($csp_result): ?>
            <div class="result-box">
                <strong>页面渲染结果：</strong>
                <div style="border:1px dashed #aaa; margin-top:8px; padding:8px; background:#fff;">
                    <?php echo $csp_result; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 漏洞说明 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>本页面用于演示不同CSP策略下，XSS攻击的可行性。你可以尝试注入不同类型的脚本，观察CSP的防护效果。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无CSP，任何脚本都可执行。';
                        if ($level == 2) echo '允许内联脚本，外部脚本受限。';
                        if ($level == 3) echo '禁止内联脚本，仅允许同源外部脚本。';
                        if ($level == 4) echo '严格CSP，禁止一切非授权脚本和对象资源。';
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