<?php
require_once("src/db.php");
require_once("src/repositories/UserRepository.php");

$db = new DB();
$userRepository = new UserRepository($db->getConnection());

$username = $password = $confirmPassword = "";
$usernameErr = $passwordErr = $confirmPasswordErr = "";
 
// When form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $usernameErr = "Моля въведете потребителско име.";
    } else {
        $user = $userRepository->getUser(trim($_POST["username"]));
        if ($user) {
            $usernameErr = "Потребителското име е заето.";
        } else {
            $username = trim($_POST["username"]);
        }
    }
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $passwordErr = "Моля въведете парола.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $passwordErr = "Паролата трябва да е поне 6 символа.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirmPassword"]))) {
        $confirmPasswordErr = "Моля потвърдете паролата.";     
    } else {
        $confirmPassword = trim($_POST["confirmPassword"]);
        if (empty($passwordErr) && ($password != $confirmPassword)) {
            $confirmPasswordErr = "Паролите не съвпадат.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($usernameErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
        $userRepository->saveUser($username, $password);
         
        header("Location: login.php");
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