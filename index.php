<?php
require_once 'backend/config/config.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบสมัครงาน UBIDS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/company.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <?php
        $flash = getFlashMessage();
        if ($flash) {
            echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
        }
        ?>
        
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>ยินดีต้อนรับสู่ระบบสมัครงาน UBIDS</h1>
                <p class="lead">ค้นหาตำแหน่งงานที่ใช่สำหรับคุณ</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form action="jobs.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="keyword" placeholder="ค้นหาตำแหน่งงาน...">
                                <button class="btn btn-primary" type="submit">ค้นหา</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>ตำแหน่งงานใหม่</h3>
                        <p>ค้นพบโอกาสใหม่ๆ ที่เพิ่งเปิดรับสมัคร</p>
                        <?php if (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-outline-primary" onclick="return confirm('กรุณาเข้าสู่ระบบก่อนดูตำแหน่งงาน')">ดูตำแหน่งงาน</a>
                        <?php else: ?>
                            <a href="jobs.php" class="btn btn-outline-primary">ดูตำแหน่งงาน</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (!isLoggedIn() || (isLoggedIn() && getUserRole() !== 'employer')): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>สร้างโปรไฟล์</h3>
                        <p>สร้างโปรไฟล์เพื่อให้บริษัทค้นพบคุณ</p>
                        <?php if (!isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-outline-primary">สมัครสมาชิก</a>
                        <?php else: ?>
                            <a href="profile.php" class="btn btn-outline-primary">แก้ไขโปรไฟล์</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>ติดตามสถานะ</h3>
                        <p>ติดตามสถานะการสมัครงานของคุณ</p>
                        <?php if (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-outline-primary">เข้าสู่ระบบ</a>
                        <?php else: ?>
                            <a href="my-applications.php" class="btn btn-outline-primary">ดูใบสมัครของฉัน</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- แสดงตำแหน่งงานล่าสุด -->
        <div class="row mt-5">
            <div class="col-md-12">
                <h2 class="text-center mb-4">ตำแหน่งงานล่าสุด</h2>
                <?php
                try {
                    $stmt = $conn->query("SELECT j.*, c.name as company_name, c.logo as company_logo 
                                        FROM jobs j 
                                        JOIN companies c ON j.company_id = c.id 
                                        WHERE j.status = 'open' 
                                        ORDER BY j.created_at DESC 
                                        LIMIT 6");
                    $latest_jobs = $stmt->fetchAll();

                    if (count($latest_jobs) > 0):
                ?>
                    <div class="row">
                        <?php foreach ($latest_jobs as $job): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="company-logo-container company-logo-list me-3">
                                                <?php if ($job['company_logo']): ?>
                                                    <img src="<?php echo $job['company_logo']; ?>" 
                                                         alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                                                         class="company-logo">
                                                <?php else: ?>
                                                    <div class="no-logo">
                                                        <?php echo mb_substr($job['company_name'], 0, 1); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo clean($job['title']); ?></h5>
                                                <h6 class="card-subtitle text-muted"><?php echo clean($job['company_name']); ?></h6>
                                            </div>
                                        </div>
                                        <p class="card-text"><?php echo mb_substr(clean($job['description']), 0, 100); ?>...</p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i> <?php echo clean($job['location']); ?><br>
                                                <i class="fas fa-clock"></i> <?php echo clean($job['type']); ?>
                                            </small>
                                        </p>
                                        <?php if (!isLoggedIn()): ?>
                                            <a href="login.php" class="btn btn-sm btn-primary" onclick="return confirm('กรุณาเข้าสู่ระบบก่อนดูรายละเอียด')">ดูรายละเอียด</a>
                                        <?php else: ?>
                                            <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">ดูรายละเอียด</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">ไม่มีตำแหน่งงานที่เปิดรับในขณะนี้</div>
                <?php 
                    endif;
                } catch(PDOException $e) {
                    echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการดึงข้อมูล</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 