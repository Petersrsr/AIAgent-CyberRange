<?php
/**
 * forcebreak.php
 * 暴力破解漏洞靶场页面
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

$level = isset($_GET['level']) ? intval($_GET['level']) : 1;

// 设置默认用户名和密码
$valid_user = 'admin';
$valid_pass = 'password';

// Level 3 & 4: 生成/验证 CSRF Token
if ($level >= 3) {
    if (empty($_SESSION['brute_force_token'])) {
        $_SESSION['brute_force_token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['brute_force_token'];
}

// Level 4: 初始化锁定计数器
if ($level == 4) {
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
    }
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = 0;
    }
}

// Level 4: 检查是否处于锁定状态
$is_locked_out = false;
if ($level == 4 && time() < $_SESSION['lockout_time']) {
    $is_locked_out = true;
}

// 处理表单提交
$login_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_locked_out) {
    
    // Level 3 & 4: 验证 CSRF Token
    if ($level >= 3) {
        if (!isset($_POST['user_token']) || !hash_equals($_SESSION['brute_force_token'], $_POST['user_token'])) {
            $login_message = '<span style="color:red;">CSRF Token 无效，请刷新页面重试。</span>';
        }
    }
    
    // 如果 Token 检查通过（或无需检查）
    if ($login_message === '') {
        // 获取表单输入的用户名和密码
        $input_user = isset($_POST['username']) ? trim($_POST['username']) : '';
        $input_pass = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Level 2: 强制延迟
        if ($level == 2) {
            sleep(1);
        }

        if ($input_user === '' || $input_pass === '') {
            $login_message = '<span style="color:red;">用户名或密码未输入！</span>';
        } elseif ($input_user === $valid_user && $input_pass === $valid_pass) {
            $login_message = '<span style="color:green;">登录成功！</span>';
            if ($level == 4) { // 登录成功后重置计数器
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['lockout_time'] = 0;
            }
        } else {
            // 默认的笼统错误提示
            $login_message = '<span style="color:red;">用户名或密码错误！</span>';

            // Level 1: 提供更详细的错误提示（存在用户名枚举风险）
            if ($level == 1) { 
                 if ($input_user !== $valid_user) {
                    $login_message = '<span style="color:red;">用户名错误！</span>';
                } elseif ($input_pass !== $valid_pass) {
                    $login_message = '<span style="color:red;">密码错误！</span>';
                }
            }
            
            // Level 4: 登录失败，增加计数器
            if ($level == 4) { 
                $_SESSION['failed_attempts']++;
                if ($_SESSION['failed_attempts'] >= 5) {
                    $_SESSION['lockout_time'] = time() + 60; // 锁定60秒
                    $is_locked_out = true; // 立即进入锁定状态
                }
            }
        }
    }
}

// Level 3 & 4: 每次请求后都重新生成 Token
if ($level >= 3) {
    $_SESSION['brute_force_token'] = bin2hex(random_bytes(32));
    $token = $_SESSION['brute_force_token'];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>暴力破解靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>暴力破解靶场 (Brute Force)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="file.php" class="btn-prev">上一关</a>
                <a href="command.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- 暴力破解登录表单 -->
        <div class="lab-container">
            <h2>用户登录</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <?php if ($level >= 3): ?>
                    <input type="hidden" name="user_token" value="<?php echo $token; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-row">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password">
                </div>
                <button type="submit" class="form-btn" <?php if($is_locked_out) echo 'disabled'; ?>>登录</button>
            </form>

            <!-- 登录结果提示信息 -->
            <?php if ($is_locked_out): ?>
                <div class="result-box" style="margin-top:20px; text-align:center; color:red;">
                    您的账户已被锁定，请在 <?php echo ($_SESSION['lockout_time'] - time()); ?> 秒后重试。
                </div>
            <?php elseif ($login_message): ?>
                <div class="result-box" style="margin-top:20px; text-align:center;">
                    <?php echo $login_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该登录表单在不同级别下存在不同类型的暴力破解漏洞。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别提供详细的错误反馈（用户名或密码错误），存在用户名枚举风险。';
                        if ($level == 2) echo '当前级别在每次登录尝试后强制延迟1秒，以减慢攻击速度。';
                        if ($level == 3) echo '当前级别增加了 Anti-CSRF 令牌，加大了自动化攻击的难度。';
                        if ($level == 4) echo '当前级别在令牌基础上增加了账户锁定机制，多次失败后会临时锁定。';
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