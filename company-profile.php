<?php
require_once 'backend/config/config.php';

// ตรวจสอบว่าเข้าสู่ระบบแล้วและเป็นผู้จ้างงาน
if (!isLoggedIn() || getUserRole() !== 'employer') {
    setFlashMessage('danger', 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้จ้างงาน');
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลบริษัท
$stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

if (isset($_POST['save_company'])) {
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $website = clean($_POST['website']);
    $address = clean($_POST['address']);
    $logo_base64 = '';

    // จัดการอัพโหลดโลโก้
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // อ่านไฟล์รูปภาพและแปลงเป็น base64
            $image_data = file_get_contents($_FILES['logo']['tmp_name']);
            $mime_type = mime_content_type($_FILES['logo']['tmp_name']);
            $logo_base64 = 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
            
            // เพิ่ม debug log
            error_log("Logo MIME Type: " . $mime_type);
            error_log("Logo Base64 Length: " . strlen($logo_base64));
            
            if ($company) {
                // อัพเดทข้อมูลบริษัท
                $sql = "UPDATE companies SET name = ?, description = ?, website = ?, address = ?, logo = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$name, $description, $website, $address, $logo_base64, $_SESSION['user_id']]);
                
                // เพิ่ม debug log
                if ($result) {
                    error_log("Logo updated successfully");
                } else {
                    error_log("Failed to update logo");
                    error_log(print_r($stmt->errorInfo(), true));
                }
            } else {
                // เพิ่มข้อมูลบริษัทใหม่
                $stmt = $conn->prepare("INSERT INTO companies (user_id, name, description, website, address, logo) VALUES (?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$_SESSION['user_id'], $name, $description, $website, $address, $logo_base64]);
                
                // เพิ่ม debug log
                if ($result) {
                    error_log("Logo inserted successfully");
                } else {
                    error_log("Failed to insert logo");
                    error_log(print_r($stmt->errorInfo(), true));
                }
            }
        } else {
            error_log("Invalid file type: " . $ext);
        }
    } else if ($_FILES['logo']['error'] > 0) {
        error_log("File upload error: " . $_FILES['logo']['error']);
    }

    try {
        if ($company) {
            // อัพเดทข้อมูลบริษัท
            $sql = "UPDATE companies SET name = ?, description = ?, website = ?, address = ?";
            $params = [$name, $description, $website, $address];

            if ($logo_base64) {
                $sql .= ", logo = ?";
                $params[] = $logo_base64;
            }

            $sql .= " WHERE user_id = ?";
            $params[] = $_SESSION['user_id'];

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            // เพิ่มข้อมูลบริษัทใหม่
            $stmt = $conn->prepare("INSERT INTO companies (user_id, name, description, website, address, logo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $description, $website, $address, $logo_base64]);
        }

        setFlashMessage('success', 'บันทึกข้อมูลบริษัทสำเร็จ');
        header("Location: company-profile.php");
        exit();
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
    <title>ข้อมูลบริษัท - UBIDS Jobs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">ข้อมูลบริษัท</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $flash = getFlashMessage();
                        if ($flash) {
                            echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
                        }
                        ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="name" class="form-label">ชื่อบริษัท</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $company ? htmlspecialchars($company['name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="logo" class="form-label">โลโก้บริษัท</label>
                                    <?php if ($company && $company['logo']): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo $company['logo']; ?>" alt="Company Logo" class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">รายละเอียดบริษัท</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $company ? htmlspecialchars($company['description']) : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="website" class="form-label">เว็บไซต์</label>
                                <input type="url" class="form-control" id="website" name="website" value="<?php echo $company ? htmlspecialchars($company['website']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">ที่อยู่</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo $company ? htmlspecialchars($company['address']) : ''; ?></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="save_company" class="btn btn-primary">บันทึกข้อมูล</button>
                            </div>
                        </form>
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