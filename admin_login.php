<?php
include 'db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];

    // Check if user is admin
    $sql = "SELECT * FROM users WHERE roll_no = :roll_no AND user_type='admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':roll_no' => $roll_no]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && $admin['password'] === $password) {
        // Set admin session
        $_SESSION['user_id']   = $admin['id'];
        $_SESSION['user_name'] = $admin['name'];
        $_SESSION['user_type'] = $admin['user_type'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid Admin Roll No or Password.";
    }
}
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2>Admin Login</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="admin_login.php">
            <div class="form-group">
                <label for="roll_no">Admin Username</label>
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
            <button type="submit" class="btn btn-primary">Login as Admin</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
