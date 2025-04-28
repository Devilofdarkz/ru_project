<?php
include 'db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];

    // Check if user exists
    $sql = "SELECT * FROM users WHERE roll_no = :roll_no AND user_type='student'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':roll_no' => $roll_no]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] === $password) {
        // Set session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        header("Location: student_dashboard.php");
        exit;
    } else {
        $error = "Invalid Roll No or Password.";
    }
}
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2>Student Login</h2>
        <?php if (!empty($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo $_GET['message']; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="roll_no">University Roll No</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="roll_no" 
                  name="roll_no" 
                  required
                >
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                  type="password" 
                  class="form-control" 
                  id="password" 
                  name="password" 
                  required
                >
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="register.php" class="btn btn-link">New student? Register here</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
