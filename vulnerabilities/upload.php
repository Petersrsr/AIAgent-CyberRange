<?php
/**
 * fileupload.php
 * 文件上传靶场页面
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

$upload_result = '';
$blocked_message = '';

// 安全过滤函数
function sanitize_upload($filename, $level) {
    global $blocked_message;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    // Level 2: 黑名单过滤
    if ($level == 2) {
        $blacklist = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'js', 'jsp', 'asp', 'aspx'];
        if (in_array($ext, $blacklist)) {
            $blocked_message = "检测到不允许的文件类型: " . htmlspecialchars($ext);
            return false;
        }
    }
    // Level 3: 仅允许图片类型
    if ($level == 3) {
        $whitelist = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        if (!in_array($ext, $whitelist)) {
            $blocked_message = "只允许上传图片类型文件。";
            return false;
        }
    }
    // Level 4: 严格MIME类型+后缀白名单
    if ($level == 4) {
        $whitelist = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $mime_whitelist = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp'
        ];
        if (!in_array($ext, $whitelist)) {
            $blocked_message = "只允许上传图片类型文件。";
            return false;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $mime_whitelist)) {
            $blocked_message = "文件MIME类型不被允许。";
            return false;
        }
    }
    return true;
}

require_once __DIR__.'/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user = $_SESSION['username'] ?? 'guest';
    $filename = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : '';
    $result = '';
    $error_count = 0;
    if ($filename === '') {
        $result = 'fail';
        log_action($current_user, 'file_upload', '未选择文件', $result);
    } else {
        $file = $_FILES['file'];
        $tmp_name = $file['tmp_name'];
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $can_upload = false;

        // Level 1 (Low): 无防护
        if ($level == 1) {
            $can_upload = true;
        }
        // Level 2 (Medium): 黑名单过滤
        if ($level == 2) {
            $can_upload = sanitize_upload($filename, 2);
            if (!$can_upload) $upload_result = $blocked_message;
        }
        // Level 3 (High): 仅允许图片类型
        if ($level == 3) {
            $can_upload = sanitize_upload($filename, 3);
            if (!$can_upload) $upload_result = $blocked_message;
        }
        // Level 4 (Impossible): 严格MIME类型+后缀白名单
        if ($level == 4) {
            $can_upload = sanitize_upload($filename, 4);
            if (!$can_upload) $upload_result = $blocked_message;
        }

        if ($can_upload) {
            $target = $upload_dir . basename($filename);
            if (move_uploaded_file($tmp_name, $target)) {
                $upload_result = "<pre>文件 " . htmlspecialchars($filename) . " 上传成功！</pre>";
                $result = 'success';
            } else {
                $upload_result = "<pre>文件上传失败！</pre>";
                $result = 'fail';
            }
        } else {
            $upload_result = "<pre>文件上传失败！</pre>";
            $result = 'fail';
        }
        log_action($current_user, 'file_upload', '上传文件: ' . $filename, $result);
    }
    require_once __DIR__.'/../db.php';
    $user = $_SESSION['username'];
    $challenge = 'upload';
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
    <title>文件上传靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1>文件上传靶场(File Upload)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-dark">返回首页</a>
                <a href="../help.php" class="btn-dark">帮助</a>
                <a href="file.php" class="btn-dark">上一关</a>
                <a href="insecure.php" class="btn-dark">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- 文件上传表单 -->
        <div class="lab-container">
            <h2>文件上传工具</h2>
            
            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>
            
            <form method="post" action="?level=<?php echo $level; ?>" enctype="multipart/form-data" autocomplete="off">
                <div class="form-row">
                    <label for="file">选择要上传的文件:</label>
                    <input type="file" id="file" name="file">
                </div>
                <button type="submit" class="form-btn">上传</button>
            </form>
            
            <!-- 文件上传结果显示 -->
            <?php if ($upload_result): ?>
            <div class="result-box">
                <?php echo $upload_result; ?>
            </div>
            <?php endif; ?>
            
            <!-- 漏洞说明 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能旨在提供一个文件上传工具，但可被用于上传恶意文件，导致服务器被攻击。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可上传任意类型文件。';
                        if ($level == 2) echo '当前级别过滤了部分危险后缀，可尝试使用双写绕过等技巧。';
                        if ($level == 3) echo '当前级别仅允许图片类型文件，可尝试伪造图片文件头。';
                        if ($level == 4) echo '当前级别对文件类型和MIME类型进行了严格验证，无法上传恶意文件。';
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