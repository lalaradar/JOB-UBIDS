<?php
require_once 'backend/config/config.php';

// กำหนดเส้นทางเริ่มต้น
$route = isset($_GET['route']) ? $_GET['route'] : 'home';

// ตรวจสอบการเข้าสู่ระบบและสิทธิ์การเข้าถึง
$restricted_routes = [
    'employer' => ['company-profile', 'post-job', 'manage-jobs'],
    'seeker' => ['profile', 'my-applications']
];

// ฟังก์ชันตรวจสอบสิทธิ์การเข้าถึง
function checkAccess($route) {
    global $restricted_routes;
    
    // ถ้ายังไม่ได้เข้าสู่ระบบและพยายามเข้าถึงหน้าที่ต้องล็อกอิน
    if (!isLoggedIn() && in_array($route, array_merge(...array_values($restricted_routes)))) {
        setFlashMessage('warning', 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งาน');
        header("Location: index.php?route=login");
        exit();
    }

    // ตรวจสอบสิทธิ์ตามบทบาท
    if (isLoggedIn()) {
        $role = getUserRole();
        if ($role == 'employer' && in_array($route, $restricted_routes['seeker'])) {
            setFlashMessage('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            header("Location: index.php");
            exit();
        }
        if ($role == 'seeker' && in_array($route, $restricted_routes['employer'])) {
            setFlashMessage('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            header("Location: index.php");
            exit();
        }
    }
}

// ตรวจสอบสิทธิ์การเข้าถึง
checkAccess($route);

// กำหนดเส้นทางไปยังไฟล์ที่เกี่ยวข้อง
switch ($route) {
    case 'home':
        require_once 'views/home.php';
        break;
    
    // เส้นทางสำหรับผู้ใช้ทั่วไป
    case 'login':
        require_once 'views/user/login.php';
        break;
    case 'register':
        require_once 'views/user/register.php';
        break;
    case 'jobs':
        require_once 'views/user/jobs.php';
        break;
    case 'job-detail':
        require_once 'views/user/job-detail.php';
        break;
        
    // เส้นทางสำหรับผู้จ้างงาน
    case 'company-profile':
        require_once 'views/user/employer/company-profile.php';
        break;
    case 'post-job':
        require_once 'views/user/employer/post-job.php';
        break;
    case 'manage-jobs':
        require_once 'views/user/employer/manage-jobs.php';
        break;
        
    // เส้นทางสำหรับผู้สมัครงาน
    case 'profile':
        require_once 'views/user/seeker/profile.php';
        break;
    case 'my-applications':
        require_once 'views/user/seeker/my-applications.php';
        break;
        
    default:
        // หากไม่พบเส้นทาง ให้ไปที่หน้าแรก
        header("Location: index.php");
        exit();
}
?> 