<?php
require_once 'backend/config/config.php';

// ดึงข้อมูลการค้นหา
$keyword = isset($_GET['keyword']) ? clean($_GET['keyword']) : '';
$type = isset($_GET['type']) ? clean($_GET['type']) : '';
$location = isset($_GET['location']) ? clean($_GET['location']) : '';

// สร้าง query พื้นฐาน
$sql = "SELECT j.*, c.name as company_name, c.logo as company_logo, 
               GROUP_CONCAT(s.name) as skill_names
        FROM jobs j 
        JOIN companies c ON j.company_id = c.id
        LEFT JOIN job_skills js ON j.id = js.job_id
        LEFT JOIN skills s ON js.skill_id = s.id
        WHERE j.status = 'open'";
$params = [];

// เพิ่มเงื่อนไขการค้นหา
if ($keyword) {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR c.name LIKE ?)";
    $params = array_merge($params, ["%$keyword%", "%$keyword%", "%$keyword%"]);
}
if ($type) {
    $sql .= " AND j.type = ?";
    $params[] = $type;
}
if ($location) {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}

$sql .= " GROUP BY j.id ORDER BY j.created_at DESC";

// ดึงข้อมูลงาน
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตำแหน่งงานทั้งหมด - UBIDS Jobs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/company.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">ตำแหน่งงานทั้งหมด</h2>

        <!-- ฟอร์มค้นหา -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="GET">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <input type="text" class="form-control" name="keyword" placeholder="ค้นหาตำแหน่งงาน..." value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <select class="form-select" name="type">
                                <option value="">ประเภทงานทั้งหมด</option>
                                <option value="full-time" <?php echo $type == 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="part-time" <?php echo $type == 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="contract" <?php echo $type == 'contract' ? 'selected' : ''; ?>>Contract</option>
                                <option value="internship" <?php echo $type == 'internship' ? 'selected' : ''; ?>>Internship</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" class="form-control" name="location" placeholder="สถานที่..." value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="alert alert-info">ไม่พบตำแหน่งงานที่คุณค้นหา</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 company-card">
                            <div class="card-body">
                                <div class="d-flex mb-3">
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
                                    <div class="company-info">
                                        <h5 class="card-title mb-1">
                                            <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($job['title']); ?>
                                            </a>
                                        </h5>
                                        <h6 class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                    </div>
                                </div>

                                <p class="card-text"><?php echo mb_substr(htmlspecialchars($job['description']), 0, 150); ?>...</p>

                                <?php if ($job['skill_names']): ?>
                                    <div class="mb-3">
                                        <?php foreach (explode(',', $job['skill_names']) as $skill): ?>
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        <small>
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?><br>
                                            <i class="fas fa-clock"></i> <?php echo htmlspecialchars($job['type']); ?>
                                        </small>
                                    </div>
                                    <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 