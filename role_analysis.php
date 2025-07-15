<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
$user = $_SESSION['username'];
// 获取所有靶场名
$challenge_list = [];
$res = $conn->query("SELECT DISTINCT challenge FROM challenge_records WHERE user='".$conn->real_escape_string($user)."'");
while ($row = $res->fetch_assoc()) {
    $challenge_list[] = $row['challenge'];
}
// 靶场英文名与中文名映射
$challenge_name_map = [
    'forcebreak' => '暴力破解 (Brute Force)',
    'command' => '命令注入 (Command Injection)',
    'command_injection' => '命令注入 (Command Injection)', // 兼容数据库中如有此写法
    'csrf' => '跨站请求伪造 (CSRF)',
    'file' => '文件包含 (File Inclusion)',
    'upload' => '文件上传 (File Upload)',
    'insecure' => '不安全的验证码 (Insecure CAPTCHA)',
    'injection' => 'SQL注入 (SQL Injection)',
    'blind' => 'SQL盲注 (SQLi - Blind)',
    'Reflected' => '反射型XSS (Reflected XSS)',
    'xss' => '存储型XSS (Stored XSS)',
    'Dom' => 'DOM型XSS (DOM Based XSS)',
    'weak' => '弱会话ID (Weak Session IDs)',
    'csp' => '绕过内容安全策略 (CSP)'
];
// 靶场类型映射（评分方式）
$challenge_type_map = [
    'forcebreak' => 'times', // 暴力破解按次数
    // 其余均为知识/技巧型
    'command' => 'knowledge',
    'csrf' => 'knowledge',
    'file' => 'knowledge',
    'upload' => 'knowledge',
    'insecure' => 'knowledge',
    'injection' => 'knowledge',
    'blind' => 'knowledge',
    'Reflected' => 'knowledge',
    'xss' => 'knowledge',
    'Dom' => 'knowledge',
    'weak' => 'knowledge',
    'csp' => 'knowledge',
];
// 靶场分值分配
$challenge_score_map = [
    'forcebreak' => ['easy'=>2, 'medium'=>3, 'hard'=>5, 'impossible'=>0],
    // 其余靶场
    'default' => ['easy'=>1.5, 'medium'=>2, 'hard'=>4, 'impossible'=>0], // 不可能级别可设为0
];
// 获取当前选中的靶场
$selected_challenge = isset($_GET['challenge']) ? $_GET['challenge'] : (count($challenge_list) ? $challenge_list[0] : '');
// 查询该靶场各难度的尝试和错误
$stats = [];
if ($selected_challenge) {
    $stmt = $conn->prepare("SELECT level, COUNT(*) as attempts, SUM(error_count) as errors FROM challenge_records WHERE user=? AND challenge=? GROUP BY level ORDER BY FIELD(level,'easy','medium','hard','impossible')");
    $stmt->bind_param('ss', $user, $selected_challenge);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
}
// 总览统计
$stmt2 = $conn->prepare("SELECT COUNT(DISTINCT challenge) as total_challenges, COUNT(*) as total_attempts, SUM(error_count) as total_errors FROM challenge_records WHERE user=?");
$stmt2->bind_param('s', $user);
$stmt2->execute();
$stmt2->bind_result($total_challenges, $total_attempts, $total_errors);
$stmt2->fetch();
$stmt2->close();
$total_errors = $total_errors ?: 0;
$total_attempts = $total_attempts ?: 0;
$overall_accuracy = $total_attempts > 0 ? round(100 * ($total_attempts - $total_errors) / $total_attempts, 1) : 0;
// 难度标签
$level_map = ['easy'=>'简单','medium'=>'中等','hard'=>'困难','impossible'=>'不可能'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>角色分析 - 靶场平台</title>
    <link rel="stylesheet" href="style.css">
    <style>
    body {
        background: linear-gradient(135deg, #e8eaf6 0%, #f3e5f5 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', Arial, sans-serif;
    }
    .dashboard-main {
        max-width: 1000px;
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
        min-width: 180px; /* 保证所有统计卡片宽度一致 */
    }
    .stat-card .stat-icon {
        font-size: 2.1em;
        margin-bottom: 8px;
    }
    .stat-card .stat-main {
        font-size: 2.4em;
        font-weight: bold;
        letter-spacing: 2px;
        color: #222;
        margin-bottom: 2px;
    }
    .stat-card .stat-label {
        font-size: 1.1em;
        color: #888;
        margin-bottom: 12px;
    }
    .stat-blue { border-top: 4px solid #3f51b5; }
    .stat-green { border-top: 4px solid #43a047; }
    .stat-red { border-top: 4px solid #e53935; }
    .stat-purple { border-top: 4px solid #8e24aa; }
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
    .challenge-table th, .challenge-table td {
        padding: 8px 16px; /* 增加左右内边距 */
        text-align: center;
        white-space: nowrap; /* 不换行 */
    }
    .challenge-table th:nth-child(1), .challenge-table td:nth-child(1) {
        width: 28%;
        min-width: 100px;
        text-align: left;
    }
    .challenge-table th:nth-child(2), .challenge-table td:nth-child(2),
    .challenge-table th:nth-child(3), .challenge-table td:nth-child(3),
    .challenge-table th:nth-child(4), .challenge-table td:nth-child(4) {
        width: 14%;
        max-width: 70px;
        min-width: 50px;
        text-align: center;
    }
    .challenge-table th {
        background: #f5f6fa;
        color: #2a5;
        font-weight: 600;
        border-bottom: 2px solid #e0e0e0;
    }
    .challenge-table td {
        color: #333;
        border-bottom: 1px solid #f2f2f2;
    }
    .challenge-table tr:last-child td { border-bottom: none; }
    .advice {
        background: #eafbe7;
        border-radius: 8px;
        padding: 16px 22px;
        color: #1a5;
        font-weight: 500;
        margin: 28px 0 0 0;
        text-align: center;
        font-size: 1.1em;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
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
    .back-btn {
        background: none;
        color: #111;
        border: none;
        border-radius: 6px;
        padding: 10px 36px;
        font-size: 1em;
        cursor: pointer;
        text-align: center;
        transition: color 0.2s;
        box-shadow: none;
        margin-top: 30px;
        font-weight: bold;
    }
    .back-btn:hover { color: #222; background: none; }
    @media (max-width: 900px) {
        .dashboard-row { flex-direction: column; gap: 18px; }
    }
    </style>
</head>
<body>
<div class="dashboard-main">
    <div class="analysis-title" style="text-align:center;font-size:2.1em;font-weight:700;color:#2a5;margin-bottom:10px;letter-spacing:1px;">我的角色分析报告</div>
    <!-- 统计卡片区 -->
    <div class="dashboard-row">
        <div class="stat-card stat-green">
            <div class="stat-icon">🎯</div>
            <div class="stat-main"><?=$overall_accuracy?>%</div>
            <div class="stat-label">总正确率</div>
        </div>
        <div class="stat-card stat-blue">
            <div class="stat-icon">🏁</div>
            <div class="stat-main"><?=$total_challenges?></div>
            <div class="stat-label">靶场数</div>
        </div>
        <div class="stat-card stat-purple">
            <div class="stat-icon">🔢</div>
            <div class="stat-main"><?=$total_attempts?></div>
            <div class="stat-label">总尝试</div>
        </div>
        <div class="stat-card stat-red">
            <div class="stat-icon">❌</div>
            <div class="stat-main"><?=$total_errors?></div>
            <div class="stat-label">总错误</div>
        </div>
    </div>
    <!-- 筛选卡片区 -->
    <div class="dashboard-row">
        <div class="dashboard-card" style="flex:2; min-width:340px;">
            <div class="card-title">靶场筛选</div>
            <form method="get" class="filter-bar" style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
                <label class="filter-label" for="challenge" style="font-weight:500;color:#333;">靶场：</label>
                <select name="challenge" id="challenge" class="filter-select" onchange="this.form.submit()" style="padding:7px 18px;border-radius:7px;border:1.5px solid #e0e0e0;font-size:1em;background:#f8fafb;color:#222;min-width:180px;">
                  <?php foreach ($challenge_list as $c): ?>
                    <option value="<?=htmlspecialchars($c)?>" <?=$c==$selected_challenge?'selected':''?>><?=isset($challenge_name_map[$c])?$challenge_name_map[$c]:htmlspecialchars($c)?></option>
                  <?php endforeach; ?>
                </select>
                <!-- 新增重置靶场按钮 -->
                <button type="button" class="btn-action" style="margin-left:10px;" onclick="showResetModal()">重置靶场</button>
            </form>
            <!-- 重置确认弹窗 -->
            <div id="resetModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
                <div style="background:#fff;border-radius:14px;box-shadow:0 8px 32px #0002;padding:32px 28px 22px 28px;max-width:380px;margin:120px auto 0 auto;position:relative;">
                    <div style="font-size:1.1em;font-weight:bold;color:#e53935;margin-bottom:18px;">重置确认</div>
                    <div style="font-size:1em;color:#222;line-height:1.7;">此操作将会重置靶场和您的分数，你确定要这么做吗？</div>
                    <div style="margin-top:22px;text-align:right;">
                        <button onclick="hideResetModal()" style="background:#888;color:#fff;border:none;border-radius:7px;padding:7px 22px;font-size:1em;margin-right:10px;">取消</button>
                        <button onclick="confirmResetChallenge()" style="background:#e53935;color:#fff;border:none;border-radius:7px;padding:7px 22px;font-size:1em;">确定重置</button>
                    </div>
                </div>
            </div>
            <script>
            function showResetModal() {
                document.getElementById('resetModal').style.display = 'block';
            }
            function hideResetModal() {
                document.getElementById('resetModal').style.display = 'none';
            }
            function confirmResetChallenge() {
                hideResetModal();
                var challenge = document.getElementById('challenge').value;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'reset_challenge.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                location.reload();
                            } else {
                                alert('重置失败：' + (res.msg || '未知错误'));
                            }
                        } catch(e) {
                            alert('重置失败：服务器返回异常');
                        }
                    }
                };
                xhr.send('challenge=' + encodeURIComponent(challenge));
            }
            // 点击遮罩关闭弹窗
            document.addEventListener('click', function(e){
                var modal = document.getElementById('resetModal');
                if(modal && modal.style.display==='block' && e.target===modal){
                    hideResetModal();
                }
            });
            </script>
        </div>
    </div>
    <!-- 表格卡片区 -->
    <div class="dashboard-row">
        <div class="dashboard-card" style="flex:3;min-width:340px;">
            <!-- 关于得分按钮 -->
            <div style="text-align:right;margin-bottom:10px;">
                <button class="btn-action" onclick="document.getElementById('scoreRuleModal').style.display='block'">关于得分</button>
            </div>
            <!-- 关于得分弹窗 -->
            <div id="scoreRuleModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
                <div style="background:#fff;border-radius:14px;box-shadow:0 8px 32px #0002;padding:32px 28px 22px 28px;max-width:480px;margin:80px auto 0 auto;position:relative;">
                    <div style="font-size:1.3em;font-weight:bold;color:#3f51b5;margin-bottom:12px;">得分规则说明</div>
                    <div style="font-size:1em;color:#222;line-height:1.8;">
                        <b>总分100分，分配如下：</b><br>
                        <b>1. 暴力破解：</b> 总分10分，按难度分配：简单2分，中等3分，困难5分，不可能0分。<br>
                        <b>2. 其余12个靶场：</b> 总分90分，每个靶场7.5分，按难度分配：简单1.5分，中等2分，困难4分。<br>
                        <b>3. 得分判定：</b> 每个难度完成即得对应分数，未完成为0分。<br>
                        <b>4. 示例：</b> 如“命令注入”完成简单和中等，得1.5+2=3.5分，未完成困难为0分。<br>
                        <b>5. 不可能难度（impossible）不计入得分。</b>
                    </div>
                    <button onclick="document.getElementById('scoreRuleModal').style.display='none'" style="margin-top:18px;float:right;background:#3f51b5;color:#fff;border:none;border-radius:7px;padding:7px 22px;font-size:1em;">关闭</button>
                </div>
            </div>
            <script>
            // 点击遮罩关闭弹窗
            document.addEventListener('click', function(e){
                var modal = document.getElementById('scoreRuleModal');
                if(modal && modal.style.display==='block' && e.target===modal){
                    modal.style.display='none';
                }
            });
            </script>
            <div class="card-title">各难度表现</div>
            <div class="log-table-wrap" style="overflow-x:auto;">
                <table class="challenge-table" style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;">
                    <thead style="background:#f5f6fa;">
                        <tr><th>难度</th><th>尝试次数</th><th>错误次数</th><th>得分</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $row): ?>
                        <tr>
                          <td><?=isset($level_map[$row['level']])?$level_map[$row['level']]:htmlspecialchars($row['level'])?></td>
                          <td><?=$row['attempts']?></td>
                          <td><?=$row['errors']?></td>
                          <td>
                            <?php
                            $type = isset($challenge_type_map[$selected_challenge]) ? $challenge_type_map[$selected_challenge] : 'knowledge';
                            // 得分分配
                            $score_map = ($selected_challenge==='forcebreak') ? $challenge_score_map['forcebreak'] : $challenge_score_map['default'];
                            $level = $row['level'];
                            $score = isset($score_map[$level]) ? $score_map[$level] : 0;
                            // 完成判定
                            if ($row['attempts']>0 && $row['errors']<$row['attempts']) {
                                echo $score;
                            } else {
                                echo '0';
                            }
                            ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- 统计区后，表格区前，插入学习建议生成逻辑 -->
            <?php
            // 生成学习建议
            $advice_text = '';
            if ($overall_accuracy >= 90) {
                $advice_text = '你的正确率非常高，说明你已经具备了扎实的网络安全攻防能力，建议挑战更高难度或尝试实战演练！';
            } elseif ($overall_accuracy >= 70) {
                $advice_text = '你的正确率较高，基础知识掌握良好。可以继续巩固薄弱环节，尝试更多高难度靶场。';
            } elseif ($overall_accuracy >= 40) {
                $advice_text = '你的正确率一般，建议多复盘错误题目，查漏补缺，逐步提升解题能力。';
            } elseif ($overall_accuracy > 0) {
                $advice_text = '你的正确率较低，建议系统学习网络安全基础知识，并多做基础靶场练习。';
            } else {
                $advice_text = '暂无数据，快去挑战靶场提升自己吧！';
            }
            ?>
            <div class="advice" style="margin-bottom:18px;">学习建议：<?=$advice_text?></div>
            <div style="text-align:center;"><a href="dashboard.php" class="back-btn">返回控制板</a></div>
        </div>
    </div>
</div>
</body>
</html> 