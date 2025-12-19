<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$layout_dir = __DIR__ . '/../layout/';
$active_id = $_GET['edit_id'] ?? null;
$msg = "";

// =========================================
// 1. LOGIKA PROSES (SIMPAN / UPDATE)
// =========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_all'])) {
    try {
        $pdo->beginTransaction();
        $id = $_POST['training_id'] ?? null;
        
        $data = [
            $_POST['title'], $_POST['trainer_id'], $_POST['held_by'], 
            $_POST['activity_id'], $_POST['skill_id'], $_POST['training_date'], 
            $_POST['start_time'], $_POST['finish_time'], $_POST['fee'], $_POST['is_certified']
        ];

        if ($id) {
            // Update Header
            $sql = "UPDATE trainings SET title=?, trainer_id=?, held_by=?, activity_id=?, skill_id=?, training_date=?, start_time=?, finish_time=?, fee=?, is_certified=? WHERE id=?";
            $data[] = $id;
            $pdo->prepare($sql)->execute($data);
            // Hapus peserta lama untuk sync ulang
            $pdo->prepare("DELETE FROM training_participants WHERE training_id = ?")->execute([$id]);
            $training_id = $id;
        } else {
            // Insert Baru
            $sql = "INSERT INTO trainings (title, trainer_id, held_by, activity_id, skill_id, training_date, start_time, finish_time, fee, is_certified) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $training_id = $pdo->lastInsertId();
        }

        // Simpan Peserta dari Table
        if (isset($_POST['emp_ids']) && is_array($_POST['emp_ids'])) {
            $stmt_p = $pdo->prepare("INSERT INTO training_participants (training_id, employee_id) VALUES (?, ?)");
            foreach ($_POST['emp_ids'] as $emp_id) {
                $stmt_p->execute([$training_id, $emp_id]);
            }
        }

        $pdo->commit();
        write_log($pdo, "Mengelola data training: " . $_POST['title']);
        header("Location: index.php?edit_id=$training_id&msg=success");
        exit();
    } catch (Exception $e) { $pdo->rollBack(); $msg = $e->getMessage(); }
}

// =========================================
// 2. DATA FETCHING
// =========================================
$trainings  = $pdo->query("SELECT * FROM trainings ORDER BY training_date DESC")->fetchAll();
$trainers   = $pdo->query("SELECT * FROM master_trainers ORDER BY trainer_name ASC")->fetchAll();
$activities = $pdo->query("SELECT * FROM attr_activities ORDER BY activity_name ASC")->fetchAll();
$skills     = $pdo->query("SELECT * FROM attr_skills ORDER BY skill_name ASC")->fetchAll();

