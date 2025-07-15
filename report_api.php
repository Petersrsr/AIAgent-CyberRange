<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username'])) {
    echo json_encode([]); exit;
}
require_once 'db.php';
$user = $_SESSION['username'];
// 查询用户所有靶场的easy/medium/hard完成记录
global $pdo;
$stmt = $pdo->prepare("SELECT challenge, level, time_used, error_count FROM challenge_records WHERE user=? AND level IN ('easy','medium','hard')");
$stmt->execute([$user]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$challenge_map = [];
foreach ($rows as $row) {
    $challenge = $row['challenge'];
    if (!isset($challenge_map[$challenge])) $challenge_map[$challenge] = [];
    $challenge_map[$challenge][] = $row;
}
$result = [];
foreach ($challenge_map as $challenge => $records) {
    $total_time = 0; $total_errors = 0;
    foreach ($records as $rec) {
        $total_time += intval($rec['time_used']);
        $total_errors += intval($rec['error_count']);
    }
    $score = 80;
    if ($total_time < 600) $score += 10;
    if ($total_errors == 0) $score += 10;
    if ($total_errors > 5) $score -= 10;
    if ($total_errors > 5) $advice = '建议多练习相关知识点，减少错误。';
    elseif ($total_time > 1200) $advice = '建议提升解题速度。';
    else $advice = '表现优秀，继续加油！';
    $result[] = [
        'challenge' => $challenge,
        'score' => $score,
        'advice' => $advice
    ];
}
echo json_encode($result); 