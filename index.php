
<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "portfolio";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = trim($_POST["id"]);
    $password = $_POST["password"];

   

    // Find user
    $sql = "SELECT id, name, pass FROM login WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

       

        // Check password
        if ($password === $user["pass"]) {

            // Login successful
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["name"];

            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Invalid email or password.";
        }

    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">

    <title>Login</title>

    
</head>

<body>

    <div class="login-box">

        <h2>Login</h2>

        <?php if ($error != ""): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-box">
                <label>ID</label>

                <input
                    type="number"
                    name="id"
                    placeholder="Enter your ID"
                    required
                >
            </div>

            <div class="input-box">
                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit">
                Login
            </button>

        </form>

    </div>

</body>
</html>