// Data Training yang sedang diedit
$current = null;
$current_participants = [];
if ($active_id) {
    $st = $pdo->prepare("SELECT * FROM trainings WHERE id = ?");
    $st->execute([$active_id]);
    $current = $st->fetch();

    $st_p = $pdo->prepare("SELECT e.*, o.org_name, p.position_name, l.level_name 
                           FROM training_participants tp 
                           JOIN employees e ON tp.employee_id = e.id 
                           LEFT JOIN master_organizations o ON e.org_id = o.id 
                           LEFT JOIN master_job_positions p ON e.position_id = p.id 
                           LEFT JOIN master_job_levels l ON e.level_id = l.id 
                           WHERE tp.training_id = ?");
    $st_p->execute([$active_id]);
    $current_participants = $st_p->fetchAll();
}

// Data Karyawan untuk Search
$all_employees = $pdo->query("SELECT e.*, o.org_name, p.position_name, l.level_name FROM employees e 
                              LEFT JOIN master_organizations o ON e.org_id = o.id 
                              LEFT JOIN master_job_positions p ON e.position_id = p.id 
                              LEFT JOIN master_job_levels l ON e.level_id = l.id 
                              WHERE e.status = 'Active'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Training Data - HC-APP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .split-container { display: flex; gap: 20px; align-items: flex-start; }
        .training-list-col { width: 350px; background: white; border-radius: 15px; border: 1px solid #e2e8f0; height: calc(100vh - 120px); overflow-y: auto; }
        .editor-col { flex: 1; }
        
        .training-item { padding: 15px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: 0.3s; }
        .training-item:hover { background: #f8fafc; }
        .training-item.active { background: #eff6ff; border-left: 4px solid var(--primary-blue); }
        
        .form-section { background: white; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
        .participant-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        .participant-table th { background: #f8fafc; text-align: left; padding: 10px; color: #64748b; text-transform: uppercase; font-size: 11px; }
        .participant-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; }
        
        #empSearchList { position: absolute; width: 100%; z-index: 100; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; display: none; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .search-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
        .search-item:hover { background: #f0f7ff; }
    </style>
</head>
<body>
    <?php include $layout_dir . 'sidebar.php'; include $layout_dir . 'header.php'; ?>

    <main class="main-content">
        <div class="split-container">
            
            <div class="training-list-col">
                <div style="padding: 15px; border-bottom: 2px solid #f1f5f9; position: sticky; top: 0; background: white; z-index: 10;">
                    <a href="index.php" class="btn-primary" style="display: block; text-align: center; text-decoration: none;">+ Tambah Training Baru</a>
                </div>
                <?php foreach($trainings as $t): ?>
                    <div class="training-item <?= ($active_id == $t['id']) ? 'active' : '' ?>" onclick="location.href='?edit_id=<?= $t['id'] ?>'">
                        <div style="font-size: 11px; color: #64748b;"><?= date('d M Y', strtotime($t['training_date'])) ?></div>
                        <div style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($t['title']) ?></div>
                        <div style="font-size: 12px; color: #94a3b8;"><?= htmlspecialchars($t['held_by']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="editor-col">
                <form method="POST" id="mainForm">
                    <input type="hidden" name="training_id" value="<?= $current['id'] ?? '' ?>">
                    
                    <div class="form-section">
                        <h4 style="margin-top: 0; color: var(--primary-blue);"><?= $current ? 'Edit Training' : 'Input Training Baru' ?></h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div class="form-group"><label>Training Title</label><input type="text" name="title" class="form-control" value="<?= $current['title'] ?? '' ?>" required></div>
                            <div class="form-group"><label>Trainer</label>
                                <select name="trainer_id" class="form-control" required><option value="">-- Pilih --</option>
                                <?php foreach($trainers as $tr): ?><option value="<?= $tr['id'] ?>" <?= (isset($current['trainer_id']) && $current['trainer_id'] == $tr['id']) ? 'selected' : '' ?>><?= $tr['trainer_name'] ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="form-group"><label>Held By</label><input type="text" name="held_by" class="form-control" value="<?= $current['held_by'] ?? '' ?>" required></div>
                            <div class="form-group" style="display: flex; gap: 10px;">
                                <div style="flex: 1;"><label>Activities</label><select name="activity_id" class="form-control" required>
                                <?php foreach($activities as $ac): ?><option value="<?= $ac['id'] ?>" <?= (isset($current['activity_id']) && $current['activity_id'] == $ac['id']) ? 'selected' : '' ?>><?= $ac['activity_name'] ?></option><?php endforeach; ?>
                                </select></div>
                                <div style="flex: 1;"><label>Skill</label><select name="skill_id" class="form-control" required>
                                <?php foreach($skills as $sk): ?><option value="<?= $sk['id'] ?>" <?= (isset($current['skill_id']) && $current['skill_id'] == $sk['id']) ? 'selected' : '' ?>><?= $sk['skill_name'] ?></option><?php endforeach; ?>
                                </select></div>
                            </div>
                            <div class="form-group"><label>Date</label><input type="date" name="training_date" class="form-control" value="<?= $current['training_date'] ?? '' ?>" required></div>
                            <div class="form-group" style="display: flex; gap: 10px;">
                                <div style="flex: 1;"><label>Start</label><input type="time" name="start_time" class="form-control" value="<?= $current['start_time'] ?? '' ?>" required></div>
                                <div style="flex: 1;"><label>Finish</label><input type="time" name="finish_time" class="form-control" value="<?= $current['finish_time'] ?? '' ?>" required></div>
                            </div>
                            <div class="form-group"><label>Fee</label><input type="number" name="fee" class="form-control" value="<?= $current['fee'] ?? '0' ?>"></div>
                            <div class="form-group"><label>Certification</label>
                                <select name="is_certified" class="form-control">
                                    <option value="No" <?= (isset($current['is_certified']) && $current['is_certified'] == 'No') ? 'selected' : '' ?>>No</option>
                                    <option value="Yes" <?= (isset($current['is_certified']) && $current['is_certified'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                                </select></div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 style="margin-top: 0;">Peserta Training</h4>
                        <div style="position: relative;">
                            <input type="text" id="empSearch" class="form-control" placeholder="Cari Nama / NIK Karyawan...">
                            <div id="empSearchList">
                                <?php foreach($all_employees as $emp): ?>
                                    <div class="search-item" 
                                         data-json='<?= json_encode($emp) ?>'>
                                        <strong><?= $emp['employee_id'] ?></strong> - <?= htmlspecialchars($emp['full_name']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <table class="participant-table" id="participantTable">
                            <thead>
                                <tr><th>NIK</th><th>Nama</th><th>Org</th><th>Position</th><th>Level</th><th>Status</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($current_participants as $cp): ?>
                                    <tr id="row-<?= $cp['id'] ?>">
                                        <td><?= $cp['employee_id'] ?><input type="hidden" name="emp_ids[]" value="<?= $cp['id'] ?>"></td>
                                        <td><strong><?= htmlspecialchars($cp['full_name']) ?></strong></td>
                                        <td><?= $cp['org_name'] ?></td>
                                        <td><?= $cp['position_name'] ?></td>
                                        <td><?= $cp['level_name'] ?></td>
                                        <td><?= $cp['status'] ?></td>
                                        <td><a href="javascript:void(0)" onclick="this.parentElement.parentElement.remove()" style="color:red">Hapus</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="save_all" class="btn-primary" style="margin-top: 20px; width: 100%; padding: 15px;">SIMPAN PERUBAHAN TRAINING</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const empSearch = document.getElementById('empSearch');
        const empList = document.getElementById('empSearchList');
        const tableBody = document.querySelector('#participantTable tbody');

        empSearch.addEventListener('focus', () => empList.style.display = 'block');
        empSearch.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.search-item').forEach(item => {
                item.style.display = item.innerText.toLowerCase().includes(val) ? 'block' : 'none';
            });
        });

        document.querySelectorAll('.search-item').forEach(item => {
            item.addEventListener('click', function() {
                const data = JSON.parse(this.getAttribute('data-json'));
                
                // Cek duplikasi
                if (document.querySelector(`input[value="${data.id}"]`)) {
                    alert('Karyawan sudah ada di daftar!'); return;
                }

                const row = `<tr>
                    <td>${data.employee_id}<input type="hidden" name="emp_ids[]" value="${data.id}"></td>
                    <td><strong>${data.full_name}</strong></td>
                    <td>${data.org_name}</td>
                    <td>${data.position_name}</td>
                    <td>${data.level_name}</td>
                    <td>${data.status}</td>
                    <td><a href="javascript:void(0)" onclick="this.parentElement.parentElement.remove()" style="color:red">Hapus</a></td>
                </tr>`;
                tableBody.insertAdjacentHTML('beforeend', row);
                empSearch.value = '';
                empList.style.display = 'none';
            });
        });

        document.addEventListener('click', (e) => {
            if (!empSearch.contains(e.target) && !empList.contains(e.target)) empList.style.display = 'none';
        });
    </script>
    <?php include $layout_dir . 'footer.php'; ?>
</body>
</html>