<?php
session_start();
// 清除所有 session 資料
$_SESSION = array();
session_destroy();
// 導向登入頁面
header("Location: login.php");
exit();
?>
