<?php
require_once 'backend/config/config.php';

// ตรวจสอบว่าเข้าสู่ระบบแล้วและเป็นผู้จ้างงาน
if (!isLoggedIn() || getUserRole() !== 'employer') {
    setFlashMessage('danger', 'กรุณาเข้าสู่ระบบด้วยบัญชีผู้จ้างงาน');
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีข้อมูลบริษัทแล้วหรือไม่
$stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

if (!$company) {
    setFlashMessage('warning', 'กรุณาเพิ่มข้อมูลบริษัทก่อนประกาศรับสมัครงาน');
    header("Location: company-profile.php");
    exit();
}

// ดึงข้อมูลทักษะทั้งหมด
$stmt = $conn->query("SELECT * FROM skills ORDER BY name");
$skills = $stmt->fetchAll();

// ดึงข้อมูลงานที่ต้องการแก้ไข
$job = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT j.*, GROUP_CONCAT(js.skill_id) as skill_ids 
                           FROM jobs j 
                           LEFT JOIN job_skills js ON j.id = js.job_id 
                           WHERE j.id = ? AND j.company_id = ?
                           GROUP BY j.id");
    $stmt->execute([$_GET['id'], $company['id']]);
    $job = $stmt->fetch();

    if (!$job) {
        setFlashMessage('danger', 'ไม่พบข้อมูลงานที่ต้องการแก้ไข');
        header("Location: manage-jobs.php");
        exit();
    }
}

if (isset($_POST['save_job'])) {
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    $requirements = clean($_POST['requirements']);
    $salary_min = clean($_POST['salary_min']);
    $salary_max = clean($_POST['salary_max']);
    $location = clean($_POST['location']);
    $type = clean($_POST['type']);
    $deadline = clean($_POST['deadline']);
    $selected_skills = isset($_POST['skills']) ? $_POST['skills'] : [];

    try {
        $conn->beginTransaction();

        if ($job) {
            // อัพเดทข้อมูลงาน
            $stmt = $conn->prepare("UPDATE jobs SET title = ?, description = ?, requirements = ?, 
                                  salary_min = ?, salary_max = ?, location = ?, type = ?, deadline = ? 
                                  WHERE id = ? AND company_id = ?");
            $stmt->execute([
                $title, $description, $requirements, $salary_min, $salary_max,
                $location, $type, $deadline, $job['id'], $company['id']
            ]);

            // ลบทักษะเดิม
            $stmt = $conn->prepare("DELETE FROM job_skills WHERE job_id = ?");
            $stmt->execute([$job['id']]);

            // เพิ่มทักษะใหม่
            if (!empty($selected_skills)) {
                $stmt = $conn->prepare("INSERT INTO job_skills (job_id, skill_id) VALUES (?, ?)");
                foreach ($selected_skills as $skill_id) {
                    $stmt->execute([$job['id'], $skill_id]);
                }
            }
        } else {
            // เพิ่มข้อมูลงานใหม่
            $stmt = $conn->prepare("INSERT INTO jobs (company_id, title, description, requirements, 
                                  salary_min, salary_max, location, type, deadline) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $company['id'], $title, $description, $requirements,
                $salary_min, $salary_max, $location, $type, $deadline
            ]);

            $job_id = $conn->lastInsertId();

            // เพิ่มทักษะที่ต้องการ
            if (!empty($selected_skills)) {
                $stmt = $conn->prepare("INSERT INTO job_skills (job_id, skill_id) VALUES (?, ?)");
                foreach ($selected_skills as $skill_id) {
                    $stmt->execute([$job_id, $skill_id]);
                }
            }
        }

        $conn->commit();
        setFlashMessage('success', ($job ? 'แก้ไข' : 'เพิ่ม') . 'ข้อมูลงานสำเร็จ');
        header("Location: manage-jobs.php");
        exit();

    } catch(PDOException $e) {
        $conn->rollBack();
        setFlashMessage('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

// แปลงรายการทักษะเป็น array
$selected_skills = [];
if ($job && $job['skill_ids']) {
    $selected_skills = explode(',', $job['skill_ids']);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $job ? 'แก้ไขประกาศรับสมัครงาน' : 'ประกาศรับสมัครงาน'; ?> - UBIDS Jobs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo $job ? 'แก้ไขประกาศรับสมัครงาน' : 'ประกาศรับสมัครงาน'; ?></h4>
                        <a href="manage-jobs.php" class="btn btn-light btn-sm">กลับไปหน้าจัดการงาน</a>
                    </div>
                    <div class="card-body">
                        <?php
                        $flash = getFlashMessage();
                        if ($flash) {
                            echo "<div class='alert alert-{$flash['type']}'>{$flash['message']}</div>";
                        }
                        ?>
                        <form action="" method="POST" id="jobForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">ชื่อตำแหน่งงาน</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo $job ? htmlspecialchars($job['title']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">รายละเอียดงาน</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="5" required><?php echo $job ? htmlspecialchars($job['description']) : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="requirements" class="form-label">คุณสมบัติที่ต้องการ</label>
                                <textarea class="form-control" id="requirements" name="requirements" 
                                          rows="5" required><?php echo $job ? htmlspecialchars($job['requirements']) : ''; ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="salary_min" class="form-label">เงินเดือนขั้นต่ำ</label>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min" 
                                           value="<?php echo $job ? $job['salary_min'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="salary_max" class="form-label">เงินเดือนขั้นสูง</label>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max" 
                                           value="<?php echo $job ? $job['salary_max'] : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">สถานที่ปฏิบัติงาน</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo $job ? htmlspecialchars($job['location']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">ประเภทการจ้างงาน</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="full-time" <?php echo ($job && $job['type'] == 'full-time') ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="part-time" <?php echo ($job && $job['type'] == 'part-time') ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="contract" <?php echo ($job && $job['type'] == 'contract') ? 'selected' : ''; ?>>Contract</option>
                                    <option value="internship" <?php echo ($job && $job['type'] == 'internship') ? 'selected' : ''; ?>>Internship</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="skills" class="form-label">ทักษะที่ต้องการ</label>
                                <select class="form-select" id="skills" name="skills[]" multiple required>
                                    <?php foreach ($skills as $skill): ?>
                                        <option value="<?php echo $skill['id']; ?>" 
                                                <?php echo in_array($skill['id'], $selected_skills) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($skill['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="deadline" class="form-label">วันที่ปิดรับสมัคร</label>
                                <input type="date" class="form-control" id="deadline" name="deadline" 
                                       value="<?php echo $job ? $job['deadline'] : ''; ?>" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="save_job" class="btn btn-primary">
                                    <?php echo $job ? 'บันทึกการแก้ไข' : 'ประกาศรับสมัครงาน'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            $('#skills').select2({
                placeholder: 'เลือกทักษะที่ต้องการ',
                allowClear: true
            });

            // ตรวจสอบเงินเดือน
            $('#jobForm').on('submit', function(e) {
                const salaryMin = parseInt($('#salary_min').val());
                const salaryMax = parseInt($('#salary_max').val());
                
                if (salaryMax < salaryMin) {
                    e.preventDefault();
                    alert('เงินเดือนขั้นสูงต้องมากกว่าเงินเดือนขั้นต่ำ');
                }
            });
        });
    </script>
</body>
</html> 