<?php
require_once '../../backend/config/config.php';

if (isset($_POST['register'])) {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = clean($_POST['role']);
    $first_name = clean($_POST['first_name']);
    $last_name = clean($_POST['last_name']);
    
    // ตรวจสอบรหัสผ่านตรงกัน
    if ($password !== $confirm_password) {
        setFlashMessage('danger', 'รหัสผ่านไม่ตรงกัน');
    } else {
        try {
            // ตรวจสอบว่ามีชื่อผู้ใช้หรืออีเมลนี้ในระบบแล้วหรือไม่
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                setFlashMessage('danger', 'ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว');
            } else {
                // เข้ารหัสรหัสผ่าน
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // เพิ่มข้อมูลผู้ใช้
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, first_name, last_name) VALUES (:username, :email, :password, :role, :first_name, :last_name)");
                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'role' => $role,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]);
                
                setFlashMessage('success', 'สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ');
                header("Location: login.php");
                exit();
            }
        } catch(PDOException $e) {
            setFlashMessage('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - UBIDS Jobs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">สมัครสมาชิก</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $flash = getFlashMessage();
                        if ($flash) {
                            echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
                        }
                        ?>
                        <form action="" method="POST" onsubmit="return validateForm('registerForm')" id="registerForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">ชื่อ</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">นามสกุล</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">ประเภทสมาชิก</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="applicant">ผู้สมัครงาน</option>
                                    <option value="employer">ผู้จ้างงาน</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div id="passwordStrength"></div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">ฉันยอมรับ<a href="terms.php">เงื่อนไขการใช้งาน</a></label>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100">สมัครสมาชิก</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updatePasswordStrength('password', 'passwordStrength');
        });
    </script>
</body>
</html> 