<?php
/**
 * weak_session.php
 * 弱会话ID靶场页面
 */

// ---- 靶场逻辑 ----

// 默认难度级别
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;

// 生成弱会话ID的函数
function generate_weak_session_id($level) {
    if ($level == 1) {
        // Level 1: 纯数字，长度6
        return strval(rand(100000, 999999));
    } elseif ($level == 2) {
        // Level 2: 数字+小写字母，长度8
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $sid = '';
        for ($i = 0; $i < 8; $i++) {
            $sid .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $sid;
    } elseif ($level == 3) {
        // Level 3: 数字+大小写字母，长度12
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $sid = '';
        for ($i = 0; $i < 12; $i++) {
            $sid .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $sid;
    } elseif ($level == 4) {
        // Level 4: 使用session_create_id()或random_bytes，长度32
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(16));
        } else {
            return session_create_id();
        }
    }
    // 默认
    return strval(rand(100000, 999999));
}

// 处理登录表单
$login_result = '';
$session_id = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    if ($username !== '') {
        $session_id = generate_weak_session_id($level);
        // 伪造登录成功
        $login_result = "<pre>登录成功！分配的会话ID: <span style='color:blue'>" . htmlspecialchars($session_id) . "</span></pre>";
        // 设置cookie
        setcookie("WEAKSESSID", $session_id, time() + 3600, "/");
    } else {
        $login_result = "<pre style='color:red'>请输入用户名！</pre>";
    }
}

// 检查当前cookie
$current_sid = isset($_COOKIE['WEAKSESSID']) ? $_COOKIE['WEAKSESSID'] : '';

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>弱会话ID靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .result-box { margin-top: 15px; font-size: 1.1em; }
        .advice-box { margin-top: 30px; }
        .form-row { margin-bottom: 12px; }
        .sid-box { margin-top: 10px; background: #f6ffed; border: 1px solid #b7eb8f; padding: 10px; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>弱会话ID靶场(Weak Session IDs)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="Dom.php" class="btn-prev">上一关</a>
                <a href="csp.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- 弱会话ID表单 -->
        <div class="lab-container">
            <h2>模拟登录 - 会话ID分配</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：极弱</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：弱</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：较强</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：安全</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="username">输入用户名:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <button type="submit" class="form-btn">登录</button>
            </form>
            
            <!-- 登录结果显示 -->
            <?php if ($login_result): ?>
            <div class="result-box">
                <?php echo $login_result; ?>
            </div>
            <?php endif; ?>

            <!-- 当前会话ID显示 -->
            <?php if ($current_sid): ?>
            <div class="sid-box">
                <strong>你当前的会话ID(WEAKSESSID):</strong>
                <span style="color:green"><?php echo htmlspecialchars($current_sid); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- 漏洞说明 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>本页面用于演示不同强度的会话ID分配方式。弱会话ID容易被猜测或暴力破解，导致会话劫持。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别会话ID为6位纯数字，极易被猜测。';
                        if ($level == 2) echo '当前级别会话ID为8位数字+小写字母，仍然容易被暴力破解。';
                        if ($level == 3) echo '当前级别会话ID为12位数字+大小写字母，安全性提升但仍不够强。';
                        if ($level == 4) echo '当前级别会话ID为32位高强度随机字符串，安全性较高。';
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