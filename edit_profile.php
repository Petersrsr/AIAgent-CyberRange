<?php
/**
 * edit_profile.php
 * 用户编辑个人资料的页面
 */

session_start();
require_once 'db.php';

// 检查用户是否登录
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// 消息变量
$profile_error = $_SESSION['profile_error'] ?? '';
$profile_success = $_SESSION['profile_success'] ?? '';

// 清除 session 中的消息，防止重复显示
unset($_SESSION['profile_error'], $_SESSION['profile_success']);


// --- POST 请求处理逻辑 ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- 处理个人资料更新 ---
    if (isset($_POST['update_profile'])) {
        $nickname = $_POST['nickname'];
        $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
        $gender = $_POST['gender'];
        $bio = $_POST['bio'];

        $update_stmt = $conn->prepare("UPDATE users SET nickname = ?, birthdate = ?, gender = ?, bio = ? WHERE username = ?");
        $update_stmt->bind_param("sssss", $nickname, $birthdate, $gender, $bio, $username);
        
        if ($update_stmt->execute()) {
            $_SESSION['profile_success'] = "个人资料更新成功！";
            $_SESSION['nickname'] = $nickname; // 同步更新 Session
            // log_action($username, 'edit_profile', '编辑个人资料', 'success'); // Removed as per edit hint
        } else {
            $_SESSION['profile_error'] = "资料更新失败，请稍后再试。";
            // log_action($username, 'edit_profile', '编辑个人资料', 'fail'); // Removed as per edit hint
        }
        $update_stmt->close();
    }

    // 处理完POST请求后，重定向以防止重复提交
    header("Location: edit_profile.php");
    exit();
}


// --- GET 请求处理逻辑 (获取页面显示数据) ---
$stmt = $conn->prepare("SELECT nickname, birthdate, gender, bio FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑个人资料 - 东海学院网络靶场</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1><a href="dashboard.php">东海学院网络靶场 控制板</a></h1>
            <div class="user-menu">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['nickname'] ?: $username); ?></span>
                <a href="profile.php" class="btn-profile">个人资料</a>
                <a href="logout.php" class="btn-logout">退出登录</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="profile-layout">
            <div class="profile-sidebar">
                <div class="profile-avatar-placeholder">头像</div>
                <h3><?php echo htmlspecialchars($username); ?></h3>
                <p class="description">在这里，您可以更新您的公开个人信息。</p>
            </div>
            <div class="profile-main">
                <h2>编辑个人资料</h2>
                <?php if ($profile_success): ?><div class="success-message"><?php echo $profile_success; ?></div><?php endif; ?>
                <?php if ($profile_error): ?><div class="error-message"><?php echo $profile_error; ?></div><?php endif; ?>

                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="update_profile" value="1">
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
                        <button type="submit" class="btn">保存资料</button>
                        <a href="profile.php" class="btn-cancel">返回</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <p>版权所有 &copy; <?php echo date("Y"); ?> 上海市东海职业技术学院</p>
        <p><a href="https://beian.miit.gov.cn/" target="_blank">沪ICP备2025126528号-1</a></p>
    </footer>
</body>
</html> 