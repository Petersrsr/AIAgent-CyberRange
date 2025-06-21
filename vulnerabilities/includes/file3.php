<?php
echo "<h3>这里是 File3.php</h3>";
echo "<p>探索文件包含的乐趣!</p>";
$ip = $_SERVER['REMOTE_ADDR'];
echo "<p>你的IP地址是: " . htmlspecialchars($ip) . "</p>";
?> 