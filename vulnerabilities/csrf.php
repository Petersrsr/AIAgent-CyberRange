<?php
/**
 * csrf.php
 * CSRF跨站请求伪造靶场页面
 */

// 开启 Session
session_start();

// 检查用户是否登录，如果未登录，则重定向到登录页面
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// ---- 靶场逻辑 ----
// 确保 db.php 的路径正确
if (isset($_GET['level']) && $_GET['level'] == 4) {
    require_once __DIR__ . '/../db.php';
}


$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
$csrf_message = '';

// Level 3 & 4: 生成/验证 CSRF Token
if ($level >= 3) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['csrf_token'];
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $can_process = false;

    // Level 1: 无任何防护
    if ($level == 1) {
        $can_process = true;
    }
    
    // Level 2: 验证 Referer
    if ($level == 2) {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            $host = $_SERVER['HTTP_HOST'];
            if (parse_url($referer, PHP_URL_HOST) == $host) {
                $can_process = true;
            } else {
                $csrf_message = '<span style="color:red;">Referer 验证失败，请求被拒绝！</span>';
            }
        } else {
            $csrf_message = '<span style="color:red;">缺少 Referer 头，请求被拒绝！</span>';
        }
    }
    
    // Level 3 & 4: 验证 CSRF Token
    if ($level >= 3) {
        if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
             $can_process = true;
        } else {
            $csrf_message = '<span style="color:red;">CSRF Token 验证失败，请求被拒绝！</span>';
            $can_process = false; // 确保即使通过了其他检查也被阻止
        }
    }
    
    // 开始处理密码修改逻辑
    if ($can_process) {
        $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

        // Level 4: 额外验证当前密码
        if ($level == 4) {
            $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
            if ($current_password === '') {
                 $csrf_message = '<span style="color:red;">请输入当前密码！</span>';
                 $can_process = false;
            } else {
                // 从数据库验证当前密码
                $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
                $stmt->bind_param("s", $_SESSION['username']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                
                if (!$user || md5($current_password) !== $user['password']) {
                    $csrf_message = '<span style="color:red;">当前密码错误！</span>';
                    $can_process = false;
                }
            }
        }
        
        // 如果所有检查都通过
        if($can_process) {
            if ($new_password === '') {
                $csrf_message = '<span style="color:red;">新密码未输入！</span>';
            } elseif ($confirm_password === '') {
                $csrf_message = '<span style="color:red;">确认密码未输入！</span>';
            } elseif ($new_password !== $confirm_password) {
                $csrf_message = '<span style="color:red;">两次密码输入不一致！</span>';
            } elseif (strlen($new_password) < 6) {
                $csrf_message = '<span style="color:red;">密码长度至少6位！</span>';
            } else {
                // 模拟密码修改成功 (在 Impossible 级别我们并未真正修改数据库，仅为演示)
                $csrf_message = '<span style="color:green;">密码修改成功！新密码：' . htmlspecialchars($new_password) . '</span>';
            }
        }
    }
    
    // Level 3 & 4: 每次请求后都重新生成 Token
    if ($level >= 3) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $token = $_SESSION['csrf_token'];
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF跨站请求伪造靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>CSRF靶场</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="command.php" class="btn-prev">上一关</a>
                <a href="file.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- CSRF 表单 -->
        <div class="lab-container">
            <h2>CSRF (跨站请求伪造)</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <?php if ($level >= 3): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                <?php endif; ?>
                
                <?php if ($level == 4): ?>
                <div class="form-row">
                    <label for="current_password">当前密码</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                <?php endif; ?>

                <div class="form-row">
                    <label for="new_password">新密码</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-row">
                    <label for="confirm_password">确认新密码</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <button type="submit" class="form-btn">修改密码</button>
            </form>

            <!-- 结果消息 -->
            <?php if ($csrf_message): ?>
            <div class="result-box" style="margin-top:20px; text-align:center;">
                <?php echo $csrf_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能模拟修改密码，不同级别下存在不同程度的CSRF漏洞。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，攻击者可轻易伪造请求。';
                        if ($level == 2) echo '当前级别验证了 Referer (请求来源)，但这可以被绕过。';
                        if ($level == 3) echo '当前级别使用了 CSRF Token (令牌)，但验证逻辑可能存在缺陷。';
                        if ($level == 4) echo '当前级别在 Token 基础上要求输入当前密码，无法被伪造。';
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