<?php
require_once("src/db.php");

session_start();
 
// If user is logged in redirect to converter.
if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
    header("Location: converter.html");
    exit();
}

$username = $password = "";
$usernameErr = $passwordErr = "";
 
// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $usernameErr = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $passwordErr = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($usernameErr) && empty($passwordErr)) {
        $db = new DB();
        $connection = $db->getConnection();
        $query = "SELECT id, username, password FROM users WHERE username = ?";
        
        if ($stmt = $connection->prepare($query)) {
            $usernameParam = trim($_POST["username"]);
            if ($result = $stmt->execute([$usernameParam])) {
				$row = $stmt->fetch();
				
				$id = $row["id"];
				$username = $row["username"];
				$hashedPassword = $row["password"];

				if (!$row) {
					$usernameErr = "No account found with that username.";
				} else {
					if (password_verify($password, $hashedPassword)) {
						session_start();

						$_SESSION["logged"] = true;
						$_SESSION["id"] = $id;
						$_SESSION["username"] = $username;                            

						header("Location: converter.html");
					} else {
						$passwordErr = "The password you entered was not valid.";
					}
				}
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>
<body>
    <div class="wrapper">
        <h2>Вход</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label for="username">Потребителско име</label>
                <input type="text" name="username" value="<?php echo $username; ?>"></br>
                <span><?php echo $usernameErr; ?></span>
            </div>    
            <div>
                <label for="password">Парола</label>
                <input type="password" name="password"></br>
                <span><?php echo $passwordErr; ?></span>
            </div>
            <div">
                <input type="submit" value="Вход">
            </div>
            <p>Нямаш акаунт? <a href="register.php">Регистрирай се</a>.</p>
        </form>
    </div>    
</body>
</html>