<?php
/**
 * logout.php
 * 处理用户登出请求
 */

// 开启 Session
session_start();

// 销毁所有 Session 数据
$_SESSION = array();

// 如果存在 session cookie，则设置一个过去的过期时间来删除它
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 最后，销毁 session
session_destroy();

// 重定向到登录页面
header("Location: login.php");
exit();
?> 