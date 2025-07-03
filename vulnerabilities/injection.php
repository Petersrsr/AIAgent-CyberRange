<?php
/**
 * sqli.php
 * SQL注入靶场页面
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

$sqli_result = '';
$sqli_message = '';
$blocked_message = '';

// 数据库连接（修正用户名和密码为aibachang）
$mysqli = new mysqli("localhost", "aibachang", "aibachang", "aibachang");
if ($mysqli->connect_errno) {
    die("数据库连接失败: " . $mysqli->connect_error);
}

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
    $rows = [];

    // Level 1 (Low): 无防护
    if ($level == 1) {
        $query = "SELECT id, username, email FROM users WHERE id = '$userid'";
        $result = $mysqli->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        $sqli_result = "<pre>SQL: " . htmlspecialchars($query) . "</pre>";
    }

    // Level 2 (Medium): 黑名单过滤
    if ($level == 2) {
        $userid = sanitize_sql($userid, 2);
        if ($userid !== null) {
            $query = "SELECT id, username, email FROM users WHERE id = '$userid'";
            $result = $mysqli->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $result->free();
            }
            $sqli_result = "<pre>SQL: " . htmlspecialchars($query) . "</pre>";
        } else {
            $sqli_result = "<pre>" . $blocked_message . "</pre>";
        }
    }

    // Level 3 (High): 仅允许数字
    if ($level == 3) {
        $userid = sanitize_sql($userid, 3);
        if ($userid !== null) {
            $query = "SELECT id, username, email FROM users WHERE id = $userid";
            $result = $mysqli->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $result->free();
            }
            $sqli_result = "<pre>SQL: " . htmlspecialchars($query) . "</pre>";
        } else {
            $sqli_result = "<pre>" . $blocked_message . "</pre>";
        }
    }

    // Level 4 (Impossible): 预处理语句
    if ($level == 4) {
        if (preg_match('/^\d+$/', $userid)) {
            $stmt = $mysqli->prepare("SELECT id, username, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            $stmt->close();
            $sqli_result = "<pre>SQL: SELECT id, username, email FROM users WHERE id = ?</pre>";
        } else {
            $sqli_result = "<pre>无效的用户ID格式。</pre>";
        }
    }

    // 展示结果
    if (!empty($rows)) {
        $sqli_result .= "<table class='result-table'><tr><th>ID</th><th>用户名</th><th>Email</th></tr>";
        foreach ($rows as $row) {
            $sqli_result .= "<tr><td>" . htmlspecialchars($row['id']) . "</td><td>" . htmlspecialchars($row['username']) . "</td><td>" . htmlspecialchars($row['email']) . "</td></tr>";
        }
        $sqli_result .= "</table>";
    } elseif (empty($sqli_result)) {
        $sqli_result = "<pre>未查询到结果。</pre>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL注入靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .result-table { border-collapse: collapse; width: 100%; margin-top: 10px;}
        .result-table th, .result-table td { border: 1px solid #ccc; padding: 6px 10px; }
        .result-table th { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>SQL注入靶场(SQL Injection)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="insecure.php" class="btn-prev">上一关</a>
                <a href="blind.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- SQL注入表单 -->
        <div class="lab-container">
            <h2>用户信息查询</h2>
            
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
            <?php if ($sqli_result): ?>
            <div class="result-box">
                <?php echo $sqli_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能旨在提供一个用户信息查询工具，但可被用于SQL注入攻击，获取敏感数据。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可直接注入如 `1 or 1=1`、`1\' or \'1\'=\'1` 等。';
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