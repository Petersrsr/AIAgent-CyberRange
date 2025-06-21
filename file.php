<?php
/**
 * file_inclusion.php
 * 文件包含漏洞靶场页面
 */

// 开启 Session
session_start();

// 检查用户是否登录，如果未登录，则重定向到登录页面
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 获取当前登录用户名
$username = $_SESSION['username'];

// 文件包含漏洞核心逻辑
$file_message = '';
$included_content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_path = isset($_POST['file_path']) ? trim($_POST['file_path']) : '';
    
    if ($file_path === '') {
        $file_message = '<span style="color:red;">请输入文件路径！</span>';
    } else {
        // 模拟文件包含漏洞 - 直接包含用户输入的文件路径
        // 注意：这是故意制造的安全漏洞，仅用于学习目的
        $file_path = $_POST['file_path'];
        
        // 检查文件是否存在
        if (file_exists($file_path)) {
            ob_start();
            include($file_path);
            $included_content = ob_get_clean();
            $file_message = '<span style="color:green;">文件包含成功！</span>';
        } else {
            $file_message = '<span style="color:red;">文件不存在或无法访问！</span>';
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
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .file-inclusion-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ccc;
        }
        
        .file-form-row {
            margin-bottom: 15px;
        }
        
        .file-form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .file-form-row input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .file-form-btn {
            background: #1890ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .file-form-btn:hover {
            background: #40a9ff;
        }
        
        .included-content {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .file-inclusion-advice {
            margin-top: 18px;
            color: #b36b00;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 14px;
            text-align: left;
        }
        
        .example-files {
            margin-top: 15px;
            padding: 10px;
            background: #f0f8ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }
        
        /* ===============================
           声明文字样式 - 居中显示
        =============================== */
        .disclaimer {
            text-align: center;
            background: #fff2e8;
            color: #d48806;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ffd591;
        }
        
        /* ===============================
           页脚样式 - 固定底部
        =============================== */
        .copyright-footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background: #f0f2f5;
            text-align: center;
            color: #222;
            font-size: 12px;
            margin: 0;
            padding: 10px 0 8px 0;
            letter-spacing: 1px;
            z-index: 100;
            border-top: 1px solid #e0e0e0;
        }
        
        /* 为了避免底部声明遮挡内容，给body加padding-bottom */
        body {
            padding-bottom: 48px;
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>文件包含漏洞靶场(File Inclusion)</h1>
            <div class="user-menu">
                <a href="dashboard.php" class="btn-home">返回首页</a>
                <a href="help.php" class="btn-help">帮助</a>
                <a href="csrf.php" class="btn-prev">上一关</a>
                <a href="a.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <!-- 声明文字 -->
    <div class="disclaimer">
        声明：该技术只用于靶场练习
    </div>

    <!-- 文件包含表单 -->
    <div class="file-inclusion-container">
        <h2 style="text-align:center;">文件包含漏洞(File Inclusion)</h2>
        <form method="post" autocomplete="off">
            <div class="file-form-row">
                <label for="file_path">文件路径：</label>
                <input type="text" id="file_path" name="file_path" 
                       placeholder="例如: /etc/passwd 或 ../../config.php" 
                       value="<?php echo isset($_POST['file_path']) ? htmlspecialchars($_POST['file_path']) : ''; ?>">
            </div>
            <button type="submit" class="file-form-btn">包含文件</button>
        </form>
        
        <!-- 操作结果提示信息 -->
        <div style="margin-top:15px;text-align:center;">
            <?php if ($file_message) echo $file_message; ?>
        </div>
        
        <!-- 示例文件列表 -->
        <div class="example-files">
            <strong>可尝试的文件路径：</strong><br>
            • /etc/passwd (Linux系统用户文件)<br>
            • /etc/hosts (主机文件)<br>
            • ../../config.php (相对路径)<br>
            • /proc/version (系统版本信息)<br>
            • /var/log/apache2/access.log (Apache访问日志)
        </div>
        
        <!-- 包含的文件内容显示 -->
        <?php if ($included_content): ?>
        <div class="included-content">
            <strong>文件内容：</strong><br>
            <?php echo htmlspecialchars($included_content); ?>
        </div>
        <?php endif; ?>
        
        <!-- 漏洞说明 -->
        <div class="file-inclusion-advice">
            <b>漏洞说明：</b>文件包含漏洞允许攻击者包含服务器上的任意文件，可能导致敏感信息泄露、代码执行等安全问题。<br>
            <span style="color:#d48806;">请注意：实际环境中应严格验证文件路径，避免直接包含用户输入的文件路径。</span>
        </div>
    </div>

    <!-- 页脚 -->
    <div class="copyright-footer">
        版权归东海职业技术学院所有
    </div>
</body>
</html>