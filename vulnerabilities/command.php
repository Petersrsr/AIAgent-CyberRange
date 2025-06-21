<?php
/**
 * command.php
 * 命令注入靶场页面
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

$command_result = '';
$command_message = '';
$blocked_message = '';

// 安全过滤函数
function sanitize_command($input, $level) {
    global $blocked_message;
    
    // Level 2: 基础黑名单过滤
    if ($level == 2) {
        $blacklist = ['cat', 'rm', 'mv', 'cp', 'whoami', 'python', 'perl', 'ruby'];
        foreach ($blacklist as $cmd) {
            if (preg_match("/\b$cmd\b/i", $input)) {
                $blocked_message = "检测到不允许的命令: " . htmlspecialchars($cmd);
                return null; // 返回 null 表示命令被阻止
            }
        }
    }
    
    // Level 3: 严格过滤
    if ($level == 3) {
        $blacklist = [' ', ';', '|', '&', '`', '$', '(', ')', '{', '}', '<', '>'];
        foreach ($blacklist as $char) {
            if (strpos($input, $char) !== false) {
                $blocked_message = "检测到不允许的字符: " . htmlspecialchars($char);
                return null; // 返回 null 表示命令被阻止
            }
        }
    }
    
    // Level 1 或通过过滤的命令
    return $input;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_ip = isset($_POST['ip']) ? $_POST['ip'] : '';
    $command_to_run = '';

    // Level 1 (Low): 无防护
    if ($level == 1) {
        $command_to_run = "ping " . $target_ip;
    }
    
    // Level 2 (Medium): 移除 && 和 ;
    if ($level == 2) {
        $blacklist = array( '&&', ';' );
        $target_ip = str_replace( $blacklist, '', $target_ip );
        $command_to_run = "ping " . $target_ip;
    }
    
    // Level 3 (High): 移除更多特殊字符
    if ($level == 3) {
        $blacklist = array( '&', '&&', ';', '|', '-', '(', ')', '`', '||');
        $target_ip = str_replace( $blacklist, '', $target_ip );
        $command_to_run = "ping " . $target_ip;
    }
    
    // Level 4 (Impossible): 严格验证 + escapeshellarg
    if ($level == 4) {
        // 严格的 IP 地址格式验证
        if (filter_var($target_ip, FILTER_VALIDATE_IP)) {
            $command_to_run = "ping " . escapeshellarg($target_ip);
        } else {
            $command_result = "无效的 IP 地址格式。";
        }
    }

    // 执行命令
    if ($command_to_run) {
        // 根据操作系统设置编码
        $os = strtoupper(substr(PHP_OS, 0, 3));
        if ($os === 'WIN') {
            $command_result = shell_exec(iconv("UTF-8", "GBK", $command_to_run));
            $command_result = "<pre>" . iconv("GBK", "UTF-8", $command_result) . "</pre>";
        } else {
            $command_result = "<pre>" . shell_exec($command_to_run) . "</pre>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>命令注入靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>命令注入靶场(Command Injection)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="forcebreak.php" class="btn-prev">上一关</a>
                <a href="csrf.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- 命令注入表单 -->
        <div class="lab-container">
            <h2>Ping 工具</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" autocomplete="off">
                <div class="form-row">
                    <label for="ip">输入一个IP地址:</label>
                    <input type="text" id="ip" name="ip" value="<?php echo isset($_POST['ip']) ? htmlspecialchars($_POST['ip']) : ''; ?>">
                </div>
                <button type="submit" class="form-btn">执行</button>
            </form>
            
            <!-- 命令执行结果显示 -->
            <?php if ($command_result): ?>
            <div class="result-box">
                <?php echo $command_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能旨在提供一个 Ping 工具，但可被用于执行未授权的系统命令。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可使用 `&`, `&&`, `|`, `;` 等注入命令。';
                        if ($level == 2) echo '当前级别过滤了 `&&` 和 `;`，可尝试使用 `&` 或 `|`。';
                        if ($level == 3) echo '当前级别过滤了更多字符，可尝试使用换行符 `\n` (在某些系统上)。';
                        if ($level == 4) echo '当前级别对输入进行了严格验证和转义，无法注入。';
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