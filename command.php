<?php
/**
 * commandinjection.php
 * 命令注入靶场页面
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

// 命令注入漏洞核心逻辑
$command_result = '';
$command_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_command = isset($_POST['command']) ? trim($_POST['command']) : '';
    
    if ($input_command === '') {
        $command_message = '<span style="color:red;">命令未输入！</span>';
    } else {
        // 执行命令并获取结果
        $command_result = shell_exec($input_command);
        if ($command_result === null) {
            $command_result = '命令执行完成，但无输出结果。';
        }
        $command_message = '<span style="color:green;">命令执行成功！</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>命令注入靶场 - AI靶场</title>
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
        
        .command-result {
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
        
        .command-injection-advice {
            margin-top: 18px;
            color: #b36b00;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 14px;
            text-align: left;
        }
        
        .example-commands {
            margin-top: 15px;
            padding: 10px;
            background: #f0f8ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }
        
        .disclaimer {
            text-align: center;
            background: #fff2e8;
            color: #d48806;
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ffd591;
        }
        
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
        
        body {
            padding-bottom: 48px;
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>命令注入靶场(Command Injection)</h1>
            <div class="user-menu">
                <a href="dashboard.php" class="btn-home">返回首页</a>
                <a href="help.php" class="btn-help">帮助</a>
                <a href="forcebreak.php" class="btn-prev">上一关</a>
                <a href="csrf.php" class="btn-next">下一关</a>
            </div>
        </div>
    </div>

    <!-- 声明文字 -->
    <div class="disclaimer">
        声明：该技术只用于靶场练习
    </div>

    <!-- 命令注入表单 -->
    <div class="file-inclusion-container">
        <h2 style="text-align:center;">命令注入(Command Injection)</h2>
        <form method="post" autocomplete="off">
            <div class="file-form-row">
                <label for="command">系统命令：</label>
                <input type="text" id="command" name="command" 
                       placeholder="例如: ls, pwd, whoami" 
                       value="<?php echo isset($_POST['command']) ? htmlspecialchars($_POST['command']) : ''; ?>">
            </div>
            <button type="submit" class="file-form-btn">执行命令</button>
        </form>
        
        <!-- 操作结果提示信息 -->
        <div style="margin-top:15px;text-align:center;">
            <?php if ($command_message) echo $command_message; ?>
        </div>
        
        <!-- 示例命令列表 -->
        <div class="example-commands">
            <strong>可尝试的命令：</strong><br>
            • ls (列出目录内容)<br>
            • pwd (显示当前目录)<br>
            • whoami (显示当前用户)<br>
            • cat /etc/passwd (查看用户文件)<br>
            • uname -a (系统信息)
        </div>
        
        <!-- 命令执行结果显示 -->
        <?php if ($command_result): ?>
        <div class="command-result">
            <strong>执行结果：</strong><br>
            <?php echo htmlspecialchars($command_result); ?>
        </div>
        <?php endif; ?>
        
        <!-- 注入技巧提示 -->
        <div class="command-injection-advice">
            <h3>注入技巧提示：</h3>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>尝试使用 <code>;</code> 分隔多个命令</li>
                <li>使用 <code>&&</code> 或 <code>||</code> 连接命令</li>
                <li>尝试使用 <code>`</code> 或 <code>$()</code> 执行命令</li>
                <li>使用 <code>|</code> 管道符连接命令</li>
            </ol>
            <span style="color:#d48806;">请注意：实际环境中应严格验证命令输入，避免直接执行用户输入的命令。</span>
        </div>
    </div>

    <!-- 页脚 -->
    <div class="copyright-footer">
        版权归东海职业技术学院所有
    </div>
</body>
</html>