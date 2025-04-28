<?php
include 'db.php';
include 'header.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// --- Add New Course ---
if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $sql = "INSERT INTO courses (course_name) VALUES (:course_name)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':course_name' => $course_name]);
    header("Location: admin_dashboard.php?msg=Course added");
    exit;
}

// --- Handle Admin File Upload ---
// Admin uploads are auto-approved.
if (isset($_POST['upload_admin_file'])) {
    $course_id = $_POST['course_id_admin'];
    $user_id   = $_SESSION['user_id'];

    if (isset($_FILES['admin_file']) && $_FILES['admin_file']['error'] == 0) {
        $file_tmp  = $_FILES['admin_file']['tmp_name'];
        $file_name = $_FILES['admin_file']['name'];
        $file_ext  = pathinfo($file_name, PATHINFO_EXTENSION);

        // Determine file type (pdf or image)
        $file_type = 'pdf';
        if (in_array(strtolower($file_ext), ['jpg','jpeg','png','gif'])) {
            $file_type = 'image';
        }

        // Generate a unique file name and move the file
        $new_path = "uploads/" . time() . "_" . $file_name;
        move_uploaded_file($file_tmp, $new_path);

        // Insert the record with status 'approved'
        $sql = "INSERT INTO files (file_name, file_path, file_type, course_id, user_id, status) 
                VALUES (:file_name, :file_path, :file_type, :course_id, :user_id, 'approved')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':file_name' => $file_name,
            ':file_path' => $new_path,
            ':file_type' => $file_type,
            ':course_id' => $course_id,
            ':user_id'   => $user_id
        ]);

        header("Location: admin_dashboard.php?msg=Admin file uploaded and approved");
        exit;
    }
}

// --- Process Approval & Disapproval for Pending Files ---
// Only pending files are actionable.
if (isset($_GET['approve'])) {
    $file_id = $_GET['approve'];
    $sql = "UPDATE files SET status='approved' WHERE id=:id AND status='pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $file_id]);
    header("Location: admin_dashboard.php?msg=File approved");
    exit;
}

if (isset($_GET['disapprove'])) {
    $file_id = $_GET['disapprove'];

    // Retrieve file info and then remove it permanently.
    $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = :id AND status='pending'");
    $stmt->execute([':id' => $file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
         $file_path = $file['file_path'];
         if (file_exists($file_path)) {
              unlink($file_path);
         }
         $stmt = $pdo->prepare("DELETE FROM files WHERE id = :id");
         $stmt->execute([':id' => $file_id]);
         header("Location: admin_dashboard.php?msg=File disapproved and removed");
         exit;
    }
}

// --- Retrieve Course List (for upload and course addition) ---
$courses_sql = "SELECT * FROM courses ORDER BY course_name";
$courses_stmt = $pdo->query($courses_sql);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Retrieve Pending Files ---
$pending_files_sql = "
    SELECT f.*, c.course_name, u.name 
    FROM files f
    JOIN courses c ON f.course_id = c.id
    JOIN users u ON f.user_id = u.id
    WHERE f.status = 'pending'
    ORDER BY f.id DESC
";
$pending_files_stmt = $pdo->query($pending_files_sql);
$pending_files = $pending_files_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Retrieve Approved Files ---
$approved_files_sql = "
    SELECT f.*, c.course_name, u.name 
    FROM files f
    JOIN courses c ON f.course_id = c.id
    JOIN users u ON f.user_id = u.id
    WHERE f.status = 'approved'
    ORDER BY f.id DESC
";
$approved_files_stmt = $pdo->query($approved_files_sql);
$approved_files = $approved_files_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Admin Dashboard</h2>
<?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
<?php endif; ?>

<!-- Card: Add New Course -->
<div class="card mb-4">
    <div class="card-header">Add New Course</div>
    <div class="card-body">
        <form method="POST" action="admin_dashboard.php">
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" required>
            </div>
            <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
        </form>
    </div>
</div>

<!-- Card: Upload File (Admin) -->
<div class="card mb-4">
    <div class="card-header">Upload File (Admin)</div>
    <div class="card-body">
        <form method="POST" action="admin_dashboard.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="course_id_admin">Select Course</label>
                <select class="form-control" id="course_id_admin" name="course_id_admin" required>
                    <option value="">-- Choose Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="admin_file">Choose File (PDF/Image)</label>
                <input type="file" class="form-control-file" id="admin_file" name="admin_file" accept=".pdf, .jpg, .jpeg, .png, .gif" required>
            </div>
            <button type="submit" name="upload_admin_file" class="btn btn-primary">Upload File</button>
        </form>
    </div>
</div>

<!-- Section: Pending File Requests -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">Pending File Requests</div>
    <div class="card-body">
        <?php if (count($pending_files) > 0): ?>
            <table class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Course</th>
                        <th>Uploaded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_files as $file): ?>
                        <tr>
                            <td>
                                <a href="<?php echo $file['file_path']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                            <td><?php echo htmlspecialchars($file['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($file['name']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?approve=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                <a href="admin_dashboard.php?disapprove=<?php echo $file['id']; ?>" class="btn btn-warning btn-sm">Disapprove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending files for approval.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Section: Approved Files (Display Only) -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">Approved Files</div>
    <div class="card-body">
        <?php if (count($approved_files) > 0): ?>
            <table class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Course</th>
                        <th>Uploaded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_files as $file): ?>
                        <tr>
                            <td>
                                <a href="<?php echo $file['file_path']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                            <td><?php echo htmlspecialchars($file['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($file['name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No approved files available.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
