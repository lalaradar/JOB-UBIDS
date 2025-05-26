<?php
require_once 'backend/config/config.php';

// ตรวจสอบว่ามีผู้ดูแลระบบอยู่แล้วหรือไม่
$stmt = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$result = $stmt->fetch();

if ($result['admin_count'] > 0) {
    die('มีผู้ดูแลระบบอยู่แล้ว ไม่สามารถสร้างเพิ่มได้');
}

// ข้อมูลผู้ดูแลระบบเริ่มต้น
$admin = [
    'username' => 'admin',
    'email' => 'admin@ubids.com',
    'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
    'role' => 'admin',
    'first_name' => 'System',
    'last_name' => 'Administrator'
];

try {
    // เริ่ม Transaction
    $conn->beginTransaction();

    // ตรวจสอบว่ามี username หรือ email ซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$admin['username'], $admin['email']]);
    $exists = $stmt->fetch();

    if ($exists['count'] > 0) {
        throw new Exception('username หรือ email นี้มีผู้ใช้งานแล้ว');
    }

    // เพิ่มผู้ดูแลระบบ
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role, first_name, last_name) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $admin['username'],
        $admin['email'],
        $admin['password'],
        $admin['role'],
        $admin['first_name'],
        $admin['last_name']
    ]);

    // Commit Transaction
    $conn->commit();

    echo "สร้างผู้ดูแลระบบสำเร็จ\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Password: Admin@123\n";
    echo "กรุณาเปลี่ยนรหัสผ่านหลังจากเข้าสู่ระบบครั้งแรก";

} catch (Exception $e) {
    // Rollback หากเกิดข้อผิดพลาด
    $conn->rollBack();
    die('เกิดข้อผิดพลาด: ' . $e->getMessage());
}

// ลบไฟล์นี้หลังจากใช้งานเสร็จเพื่อความปลอดภัย
unlink(__FILE__);
?>