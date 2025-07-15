<?php
$__start = microtime(true);
session_start();
// 管理员认证
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        if ($user === 'admin' && $pass === '123456') {
            $_SESSION['username'] = 'admin';
            header('Location: log_analysis.php');
            exit;
        } else {
            $error = '账号或密码错误';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>管理员登录 - 日志分析</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body { background: #f7f8fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .login-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 24px #0002;
                max-width: 370px;
                margin: 48px auto;
                padding: 38px 36px 32px 36px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .login-card h2 {
                font-size: 1.7em;
                font-weight: bold;
                margin-bottom: 18px;
                letter-spacing: 1px;
                text-align: center;
            }
            .login-card form {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .login-card label {
                font-size: 1em;
                color: #222;
                margin-bottom: 2px;
            }
            .login-card input[type="text"], .login-card input[type="password"] {
                width: 100%;
                padding: 9px 12px;
                border: 1px solid #d0d7de;
                border-radius: 6px;
                font-size: 1em;
                background: #f8fafc;
                margin-top: 4px;
                box-sizing: border-box;
            }
            .login-card button[type="submit"] {
                background: #2196f3;
                color: #fff;
                border: none;
                border-radius: 6px;
                padding: 10px 0;
                font-size: 1.1em;
                font-weight: bold;
                cursor: pointer;
                margin-top: 6px;
                transition: background 0.2s;
            }
            .login-card button[type="submit"]:hover {
                background: #1976d2;
            }
            .login-card .login-error {
                color: #f44336;
                margin-bottom: 10px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>管理员登录</h2>
            <?php if (!empty($error)) echo '<div class="login-error">' . htmlspecialchars($error) . '</div>'; ?>
            <form method="post" autocomplete="off">
                <label>账号
                    <input type="text" name="username" required autofocus autocomplete="username">
                </label>
                <label>密码
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button type="submit">登录</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$log_file = __DIR__ . '/user_actions.log';
$logs = [];
if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_slice($lines, -1000); // 只保留最后1000行
    foreach ($lines as $line) {
        if (preg_match('/^\[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\]$/', $line, $m)) {
            $logs[] = [
                'time' => $m[1],
                'user' => $m[2],
                'action' => $m[3],
                'detail' => $m[4],
                'result' => $m[5],
                'ip' => $m[6],
            ];
        }
    }
}

// 先收集所有用户
$user_list = [];
foreach ($logs as $log) {
    $user_list[$log['user']] = true;
}
$user_list = array_keys($user_list);
sort($user_list);
$selected_user = $_GET['stat_user'] ?? ($user_list[0] ?? '');

// 筛选参数
$filter_user = $_GET['user'] ?? '';
$filter_action = $_GET['action'] ?? '';
$filter_result = $_GET['result'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$page_size = 10;

// 优化：单次遍历日志，完成筛选、统计、用户列表、图表数据收集
$filtered_logs = [];
$stat_action = [];
$stat_result = [];
$trend = [];
$user_action_stat = [];
foreach ($logs as $log) {
    // 筛选
    $match = true;
    if ($filter_user && stripos($log['user'], $filter_user) === false) $match = false;
    if ($filter_action && stripos($log['action'], $filter_action) === false) $match = false;
    if ($filter_result && stripos($log['result'], $filter_result) === false) $match = false;
    if ($search) {
        $found = false;
        foreach ($log as $v) {
            if (stripos($v, $search) !== false) {
                $found = true;
                break;
            }
        }
        if (!$found) $match = false;
    }
    if ($match) {
        $filtered_logs[] = $log;
        // 统计
        $stat_action[$log['action']] = ($stat_action[$log['action']] ?? 0) + 1;
        $stat_result[$log['result']] = ($stat_result[$log['result']] ?? 0) + 1;
        $date = substr($log['time'], 0, 10);
        $trend[$date] = ($trend[$date] ?? 0) + 1;
    }
    // 单用户靶场分布统计（不受筛选影响，直接统计所有日志）
    if ($selected_user && $log['user'] === $selected_user) {
        $user_action_stat[$log['action']] = ($user_action_stat[$log['action']] ?? 0) + 1;
    }
}

// 统计卡片数据
$total_logs = count($filtered_logs);
$success_logs = count(array_filter($filtered_logs, function($l){ return isset($l['result']) && $l['result'] === 'success'; }));
$fail_logs = count(array_filter($filtered_logs, function($l){ return isset($l['result']) && $l['result'] === 'fail'; }));
$unique_users = count($user_list);

// 操作类型统计
$action_stat = $stat_action;
// 按时间趋势统计（按天）
$trend = $trend;
ksort($trend);

// 获取所有用户列表用于下拉选择
$user_list = $user_list;
// 统计选中用户的操作类型分布
$user_action_stat = $user_action_stat;
$user_action_labels = json_encode(array_values(array_filter(array_keys($user_action_stat), 'strlen')), JSON_UNESCAPED_UNICODE);
$user_action_data = json_encode(array_values($user_action_stat));
// 靶场类型颜色映射
$action_colors = [
    'injection' => '#f44336', // SQL注入
    'forcebreak' => '#ff9800', // 暴力破解
    'file_upload' => '#ffc107', // 恶意软件/文件上传
    'xss' => '#2196f3', // XSS跨站
    'other' => '#9e9e9e', // 其他
    // 可继续补充
];
$user_action_colors = [];
foreach (array_keys($user_action_stat) as $act) {
    $user_action_colors[] = $action_colors[$act] ?? '#9e9e9e';
}
$user_action_colors_json = json_encode($user_action_colors);

// 统计在线人数（假设以活跃用户数为在线人数）
$online_users = $unique_users;
// 靶场稳定状况（示例：根据日志中fail比例判断）
$total_ops = $total_logs;
$fail_ops = $fail_logs;
$stable_percent = $total_ops > 0 ? round(100 - ($fail_ops / $total_ops) * 100, 1) : 100;
$stable_status = $stable_percent > 95 ? 'Stable' : ($stable_percent > 80 ? 'Warning' : 'Unstable');
$stable_color = $stable_percent > 95 ? '#4caf50' : ($stable_percent > 80 ? '#ff9800' : '#f44336');

// 导出Excel
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="user_logs.xls"');
    echo "时间\t用户名\t操作类型\t操作详情\t结果\tIP\n";
    foreach ($filtered_logs as $log) {
        echo implode("\t", array_map('htmlspecialchars', $log)) . "\n";
    }
    exit;
}

// 处理删除日志请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logs'])) {
    $delete_user = trim($_POST['delete_user'] ?? '');
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new_lines = [];
    foreach ($lines as $line) {
        if ($delete_user) {
            // 只保留不是该用户的日志
            if (!preg_match('/^\[.*?\] \['.preg_quote($delete_user, '/').'\] /', $line)) {
                $new_lines[] = $line;
            }
        } else {
            // 删除全部日志
            // 什么都不做
        }
    }
    if ($delete_user) {
        file_put_contents($log_file, implode(PHP_EOL, $new_lines) . (count($new_lines) ? PHP_EOL : ''));
    } else {
        file_put_contents($log_file, '');
    }
    header('Location: log_analysis.php');
    exit;
}

// 分页
$total = count($filtered_logs);
$total_pages = max(1, ceil($total / $page_size));
$show_logs = array_slice(array_values($filtered_logs), ($page-1)*$page_size, $page_size);

function highlight($text, $keyword) {
    if (!$keyword) return htmlspecialchars($text);
    return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span style="background:yellow">$1</span>', htmlspecialchars($text));
}

// 生成前端用的统计数据
$action_labels = json_encode(array_keys($action_stat), JSON_UNESCAPED_UNICODE);
$action_data = json_encode(array_values($action_stat));
$trend_labels = json_encode(array_keys($trend));
$trend_data = json_encode(array_values($trend));
// 右下角用户靶场分布数据，改为折线图数据
$user_action_line_labels = json_encode(array_keys($user_action_stat), JSON_UNESCAPED_UNICODE);
$user_action_line_data = json_encode(array_values($user_action_stat));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>日志分析仪表盘</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #e8eaf6 0%, #f3e5f5 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', Arial, sans-serif;
        }
        .dashboard-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 0 32px 0;
        }
        .dashboard-row {
            display: flex;
            gap: 28px;
            margin-bottom: 28px;
        }
        .stat-card {
            flex: 1;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px #0001;
            padding: 28px 28px 18px 28px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 0;
            position: relative;
        }
        .stat-card .stat-icon {
            font-size: 2.1em;
            margin-bottom: 8px;
        }
        .stat-card .stat-main {
            font-size: 2.8em;
            font-weight: bold;
            letter-spacing: 2px;
            color: #222;
            margin-bottom: 2px;
        }
        .stat-card .stat-unit {
            font-size: 0.5em;
            color: #aaa;
            margin-left: 2px;
        }
        .stat-card .stat-label {
            font-size: 1.1em;
            color: #888;
            margin-bottom: 12px;
        }
        .stat-card .stat-info-bar {
            width: 100%;
            display: flex;
            gap: 12px;
            font-size: 0.98em;
            color: #fff;
            margin-top: auto;
            border-radius: 0 0 12px 12px;
            padding: 8px 0 0 0;
        }
        .stat-blue { border-top: 4px solid #3f51b5; }
        .stat-blue .stat-info-bar { background: #3f51b5; }
        .stat-red { border-top: 4px solid #e53935; }
        .stat-red .stat-info-bar { background: #e53935; }
        .stat-green { border-top: 4px solid #43a047; }
        .stat-green .stat-info-bar { background: #43a047; }
        .stat-purple { border-top: 4px solid #8e24aa; }
        .stat-purple .stat-info-bar { background: #8e24aa; }
        .dashboard-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px #0001;
            padding: 28px 28px 18px 28px;
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .dashboard-card .card-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 18px;
        }
        .dashboard-charts-row {
            display: flex;
            gap: 28px;
        }
        @media (max-width: 900px) {
            .dashboard-row, .dashboard-charts-row { flex-direction: column; gap: 18px; }
        }
    </style>
</head>
<body>
<div class="dashboard-main">
    <!-- 统计卡片区 -->
    <div class="dashboard-row">
        <div class="stat-card stat-blue">
            <div class="stat-icon">📊</div>
            <div class="stat-main"><?=$total_logs?></div>
            <div class="stat-label">日志总数</div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-icon">✅</div>
            <div class="stat-main"><?=$success_logs?></div>
            <div class="stat-label">成功操作</div>
        </div>
        <div class="stat-card stat-red">
            <div class="stat-icon">❌</div>
            <div class="stat-main"><?=$fail_logs?></div>
            <div class="stat-label">失败操作</div>
        </div>
        <div class="stat-card stat-purple">
            <div class="stat-icon">👤</div>
            <div class="stat-main"><?=$unique_users?></div>
            <div class="stat-label">活跃用户数</div>
        </div>
    </div>
    <!-- 图表区 -->
    <!-- 已删除日志趋势、操作类型分布、用户靶场分布三个卡片 -->
    <!-- 筛选与导出卡片 -->
    <div class="dashboard-row">
        <div class="dashboard-card" style="flex:2; min-width:340px;">
            <div class="card-title" style="margin-bottom:18px;">日志筛选与导出</div>
            <form method="get" class="filter-form" style="display:flex;flex-wrap:wrap;gap:18px 24px;align-items:center;">
                <label style="flex:1;min-width:160px;display:flex;align-items:center;gap:6px;white-space:nowrap;">用户名:
                    <select name="user" style="width:140px;min-width:0;border-radius:8px;padding:7px 10px;border:1px solid #d0d7de;">
                        <option value="">全部</option>
                        <?php foreach ($user_list as $u): ?>
                            <option value="<?=htmlspecialchars($u)?>" <?=($filter_user===$u?'selected':'')?>><?=htmlspecialchars($u)?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label style="flex:1;min-width:160px;display:flex;align-items:center;gap:6px;white-space:nowrap;">操作类型: <input type="text" name="action" value="<?=htmlspecialchars($filter_action)?>" style="width:140px;border-radius:8px;padding:7px 10px;border:1px solid #d0d7de;"></label>
                <label style="flex:1;min-width:160px;display:flex;align-items:center;gap:6px;white-space:nowrap;">结果: <input type="text" name="result" value="<?=htmlspecialchars($filter_result)?>" style="width:140px;border-radius:8px;padding:7px 10px;border:1px solid #d0d7de;"></label>
                <label style="flex:1;min-width:160px;display:flex;align-items:center;gap:6px;white-space:nowrap;">关键字: <input type="text" name="search" value="<?=htmlspecialchars($search)?>" style="width:140px;border-radius:8px;padding:7px 10px;border:1px solid #d0d7de;"></label>
                <button type="submit" class="btn-action" style="height:38px;padding:0 28px;font-size:1em;">筛选</button>
                <a href="?export=excel&user=<?=urlencode($filter_user)?>&action=<?=urlencode($filter_action)?>&result=<?=urlencode($filter_result)?>&search=<?=urlencode($search)?>" class="btn-action" style="height:38px;display:inline-flex;align-items:center;justify-content:center;padding:0 28px;font-size:1em;text-decoration:none;background:#43a047;">导出Excel</a>
            </form>
            <form method="post" onsubmit="return confirm('确定要删除吗？此操作不可恢复！');" style="margin-top:18px;display:flex;align-items:center;gap:10px;">
                <input type="text" name="delete_user" placeholder="输入用户名可只删除该用户日志" style="flex:1;border-radius:8px;padding:7px 10px;border:1px solid #d0d7de;">
                <button type="submit" name="delete_logs" class="btn-action" style="background:#e53935;">删除日志</button>
                <span style="color:#888;font-size:13px;">（留空则删除全部日志）</span>
            </form>
        </div>
    </div>
    <!-- 日志表格卡片 -->
    <div class="dashboard-row">
        <div class="dashboard-card" style="flex:3;min-width:340px;">
            <div class="card-title" style="margin-bottom:18px;">日志明细</div>
            <div class="log-table-wrap" style="overflow-x:auto;">
                <table class="log-table" style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;">
                    <thead style="background:#f5f6fa;">
                        <tr>
                            <th>时间</th><th>用户名</th><th>操作类型</th><th>操作详情</th><th>结果</th><th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($show_logs as $log): ?>
                        <tr>
                            <td><?=highlight($log['time'], $search)?></td>
                            <td><?=highlight($log['user'], $search)?></td>
                            <td><?=highlight($log['action'], $search)?></td>
                            <td><?=highlight($log['detail'], $search)?></td>
                            <td><?=highlight($log['result'], $search)?></td>
                            <td><?=highlight($log['ip'], $search)?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination" style="margin:18px 0 0 0;display:flex;justify-content:center;gap:8px;">
                <?php for ($i=1; $i<=$total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-btn active" style="background:#3f51b5;color:#fff;">[<?=$i?>]</span>
                    <?php else: ?>
                        <a href="?page=<?=$i?>&user=<?=urlencode($filter_user)?>&action=<?=urlencode($filter_action)?>&result=<?=urlencode($filter_result)?>&search=<?=urlencode($search)?>" class="page-btn">[<?=$i?>]</a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>
<style>
.btn-action {
    background: #3f51b5;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0 18px;
    font-size: 1em;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    box-shadow: none;
    height: 38px;
    display: inline-block;
}
.btn-action:hover { background: #283593; }
.page-btn {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    background: #f5f6fa;
    color: #333;
    text-decoration: none;
    font-size: 1em;
    transition: background 0.2s;
}
.page-btn.active, .page-btn:hover { background: #3f51b5; color: #fff; }
.log-table th, .log-table td {
    padding: 8px 10px;
    text-align: center;
    border-bottom: 1px solid #eee;
}
.log-table th {
    background: #f5f6fa;
    color: #666;
    font-weight: 600;
}
.log-table tr:last-child td { border-bottom: none; }
.log-table tr:hover { background: #f0f4ff; }
</style>
</body>
<?php
$__end = microtime(true);
$seconds = $__end - $__start;
$minutes = $seconds / 60;
echo '<div style="color:#888;font-size:12px;text-align:center;margin:10px 0;">页面生成耗时: ' . round($minutes, 4) . ' 分钟（' . round($seconds, 4) . ' 秒）</div>';
// 添加返回控制板按钮
?>
<div style="text-align:center;margin:18px 0 32px 0;">
    <a href="dashboard.php" class="btn-dark" style="background:#343a40;color:#fff;border-radius:4px;padding:8px 28px;font-size:1.1em;text-decoration:none;display:inline-block;">返回控制板</a>
</div>
<?php
?>
</html> 