<?php
/**
 * edit_profile.php
 * 用户编辑个人资料的页面和逻辑
 */

session_start();
require_once 'db.php';

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$error_message = '';
$success_message = '';

// 获取当前用户信息
$stmt = $conn->prepare("SELECT nickname, birthdate, gender, bio FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $gender = $_POST['gender'];
    $bio = $_POST['bio'];

    // 更新用户信息
    $update_stmt = $conn->prepare("UPDATE users SET nickname = ?, birthdate = ?, gender = ?, bio = ? WHERE username = ?");
    $update_stmt->bind_param("sssss", $nickname, $birthdate, $gender, $bio, $username);
    
    if ($update_stmt->execute()) {
        $success_message = "资料更新成功！";
        // 同步更新 Session 中的昵称
        $_SESSION['nickname'] = $nickname;
        // 重新获取更新后的用户信息以显示
        $user = [
            'nickname' => $nickname,
            'birthdate' => $birthdate,
            'gender' => $gender,
            'bio' => $bio,
        ];
    } else {
        $error_message = "资料更新失败，请稍后再试。";
    }
    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑个人资料 - AI靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1><a href="dashboard.php" style="color: white; text-decoration: none;">AI靶场 控制板</a></h1>
            <div class="user-menu">
                <span>欢迎, <?php echo htmlspecialchars($username); ?></span>
                <a href="profile.php" class="btn-profile">个人资料</a>
                <a href="logout.php" class="btn-logout">退出登录</a>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <h2>编辑个人资料</h2>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="edit_profile.php" method="post" class="profile-form">
            <div class="input-group">
                <label for="nickname">昵称</label>
                <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="birthdate">生日</label>
                <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="gender">性别</label>
                <select id="gender" name="gender">
                    <option value="保密" <?php echo ($user['gender'] ?? '保密') == '保密' ? 'selected' : ''; ?>>保密</option>
                    <option value="男" <?php echo ($user['gender'] ?? '') == '男' ? 'selected' : ''; ?>>男</option>
                    <option value="女" <?php echo ($user['gender'] ?? '') == '女' ? 'selected' : ''; ?>>女</option>
                </select>
            </div>
            <div class="input-group">
                <label for="bio">个人简介</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">保存更改</button>
                <a href="profile.php" class="btn-cancel">返回</a>
            </div>
        </form>
    </div>
</body>
</html> 