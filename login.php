<?php
require_once 'backend/config/config.php';

if (isset($_POST['login'])) {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/');
                
                $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
                $stmt->execute([
                    'user_id' => $user['id'],
                    'token' => $token,
                    'expires_at' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60))
                ]);
            }
            
            setFlashMessage('success', 'เข้าสู่ระบบสำเร็จ');
            header("Location: index.php");
            exit();
        } else {
            setFlashMessage('danger', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        }
    } catch(PDOException $e) {
        setFlashMessage('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - UBIDS Jobs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">เข้าสู่ระบบ</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $flash = getFlashMessage();
                        if ($flash) {
                            echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
                        }
                        ?>
                        <form action="" method="POST" onsubmit="return validateForm('loginForm')" id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">จดจำฉัน</label>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
                        <p class="mt-2 mb-0"><a href="forgot-password.php">ลืมรหัสผ่าน?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 