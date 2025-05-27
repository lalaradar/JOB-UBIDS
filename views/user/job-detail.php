<?php
require_once 'backend/config/config.php';

if (!isset($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

// ดึงข้อมูลงานและบริษัท
$stmt = $conn->prepare("SELECT j.*, c.name as company_name, c.logo as company_logo, c.description as company_description, 
                        c.website as company_website, c.address as company_address,
                        GROUP_CONCAT(s.name) as skill_names
                        FROM jobs j 
                        JOIN companies c ON j.company_id = c.id
                        LEFT JOIN job_skills js ON j.id = js.job_id
                        LEFT JOIN skills s ON js.skill_id = s.id
                        WHERE j.id = ?
                        GROUP BY j.id");
$stmt->execute([$_GET['id']]);
$job = $stmt->fetch();

if (!$job) {
    setFlashMessage('danger', 'ไม่พบข้อมูลงาน');
    header("Location: jobs.php");
    exit();
}

// แปลงรายการทักษะเป็น array
$skills = $job['skill_names'] ? explode(',', $job['skill_names']) : [];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - <?php echo htmlspecialchars($job['company_name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/company.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body company-details">
                        <div class="d-flex align-items-center mb-4">
                            <div class="company-logo-container company-logo-header me-3">
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
                            <div class="company-info">
                                <h2 class="mb-1"><?php echo htmlspecialchars($job['title']); ?></h2>
                                <h5 class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></h5>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4>รายละเอียดงาน</h4>
                            <p class="company-description"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                        </div>

                        <div class="mb-4">
                            <h4>คุณสมบัติที่ต้องการ</h4>
                            <p class="company-description"><?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
                        </div>

                        <?php if (!empty($skills)): ?>
                        <div class="mb-4">
                            <h4>ทักษะที่ต้องการ</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($skills as $skill): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4>รายละเอียดเพิ่มเติม</h4>
                                <ul class="list-unstyled company-meta">
                                    <li><strong>ประเภทงาน:</strong> <?php echo htmlspecialchars($job['type']); ?></li>
                                    <li><strong>สถานที่ปฏิบัติงาน:</strong> <?php echo htmlspecialchars($job['location']); ?></li>
                                    <li><strong>เงินเดือน:</strong> <?php echo number_format($job['salary_min']); ?> - <?php echo number_format($job['salary_max']); ?> บาท</li>
                                    <li><strong>วันที่ปิดรับสมัคร:</strong> <?php echo date('d/m/Y', strtotime($job['deadline'])); ?></li>
                                </ul>
                            </div>
                        </div>

                        <?php if (isLoggedIn() && getUserRole() == 'applicant'): ?>
                            <div class="d-grid">
                                <a href="apply-job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">สมัครงาน</a>
                            </div>
                        <?php elseif (!isLoggedIn()): ?>
                            <div class="d-grid">
                                <a href="login.php" class="btn btn-primary">เข้าสู่ระบบเพื่อสมัครงาน</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body company-details">
                        <h4>เกี่ยวกับบริษัท</h4>
                        <div class="company-logo-container company-logo-sidebar">
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
                        <h5><?php echo htmlspecialchars($job['company_name']); ?></h5>
                        <p class="company-description"><?php echo nl2br(htmlspecialchars($job['company_description'])); ?></p>
                        <?php if ($job['company_website']): ?>
                            <p class="company-meta"><strong>เว็บไซต์:</strong> <a href="<?php echo htmlspecialchars($job['company_website']); ?>" target="_blank"><?php echo htmlspecialchars($job['company_website']); ?></a></p>
                        <?php endif; ?>
                        <p class="company-meta"><strong>ที่อยู่:</strong><br><?php echo nl2br(htmlspecialchars($job['company_address'])); ?></p>
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