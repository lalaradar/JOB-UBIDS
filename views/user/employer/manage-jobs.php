<?php
require_once 'backend/config/config.php';

// ตรวจสอบว่าเข้าสู่ระบบแล้วและเป็นผู้จ้างงาน
if (!isLoggedIn() || getUserRole() !== 'employer') {
    setFlashMessage('danger', 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้จ้างงาน');
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลบริษัทของผู้ใช้
$stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

if (!$company) {
    setFlashMessage('warning', 'กรุณาสร้างข้อมูลบริษัทก่อนจัดการตำแหน่งงาน');
    header("Location: company-profile.php");
    exit();
}

// จัดการการลบงาน
if (isset($_POST['delete_job']) && isset($_POST['job_id'])) {
    $job_id = clean($_POST['job_id']);
    
    // ตรวจสอบว่างานนี้เป็นของบริษัทนี้จริงๆ
    $stmt = $conn->prepare("SELECT id FROM jobs WHERE id = ? AND company_id = ?");
    $stmt->execute([$job_id, $company['id']]);
    if ($stmt->fetch()) {
        try {
            $conn->beginTransaction();
            
            // ลบทักษะที่เกี่ยวข้องก่อน
            $stmt = $conn->prepare("DELETE FROM job_skills WHERE job_id = ?");
            $stmt->execute([$job_id]);
            
            // ลบงาน
            $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
            $stmt->execute([$job_id]);
            
            $conn->commit();
            setFlashMessage('success', 'ลบตำแหน่งงานสำเร็จ');
        } catch (PDOException $e) {
            $conn->rollBack();
            setFlashMessage('danger', 'เกิดข้อผิดพลาดในการลบตำแหน่งงาน');
        }
    }
    header("Location: manage-jobs.php");
    exit();
}

// จัดการการเปลี่ยนสถานะงาน
if (isset($_POST['toggle_status']) && isset($_POST['job_id'])) {
    $job_id = clean($_POST['job_id']);
    
    // ตรวจสอบว่างานนี้เป็นของบริษัทนี้จริงๆ
    $stmt = $conn->prepare("SELECT id, status FROM jobs WHERE id = ? AND company_id = ?");
    $stmt->execute([$job_id, $company['id']]);
    if ($job = $stmt->fetch()) {
        $new_status = $job['status'] === 'open' ? 'closed' : 'open';
        $stmt = $conn->prepare("UPDATE jobs SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $job_id]);
        setFlashMessage('success', 'อัพเดทสถานะตำแหน่งงานสำเร็จ');
    }
    header("Location: manage-jobs.php");
    exit();
}

// ดึงข้อมูลงานทั้งหมดของบริษัท
$stmt = $conn->prepare("
    SELECT j.*, 
           COUNT(DISTINCT a.id) as application_count,
           GROUP_CONCAT(s.name) as skill_names
    FROM jobs j 
    LEFT JOIN applications a ON j.id = a.job_id
    LEFT JOIN job_skills js ON j.id = js.job_id
    LEFT JOIN skills s ON js.skill_id = s.id
    WHERE j.company_id = ?
    GROUP BY j.id
    ORDER BY j.created_at DESC
");
$stmt->execute([$company['id']]);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการตำแหน่งงาน - UBIDS Jobs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/company.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>จัดการตำแหน่งงาน</h2>
            <a href="post-job.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> เพิ่มตำแหน่งงานใหม่
            </a>
        </div>

        <?php
        $flash = getFlashMessage();
        if ($flash) {
            echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
        }
        ?>

        <?php if (empty($jobs)): ?>
            <div class="alert alert-info">ยังไม่มีตำแหน่งงานที่ประกาศ</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ตำแหน่งงาน</th>
                            <th>ทักษะที่ต้องการ</th>
                            <th>สถานที่</th>
                            <th>เงินเดือน</th>
                            <th>ผู้สมัคร</th>
                            <th>สถานะ</th>
                            <th>วันที่ลงประกาศ</th>
                            <th>วันที่ปิดรับสมัคร</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($job['type']); ?></small>
                                </td>
                                <td>
                                    <?php if ($job['skill_names']): ?>
                                        <?php foreach (explode(',', $job['skill_names']) as $skill): ?>
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($skill); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($job['location']); ?></td>
                                <td>
                                    <?php echo number_format($job['salary_min']); ?> - <?php echo number_format($job['salary_max']); ?> บาท
                                </td>
                                <td>
                                    <a href="view-applications.php?job_id=<?php echo $job['id']; ?>" class="text-decoration-none">
                                        <?php echo $job['application_count']; ?> คน
                                    </a>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $job['status'] === 'open' ? 'btn-success' : 'btn-secondary'; ?>">
                                            <?php echo $job['status'] === 'open' ? 'เปิดรับสมัคร' : 'ปิดรับสมัคร'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($job['created_at'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($job['deadline'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="job-detail.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="post-job.php?edit=<?php echo $job['id']; ?>" class="btn btn-sm btn-warning" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบตำแหน่งงานนี้?');">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" name="delete_job" class="btn btn-sm btn-danger" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 