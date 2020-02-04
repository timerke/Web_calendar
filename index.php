<!DOCTYPE html>
<?php
// Существуют ли в массиве $_POST требуемые ключи, то есть предпринималась ли уже попытка
// входа/регистрации
if (array_key_exists('exit', $_POST) && isset($_POST['exit'])) {
    // Если пользователь был в личном кабинете и нажал кнопку Выйти
    setcookie('user_id', null); // в $_COOKIE['user_id'] сохраняется id пользователя
    setcookie('user_name', null); // в $_COOKIE['user_name'] сохраняется login пользователя
}
if (array_key_exists('login', $_POST)) {
    // Если попытки были
    $login = $_POST['login'];
    $password = $_POST['password'];
    $input = $_POST['input'];
    @$db = new mysqli('localhost', 'calendar_admin', '1234', 'calendar');
    if (mysqli_connect_errno()) {
        // Если не удалось подсоединиться к базе данных веб-календаря
        exit;
    }
    if ($input == 'Зарегистрироваться') {
        // Если пользователь хочет зарегистрироваться
        if (isset($login) && $login != '' && isset($password) && $password != '') {
            // Если введены логин и пароль, формируется запрос, чтобы проверить, есть ли пользователи с таким логином
            $query = "select 1 from users where name='$login'";
            $result = $db->query($query);
            if (!$result) {
                // Если запрос не выполнен, произошла ошибка
            } else if ($result->num_rows == 0) {
                // Не найден ни один пользователь с таким логином, то есть логин свободен и регистрация возможна
                // Формируется запрос, чтобы сохранить данные пользователя в базу данных
                $query = "insert into users values (null,'$login','$password')";
                $result = $db->query($query);
                if (!$result) {
                    // Если данные в базу данных не сохранены, произошла ошибка
                }
                // Запрашивается id только что зарегистрированного пользователя
                $query = "select id, name from users where name='$login'";
                $result = $db->query($query);
                if (!$result) {
                    // Если запрос не выполнен, произошла ошибка
                }
                $result->data_seek(0); // перевод внутреннего указателя объекта в начало
                $row = $result->fetch_assoc();
                setcookie('user_id', $row['id']); // в $_COOKIE['user_id'] сохраняется id пользователя
                setcookie('user_name', $row['name']); // в $_COOKIE['user_name'] сохраняется login пользователя
                $month = date('m'); // определяется текущий месяц
                $year = date('Y'); // определяется текущий год
                setcookie('month_year', $month . ' ' . $year); // в $_COOKIE['month_year'] сохраняются текущие месяц и год
                header('Location: lk.php');
            }
        }
    } else {
        // Если пользователь хочет войти в личный кабинет
        if (isset($login) && $login != '' && isset($password) && $password != '') {
            // Если введены и логин, и пароль, формируется запрос, чтобы проверить правильность введенных логина и пароля
            $query = "select id, name from users where name='$login' and password='$password'";
            $result = $db->query($query);
            if (!$result) {
                // Если запрос не выполнен, произошла ошибка
            } else if ($result->num_rows) {
                // Если логин и пароль введены правильно, то пользователь переходит в личный кабинет
                $result->data_seek(0); // перевод внутреннего указателя объекта в начало
                $row = $result->fetch_assoc();
                setcookie('user_id', $row['id']); // в $_COOKIE['user_id'] сохраняется id пользователя
                setcookie('user_name', $row['name']); // в $_COOKIE['user_name'] сохраняется login пользователя
                $month = date('m'); // определяется текущий месяц
                $year = date('Y'); // определяется текущий год
                setcookie('month_year', $month . ' ' . $year); // в $_COOKIE['month_year'] сохраняются текущие месяц и год
                header('Location: lk.php');
            }
        }
    }
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Календарь</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700,900&display=swap" rel="stylesheet">
    <link type="text/css" href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Календарь</h1>
        </div>
        <div class="content">
            <h2>Вход/регистрация</h2>
            <form action="index.php" method="POST">
                <label for="name">Логин </label>
                <input name="login" id="name"><br>
                <?php
                if (isset($input)) {
                    echo '<p class="warning">Введите логин!</p>';
                }
                ?>
                <label for="password">Пароль </label>
                <input type="password" name="password" id="password"><br>
                <?php
                if (isset($input)) {
                    echo '<p class="warning">Введите пароль!</p>';
                }
                ?>
                <input type="submit" name="input" value="Войти">
                <input type="submit" name="input" value="Зарегистрироваться">
            </form>
        </div>
        <div class="clr"></div>
    </div>
    <div class="footer">
        Copyright &#169; Студент 2019 Все права защищены
    </div>
</body>

</html>