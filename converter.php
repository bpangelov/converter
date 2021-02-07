<?php

session_start();

if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
    $loginControls = '
        <li class="nav-item">
            <p class="navbar-text">' . $_SESSION["username"] . '</p>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">Изход</a>
        </li>
    ';
} else {
    $loginControls = '
        <li class="nav-item">
            <a class="nav-link" href="login.php">Вход</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="register.php">Регистрация</a>
        </li>
    ';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/converter.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Converter</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="historyDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    История
                    </a>
                    <div class="dropdown-menu scrollable-menu" aria-labelledby="historyDropdown" id="historyItems"></div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="sharedDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Споделени
                    </a>
                    <div class="dropdown-menu scrollable-menu" aria-labelledby="sharedDropdown" id="sharedItems"></div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="configsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Конфигурации
                    </a>
                    <div class="dropdown-menu scrollable-menu" aria-labelledby="configsDropdown" id="configs"></div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="sharedConfigsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Споделени конфигурации
                    </a>
                    <div class="dropdown-menu scrollable-menu" aria-labelledby="sharedConfigsDropdown" id="sharedConfigs"></div>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <?php 
                    echo $loginControls;
                ?>
            </ul>
            </form>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-5">
                <textarea class="form-control" rows="25" placeholder="Вход" id="converterInput"></textarea>
            </div>
            <div class="col-md-2">
                <label for="inputFormat">Формат на входа и изхода</label>
                <div class="input-group mb-3">
                    <select class="form-control" id="inputFormat">
                        <option>json</option>
                        <option>yaml</option>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">към</span>
                    </div>
                    <select class="form-control" id="outputFormat">
                        <option>json</option>
                        <option>yaml</option>
                    </select>
                </div>
                <label for="tabulation">Спейсове в табулация</label>
                <div class="input-group mb-3">
                    <input class="form-control" type="number" id="tabulation" value=4>
                </div>
                <label for="propertyCase">Формат на полетата</label>
                <div class="input-group mb-3">
                    <select class="form-control" id="propertyCase">
                        <option value="1">None</option>
                        <option value="2">Snake case</option>
                        <option value="3">Camel case</option>
                    </select>
                </div>
                <label for="configName">Име на конфигурация</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="configName"/>
                </div>
                <label for="transformationName">Име на трансформация</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="transformationName"/>
                </div>
                <div class="input-group mb-3">
                    <label><input type="checkbox" onclick="saveCheck();" class="checkbox" id="saveCheck"/> Запази в историята</label>
                </div>
                <button type="button" class="btn btn-primary mx-auto d-block" id="convert-btn" onclick="convert()">Конвертиране</button>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" rows="25" placeholder="Изход" id="converterOutput"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <input type="file" class="form-control-file" id="inputFile" onchange='onChooseFile(event, onFileLoad.bind(this, "converterInput")); this.value=null'>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="js/converter.js"></script>
</body>
</html>