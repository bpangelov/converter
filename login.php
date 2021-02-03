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
	} else {
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
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Вход</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/login.css">
</head>

<body class="login-page">
	<section class="h-100">
		<div class="container h-100">
			<div class="row justify-content-md-center h-100">
				<div class="card-wrapper">
                    <div class="brand"></div>
					<div class="card fat">
						<div class="card-body">
							<h4 class="card-title">Вход</h4>
							<form method="POST" novalidate="">
								<div class="form-group">
									<label for="username">Потребителско име</label>
									<input id="username" type="text" class="form-control<?php echo $usernameErr ? ' is-invalid' : ''?>" 
										name="username" value="<?php echo $username; ?>" required autofocus>
									<div class="invalid-feedback">
										<?php echo $usernameErr; ?>
									</div>
								</div>

								<div class="form-group">
									<label for="password">Парола</label>
									<input id="password" type="password" class="form-control<?php echo $passwordErr ? ' is-invalid' : ''?>" name="password" required>
								    <div class="invalid-feedback">
										<?php echo $passwordErr; ?>
							    	</div>
								</div>

								<div class="form-group m-0">
									<button type="submit" class="btn btn-primary btn-block">
										Вход
									</button>
								</div>
								<div class="mt-4 text-center">
									Нямаш акаунт? <a href="register.php">Регистрирай се</a>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>