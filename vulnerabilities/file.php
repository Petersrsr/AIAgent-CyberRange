<?php
/**
 * file.php
 * 文件包含漏洞靶场页面
 */

// 开启 Session
session_start();

// 检查用户是否登录，如果未登录，则重定向到登录页面
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// ---- 靶场逻辑 ----
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
$file_to_include = 'includes/file1.php'; // 默认包含文件

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
    $page = $_GET['page'];

    // Level 1 (Low): 无防护
    if ($level == 1) {
        $file_to_include = $page;
    }
    
    // Level 2 (Medium): 简单过滤
    if ($level == 2) {
        $page = str_replace( array( "http://", "https://" ), "", $page );
		$page = str_replace( array( "../", "..\\" ), "", $page );
        $file_to_include = $page;
    }
    
    // Level 3 (High): 严格过滤
    if ($level == 3) {
        if (fnmatch("file*", $page)) {
             $file_to_include = $page;
        } else {
             $file_to_include = 'includes/file_does_not_exist.php'; // 故意给一个不存在的文件
        }
    }
    
    // Level 4 (Impossible): 白名单
    if ($level == 4) {
        if ($page == 'includes/file1.php' || $page == 'includes/file2.php' || $page == 'includes/file3.php') {
            $file_to_include = $page;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件包含漏洞靶场 - AI靶场</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../style.css">
    <style>
        .fi-links {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #f0f8ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }

        .fi-links a {
            margin: 0 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>文件包含漏洞靶场(File Inclusion)</h1>
            <div class="user-menu">
                <a href="../dashboard.php" class="btn-home">返回首页</a>
                <a href="../help.php" class="btn-help">帮助</a>
                <a href="csrf.php" class="btn-prev">上一关</a>
                <a href="forcebreak.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- 文件包含表单 -->
        <div class="lab-container">
            <h2>文件包含</h2>

            <!-- 难度选择器 -->
            <div class="level-selector">
                <a href="?level=1" class="<?php if($level == 1) echo 'active'; ?>">级别1：简单</a>
                <a href="?level=2" class="<?php if($level == 2) echo 'active'; ?>">级别2：中等</a>
                <a href="?level=3" class="<?php if($level == 3) echo 'active'; ?>">级别3：困难</a>
                <a href="?level=4" class="<?php if($level == 4) echo 'active'; ?>">级别4：不可能</a>
            </div>

            <!-- 可供点击的链接 -->
            <div class="fi-links">
                <a href="?page=includes/file1.php&level=<?php echo $level; ?>">文件1</a>
                <a href="?page=includes/file2.php&level=<?php echo $level; ?>">文件2</a>
                <a href="?page=includes/file3.php&level=<?php echo $level; ?>">文件3</a>
            </div>

            <!-- 包含的文件内容显示 -->
            <div class="result-box">
                <?php
                    if (isset($file_to_include) && file_exists($file_to_include)) {
                        include($file_to_include);
                    } else {
                        echo "<p>文件未找到!</p>";
                    }
                ?>
            </div>
            
            <!-- 注入技巧提示 -->
            <div class="advice-box">
                <h3>漏洞说明：</h3>
                <p>该功能允许用户查看不同文件，但可被用于包含未授权的文件。</p>
                <span style="color:#d48806;">
                    <?php
                        if ($level == 1) echo '当前级别无任何防护，可尝试目录遍历 (../) 或包含远程文件。';
                        if ($level == 2) echo '当前级别过滤了 `../` 和 `http://`，可尝试双写绕过或使用其他协议。';
                        if ($level == 3) echo '当前级别只允许包含以 `file` 开头的文件，需要寻找其他方法。';
                        if ($level == 4) echo '当前级别使用了白名单，无法包含其他文件。';
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