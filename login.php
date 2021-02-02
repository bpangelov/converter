<?php
require_once("src/db.php");
require_once("src/repositories/UserRepository.php");

session_start();
 
// If user is logged in redirect to converter.
if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
    header("Location: converter.html");
    exit();
}

$db = new DB();
$userRepository = new UserRepository($db->getConnection());

$username = $password = "";
$usernameErr = $passwordErr = "";
 
// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $usernameErr = "Моля въведете потребителско име.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $passwordErr = "Моля въведете парола.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($usernameErr) && empty($passwordErr)) {
        $user = $userRepository->getUser($username, true);

        if (!$user) {
            $usernameErr = "Потребителското име не съществува.";
        } else {
            $hashedPassword = $user["password"];
            if (password_verify($password, $hashedPassword)) {
                $_SESSION["logged"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["username"] = $user["username"];

                header("Location: converter.html");
            } else {
                $passwordErr = "Грешна парола.";
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