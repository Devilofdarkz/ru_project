<?php
include 'db.php';
include 'header.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Fetch all approved files for viewing
$files_sql = "
    SELECT f.*, c.course_name 
    FROM files f
    JOIN courses c ON f.course_id = c.id
    WHERE f.status = 'approved'
    ORDER BY c.course_name, f.id DESC
";
$files_stmt = $pdo->query($files_sql);
$approved_files = $files_stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize files by course
$files_by_course = [];
foreach ($approved_files as $file) {
    $course = $file['course_name'];
    $files_by_course[$course][] = $file;
}

// Get the course list for uploading
$courses_sql = "SELECT * FROM courses ORDER BY course_name";
$courses_stmt = $pdo->query($courses_sql);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file upload
if (isset($_POST['upload_file'])) {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['user_id'];

    // Check file is provided and no error
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_tmp  = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];
        $file_ext  = pathinfo($file_name, PATHINFO_EXTENSION);

        // Define file type based on extension
        $file_type = 'pdf';
        if (in_array(strtolower($file_ext), ['jpg','jpeg','png','gif'])) {
            $file_type = 'image';
        }

        // Create a unique file path and move the file to uploads folder
        $new_path = "uploads/" . time() . "_" . $file_name;
        move_uploaded_file($file_tmp, $new_path);

        // Insert record into files table (status will be 'pending')
        $sql = "INSERT INTO files (file_name, file_path, file_type, course_id, user_id, status) 
                VALUES (:file_name, :file_path, :file_type, :course_id, :user_id, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':file_name' => $file_name,
            ':file_path' => $new_path,
            ':file_type' => $file_type,
            ':course_id' => $course_id,
            ':user_id'   => $user_id
        ]);

        header("Location: student_dashboard.php?msg=File uploaded and awaiting approval");
        exit;
    }
}
?>

<h2>Student Dashboard</h2>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-info"><?php echo $_GET['msg']; ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-6">
    <h4>Upload a New File</h4>
    <form method="POST" action="student_dashboard.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="course_id">Select Course</label>
            <select class="form-control" id="course_id" name="course_id" required>
                <option value="">-- Choose Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>">
                        <?php echo $course['course_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="file">Choose File (PDF/Image)</label>
            <input 
              type="file" 
              class="form-control-file" 
              id="file" 
              name="file" 
              accept=".pdf, .jpg, .jpeg, .png, .gif"
              required
            >
        </div>
        <button type="submit" name="upload_file" class="btn btn-primary">Upload</button>
    </form>
  </div>
</div>

<hr>

<h4>Approved Files by Course</h4>
<?php if (!empty($files_by_course)): ?>
    <?php foreach ($files_by_course as $course => $files): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <?php echo htmlspecialchars($course); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($files as $file): ?>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($file['file_name']); ?>
                                    </h5>
                                    <p class="card-text">
                                      File Type: <?php echo htmlspecialchars($file['file_type']); ?>
                                    </p>
                                    <a href="<?php echo $file['file_path']; ?>" target="_blank" 
                                       class="btn btn-success btn-sm">
                                        Download/View
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No approved files available yet.</p>
<?php endif; ?>

<?php include 'footer.php'; ?>
