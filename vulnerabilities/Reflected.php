<?php
/**
 * xss.php
 * 反射型XSS靶场页面
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

$xss_result = '';
$blocked_message = '';

function sanitize_xss($input, $level) {
    global $blocked_message;
    // Level 2: 基础HTML标签过滤
    if ($level == 2) {
        // 移除script、img、svg等常见标签
        $input = preg_replace('/<(script|img|svg|iframe|object|embed|form|input|style|link|base|meta)[^>]*?>.*?<\/\1>/is', '', $input);
        $input = preg_replace('/<(script|img|svg|iframe|object|embed|form|input|style|link|base|meta)[^>]*?>/is', '', $input);
        // 移除on*事件
        $input = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        return $input;
    }
    // Level 3: 仅允许字母数字和部分标点
    if ($level == 3) {
        if (!preg_match('/^[a-zA-Z0-9\s\.\,\!\?]+$/', $input)) {
            $blocked_message = "只允许字母、数字和常见标点。";
            return null;
        }
        return $input;
    }
    // Level 4: 完全转义
    if ($level == 4) {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    // Level 1: 无防护
    return $input;
}

$user_input = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user = $_SESSION['username'] ?? 'guest';
    $user_input = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    $display_input = '';
    if ($level == 1) {
        // 无防护，直接输出
        $display_input = $user_input;
    } elseif ($level == 2) {
        $display_input = sanitize_xss($user_input, 2);
    } elseif ($level == 3) {
        $filtered = sanitize_xss($user_input, 3);
        if ($filtered !== null) {
            $display_input = $filtered;
        } else {
            $xss_result = "<pre>" . $blocked_message . "</pre>";
        }
    } elseif ($level == 4) {
        $display_input = sanitize_xss($user_input, 4);
    }
    if ($display_input !== '' && $xss_result === '') {
        $xss_result = "<div>你搜索的关键词：<span class='xss-echo'>{$display_input}</span></div>";
    }
    // 这里假设有变量 $reflected_result 表示操作结果
    $result = (isset($reflected_result) && strpos($reflected_result, '成功') !== false) ? 'success' : 'fail';
    log_action($current_user, 'reflected_xss', '反射型XSS操作', $result);

    $error_count = 0;
    if (isset($reflected_message) && strpos($reflected_message, '成功') === false) {
        $error_count = 1;
    }
    require_once __DIR__.'/../db.php';
    $user = $_SESSION['username'];
    $challenge = 'reflected';
    $level_str = isset($level) ? (string)$level : 'easy';
    $completed_at = date('Y-m-d H:i:s');
    $time_used = 0;
    $stmt = $conn->prepare("INSERT INTO challenge_records (user, challenge, level, completed_at, time_used, error_count) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssii', $user, $challenge, $level_str, $completed_at, $time_used, $error_count);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>反射型XSS靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .xss-echo { color: #d46b08; font-weight: bold; }
        .result-box { margin-top: 15px; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>反射型XSS靶场(Reflected XSS)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-dark">返回首页</a>
                <a href="../help.php" class="btn-dark">帮助</a>
                <a href="blind.php" class="btn-dark">上一关</a>
                <a href="xss.php" class="btn-dark">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- XSS表单 -->
        <div class="lab-container">
            <h2>搜索功能 (反射型XSS)</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="keyword">输入搜索关键词:</label>
                    <input type="text" id="keyword" name="keyword" value="<?php echo isset($_POST['keyword']) ? htmlspecialchars($_POST['keyword']) : ''; ?>">
                </div>
                <button type="submit" class="form-btn">搜索</button>
            </form>
            
            <!-- XSS回显结果显示 -->
            <?php if ($xss_result): ?>
            <div class="result-box">
                <?php echo $xss_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能旨在提供一个搜索工具，但存在反射型XSS漏洞，可被用于执行恶意脚本。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可直接注入如 <code>&lt;script&gt;alert(1)&lt;/script&gt;</code>、<code>&lt;img src=1 onerror=alert(1)&gt;</code> 等。';
                        if ($level == 2) echo '当前级别过滤了部分危险标签和事件，可尝试大小写混淆、svg、data uri等绕过。';
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