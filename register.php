<?php
include 'db.php';
include 'header.php';

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'];
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];

    // Insert into users table (no hashing as requested)
    $sql = "INSERT INTO users (name, roll_no, password, user_type) 
            VALUES (:name, :roll_no, :password, 'student')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':roll_no' => $roll_no,
        ':password' => $password
    ]);

    // Redirect to login
    header("Location: login.php?message=Registered successfully!");
    exit;
}
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <h2>Student Registration</h2>
        <form method="POST" action="register.php" class="needs-validation">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="name" 
                  name="name" 
                  required
                >
            </div>
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
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
