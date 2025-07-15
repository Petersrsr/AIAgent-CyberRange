<?php
/**
 * blind.php
 * SQL盲注靶场页面
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

$blind_result = '';
$blocked_message = '';

// 数据库连接（修正用户名和密码为aibachang）
require_once __DIR__.'/../db.php';

// 安全过滤函数
function sanitize_sql($input, $level) {
    global $blocked_message;
    // Level 2: 基础黑名单过滤
    if ($level == 2) {
        $blacklist = ['\'', '"', '--', '#', '/*', '*/', ';', 'or', 'and'];
        foreach ($blacklist as $item) {
            if (stripos($input, $item) !== false) {
                $blocked_message = "检测到不允许的字符或关键字: " . htmlspecialchars($item);
                return null;
            }
        }
    }
    // Level 3: 仅允许数字
    if ($level == 3) {
        if (!preg_match('/^\d+$/', $input)) {
            $blocked_message = "只允许数字类型输入。";
            return null;
        }
    }
    // Level 4: 使用预处理语句
    // 过滤在主逻辑中实现
    return $input;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = isset($_POST['userid']) ? $_POST['userid'] : '';
    $query = '';
    $found = false;
    $error_count = 0;

    // Level 1 (Low): 无防护
    if ($level == 1) {
        $query = "SELECT id FROM users WHERE id = '$userid'";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            $found = true;
        }
        if ($result) $result->free();
        $blind_result = $found ? "<span style='color:green;'>存在该用户！</span>" : "<span style='color:red;'>不存在该用户！</span>";
        $blind_result .= "<pre>SQL: " . htmlspecialchars($query) . "</pre>";
    }

    // Level 2 (Medium): 黑名单过滤
    if ($level == 2) {
        $userid = sanitize_sql($userid, 2);
        if ($userid !== null) {
            $query = "SELECT id FROM users WHERE id = '$userid'";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $found = true;
            }
            if ($result) $result->free();
            $blind_result = $found ? "<span style='color:green;'>存在该用户！</span>" : "<span style='color:red;'>不存在该用户！</span>";
            $blind_result .= "<pre>SQL: " . htmlspecialchars($query) . "</pre>";
        } else {
            $blind_result = "<pre>" . $blocked_message . "</pre>";
        }
    }

    // Level 3 (High): 仅允许数字
    if ($level == 3) {
        $userid = sanitize_sql($userid, 3);
        if ($userid !== null) {
            $query = "SELECT id FROM users WHERE id = $userid";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $found = true;
            }
            if ($result) $result->free();
            $blind_result = $found ? "<span style='color:green;'>存在该用户！</span>" : "<span style='color:red;'>不存在该用户！</span>";
            $blind_result .= "<pre>SQL: " . htmlspecialchars($query) . "</pre>";
        } else {
            $blind_result = "<pre>" . $blocked_message . "</pre>";
        }
    }

    // Level 4 (Impossible): 预处理语句
    if ($level == 4) {
        if (preg_match('/^\d+$/', $userid)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $found = true;
            }
            if ($res) $res->free();
            $stmt->close();
            $blind_result = $found ? "<span style='color:green;'>存在该用户！</span>" : "<span style='color:red;'>不存在该用户！</span>";
            $blind_result .= "<pre>SQL: SELECT id FROM users WHERE id = ?</pre>";
        } else {
            $blind_result = "<pre>无效的用户ID格式。</pre>";
        }
    }

    if (isset($blind_result) && strpos($blind_result, '成功') === false) {
        $error_count = 1;
    }
    require_once __DIR__.'/../db.php';
    $user = $_SESSION['username'];
    $challenge = 'blind';
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
    <title>SQL盲注靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .result-box { margin-top: 15px; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>SQL盲注靶场(SQL Blind Injection)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-dark">返回首页</a>
                <a href="../help.php" class="btn-dark">帮助</a>
                <a href="injection.php" class="btn-dark">上一关</a>
                <a href="Reflected.php" class="btn-dark">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- SQL盲注表单 -->
        <div class="lab-container">
            <h2>用户ID存在性查询</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="userid">输入用户ID:</label>
                    <input type="text" id="userid" name="userid" value="<?php echo isset($_POST['userid']) ? htmlspecialchars($_POST['userid']) : ''; ?>">
                </div>
                <button type="submit" class="form-btn">查询</button>
            </form>
            
            <!-- 查询结果显示 -->
            <?php if ($blind_result): ?>
            <div class="result-box">
                <?php echo $blind_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>本关为SQL盲注靶场，仅返回“存在/不存在”提示，不显示具体数据。请尝试通过布尔型盲注等方式获取敏感信息。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可直接注入如 `1 or 1=1`、`1\' and length(username)=5 --+` 等。';
                        if ($level == 2) echo '当前级别过滤了部分危险字符和关键字，可尝试大小写混淆、注释绕过等。';
                        if ($level == 3) echo '当前级别仅允许数字类型输入，尝试类型转换绕过。';
                        if ($level == 4) echo '当前级别使用了预处理语句，无法注入。';
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