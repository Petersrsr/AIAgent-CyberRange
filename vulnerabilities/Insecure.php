<?php
/**
 * insecure_captcha.php
 * Insecure CAPTCHA 靶场页面
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

$captcha_result = '';
$captcha_message = '';
$captcha_code = '';

// 生成简单的验证码
function generate_captcha($level) {
    if ($level == 1) {
        // Level 1: 固定验证码
        return '1234';
    } elseif ($level == 2) {
        // Level 2: 简单数字验证码
        return strval(rand(1000, 9999));
    } elseif ($level == 3) {
        // Level 3: 字母+数字验证码
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 4; $i++) {
            $code .= $chars[rand(0, strlen($chars)-1)];
        }
        return $code;
    } else {
        // Level 4: 图形验证码（这里只做文本模拟）
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[rand(0, strlen($chars)-1)];
        }
        return $code;
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = isset($_POST['captcha_input']) ? trim($_POST['captcha_input']) : '';
    $real_captcha = isset($_SESSION['captcha_code']) ? $_SESSION['captcha_code'] : '';

    if ($level == 1) {
        // Level 1: 固定验证码，前端可直接看到
        if ($user_input === $real_captcha) {
            $captcha_result = '<span style="color:green;">验证成功！</span>';
        } else {
            $captcha_result = '<span style="color:red;">验证码错误！</span>';
        }
    } elseif ($level == 2) {
        // Level 2: 简单数字验证码，验证码保存在 session
        if ($user_input === $real_captcha) {
            $captcha_result = '<span style="color:green;">验证成功！</span>';
        } else {
            $captcha_result = '<span style="color:red;">验证码错误！</span>';
        }
    } elseif ($level == 3) {
        // Level 3: 字母+数字验证码，验证码保存在 session
        if (strcasecmp($user_input, $real_captcha) == 0) {
            $captcha_result = '<span style="color:green;">验证成功！</span>';
        } else {
            $captcha_result = '<span style="color:red;">验证码错误！</span>';
        }
    } else {
        // Level 4: 图形验证码（这里只做文本模拟），验证码保存在 session
        if (strcasecmp($user_input, $real_captcha) == 0) {
            $captcha_result = '<span style="color:green;">验证成功！</span>';
        } else {
            $captcha_result = '<span style="color:red;">验证码错误！</span>';
        }
    }
    // 每次提交后都重新生成验证码
    $_SESSION['captcha_code'] = generate_captcha($level);
} else {
    // 首次访问或GET，生成验证码
    $_SESSION['captcha_code'] = generate_captcha($level);
}
$captcha_code = $_SESSION['captcha_code'];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insecure CAPTCHA 靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Insecure CAPTCHA 靶场</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="upload.php" class="btn-prev">上一关</a>
                <a href="injection.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- CAPTCHA 表单 -->
        <div class="lab-container">
            <h2>验证码验证</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="captcha_input">请输入验证码:</label>
                    <?php if ($level == 1): ?>
                        <input type="text" id="captcha_input" name="captcha_input" placeholder="请输入验证码">
                        <span style="margin-left:10px;color:#888;">验证码: <b><?php echo $captcha_code; ?></b></span>
                    <?php elseif ($level == 2): ?>
                        <input type="text" id="captcha_input" name="captcha_input" placeholder="请输入验证码">
                        <span style="margin-left:10px;color:#888;">验证码: <b><?php echo $captcha_code; ?></b></span>
                    <?php elseif ($level == 3): ?>
                        <input type="text" id="captcha_input" name="captcha_input" placeholder="请输入验证码">
                        <span style="margin-left:10px;color:#888;">验证码: <b><?php echo $captcha_code; ?></b></span>
                    <?php else: ?>
                        <input type="text" id="captcha_input" name="captcha_input" placeholder="请输入验证码">
                        <span style="margin-left:10px;color:#888;">验证码: <b style="letter-spacing:2px;"><?php echo $captcha_code; ?></b> <span style="font-size:12px;">(模拟图形验证码)</span></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="form-btn">验证</button>
            </form>
            
            <!-- 验证结果显示 -->
            <?php if ($captcha_result): ?>
            <div class="result-box">
                <?php echo $captcha_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 漏洞说明 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能旨在提供一个验证码验证工具，但由于验证码设计不安全，容易被绕过或暴力破解。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别验证码固定为1234，前端可直接看到。';
                        if ($level == 2) echo '当前级别验证码为简单数字，前端可直接看到。';
                        if ($level == 3) echo '当前级别验证码为字母+数字，前端可直接看到。';
                        if ($level == 4) echo '当前级别为模拟图形验证码，但验证码仍然明文显示，易被绕过。';
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