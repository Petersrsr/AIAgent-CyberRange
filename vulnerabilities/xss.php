<?php
/**
 * xss_stored.php
 * 存储型XSS靶场页面
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

// 如果是GET请求并且带有level参数，则重置POST数据（即切换难度时重置行为）
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['level'])) {
    // 清空POST数据，防止表单回显
    $_POST = [];
}

$xss_result = '';
$blocked_message = '';

// 数据库连接（修正用户名和密码为aibachang）
$mysqli = new mysqli("localhost", "aibachang", "aibachang", "aibachang");
if ($mysqli->connect_errno) {
    die("数据库连接失败: " . $mysqli->connect_error);
}

// 创建存储型XSS留言表（如不存在）
$mysqli->query("CREATE TABLE IF NOT EXISTS xss_stored_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 清空留言功能
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    $mysqli->query("TRUNCATE TABLE xss_stored_messages");
    header("Location: xss.php?level=" . $level);
    exit();
}

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
    $user_input = isset($_POST['message']) ? $_POST['message'] : '';
    $display_input = '';
    $filtered = null;
    if ($level == 1) {
        $filtered = $user_input;
    } elseif ($level == 2) {
        $filtered = sanitize_xss($user_input, 2);
    } elseif ($level == 3) {
        $filtered = sanitize_xss($user_input, 3);
        if ($filtered === null) {
            $xss_result = "<pre>" . $blocked_message . "</pre>";
        }
    } elseif ($level == 4) {
        $filtered = sanitize_xss($user_input, 4);
    }
    if ($filtered !== null && $filtered !== '' && $xss_result === '') {
        // 存储留言
        $stmt = $mysqli->prepare("INSERT INTO xss_stored_messages (username, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $filtered);
        $stmt->execute();
        $stmt->close();
        $xss_result = "<div>留言已提交！</div>";
    }
}

// 获取所有留言
$messages = [];
$res = $mysqli->query("SELECT username, content, created_at FROM xss_stored_messages ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }
    $res->free();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>存储型XSS靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .xss-echo { color: #d46b08; font-weight: bold; }
        .result-box { margin-top: 15px; font-size: 1.1em; }
        .message-list { margin-top: 20px; }
        .message-item { border-bottom: 1px solid #eee; padding: 8px 0; }
        .message-meta { color: #888; font-size: 0.95em; }
        .message-content { margin: 4px 0 0 0; }
        .clear-btn { 
            margin-left: 10px; 
            color: #111 !important; 
            background: none !important; 
            border: none; 
            padding: 4px 10px; 
            border-radius: 3px; 
            cursor: pointer; 
            font-size: 0.85em !important;
            box-shadow: none;
        }
        .clear-btn:hover { 
            background: #f5f5f5 !important; 
            color: #111 !important;
        }
    </style>
    <script>
        // 优化：点击难度切换时重置表单
        document.addEventListener('DOMContentLoaded', function() {
            var levelLinks = document.querySelectorAll('.level-selector a');
            levelLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // 跳转前清空表单（可选，实际跳转后表单会被重置）
                    var form = document.querySelector('form[method="post"]');
                    if (form) {
                        form.reset();
                    }
                });
            });
            // 清空留言确认
            var clearBtn = document.getElementById('clear-messages-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    if (!confirm('确定要清空所有留言吗？此操作不可恢复！')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>存储型XSS靶场(Stored XSS)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="Reflected.php" class="btn-prev">上一关</a>
                <a href="Dom.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- XSS表单 -->
        <div class="lab-container">
            <h2>留言板 (存储型XSS)
                <a href="?level=<?php echo $level; ?>&clear=1" id="clear-messages-btn" class="clear-btn" style="float:right;">清空留言</a>
            </h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>" onclick="event.preventDefault();window.location='?level=1';">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>" onclick="event.preventDefault();window.location='?level=2';">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>" onclick="event.preventDefault();window.location='?level=3';">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>" onclick="event.preventDefault();window.location='?level=4';">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="message">输入留言内容:</label>
                    <input type="text" id="message" name="message" value="<?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?>">
                </div>
                <button type="submit" class="form-btn">提交留言</button>
            </form>
            
            <!-- XSS回显结果显示 -->
            <?php if ($xss_result): ?>
            <div class="result-box">
                <?php echo $xss_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 留言列表 -->
            <div class="message-list">
                <h3>留言列表：</h3>
                <?php if (count($messages) === 0): ?>
                    <div>暂无留言，快来抢沙发！</div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item">
                            <div class="message-meta">
                                用户: <span class="xss-echo"><?php echo htmlspecialchars($msg['username']); ?></span>
                                &nbsp;|&nbsp;
                                时间: <?php echo $msg['created_at']; ?>
                            </div>
                            <div class="message-content">
                                <?php
                                // Level 1/2/3: 直接输出（1/2/3级别下有可能XSS），Level 4: 已转义
                                if ($level == 4) {
                                    echo $msg['content'];
                                } else {
                                    echo $msg['content'];
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能为一个留言板，存在存储型XSS漏洞，攻击者可提交恶意脚本，所有访问者都会被执行。</p>
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