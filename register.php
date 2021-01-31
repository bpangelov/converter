<?php
require_once("src/db.php");

$username = $password = $confirmPassword = "";
$usernameErr = $passwordErr = $confirmPasswordErr = "";
 
// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $usernameErr = "Please enter a username.";
    } else {
        $db = new DB();
        $connection = $db->getConnection();
        $query = "SELECT id FROM users WHERE username = ?";
        
        if ($stmt = $connection->prepare($query)) {
            $usernameParam = trim($_POST["username"]);
            if ($result = $stmt->execute([$usernameParam])) {
                $username = trim($_POST["username"]);

                $row = $stmt->fetch();
                if ($row) {
                    $usernameErr = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $passwordErr = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $passwordErr = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirmPassword"]))) {
        $confirmPasswordErr = "Please confirm password.";     
    } else {
        $confirmPassword = trim($_POST["confirmPassword"]);
        if (empty($passwordErr) && ($password != $confirmPassword)) {
            $confirmPasswordErr = "Passwords did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($usernameErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
        $query = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if ($stmt = $connection->prepare($query)) {
            $usernameParam = $username;
            $passwordParam = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$usernameParam, $passwordParam])) {
                header("Location: login.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>
    <div class="wrapper">
        <h2>Регистрация</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label for="username">Потребителско име</label>
                <input type="text" name="username" value="<?php echo $username; ?>"></br>
                <span><?php echo $usernameErr; ?></span>
            </div>    
            <div>
                <label for="password">Парола</label>
                <input type="password" name="password" value="<?php echo $password; ?>"> </br>
                <span><?php echo $passwordErr; ?></span>
            </div>
            <div>
                <label for="confirmPassword">Потвърди парола</label>
                <input type="password" name="confirmPassword" value="<?php echo $confirmPassword; ?>"> </br>
                <span><?php echo $confirmPasswordErr; ?></span>
            </div>
            <div>
                <input type="submit" value="Регистрирай се">
            </div>
            <p>Вече имаш регистрация? <a href="login.php">Влез в профила си</a>.</p>
        </form>
    </div>    
</body>
</html>