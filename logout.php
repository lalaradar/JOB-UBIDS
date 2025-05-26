<?php
require_once 'backend/config/config.php';

// ล้างข้อมูล session
session_destroy();

// ลบคุกกี้ที่เกี่ยวข้อง
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// แสดงข้อความและเปลี่ยนเส้นทาง
setFlashMessage('success', 'ออกจากระบบสำเร็จ');
header("Location: index.php");
