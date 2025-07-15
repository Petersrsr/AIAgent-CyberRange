<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username'])) {
    echo json_encode(['success'=>false, 'msg'=>'未登录']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false, 'msg'=>'请求方式错误']);
    exit;
}
if (!isset($_POST['challenge']) || !$_POST['challenge']) {
    echo json_encode(['success'=>false, 'msg'=>'缺少参数']);
    exit;
}
require_once 'db.php';
$user = $_SESSION['username'];
$challenge = $_POST['challenge'];
$stmt = $conn->prepare('DELETE FROM challenge_records WHERE user=? AND challenge=?');
$stmt->bind_param('ss', $user, $challenge);
$ok = $stmt->execute();
$stmt->close();
if ($ok) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'msg'=>'数据库操作失败']);
} 