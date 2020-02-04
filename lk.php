<!DOCTYPE html>
<?php
if (!array_key_exists('user_id', $_COOKIE)) {
    // Если пользователь незаконно пытается влезть на страницу
    exit;
}
// Определяются месяц и год, которые были установлены на календаре пользователя в личном кабинете
$month = explode(' ', $_COOKIE['month_year'])[0];
$year = explode(' ', $_COOKIE['month_year'])[1];
if (array_key_exists('leaf_over', $_POST) && $_POST['leaf_over'] == '<<') {
    // Если пользователь нажал кнопку формы << и хочет перелистнуть календарь на месяц назад
    $month--;
    if ($month == 0) {
        $month = 12;
        $year--;
    }
    unset($_POST['leaf_over']);
} else if (array_key_exists('leaf_over', $_POST) && $_POST['leaf_over'] == '>>') {
    // Если пользователь нажал кнопку формы >> и хочет перелистнуть календарь на месяц вперед
    $month++;
    if ($month == 13) {
        $month = 1;
        $year++;
    }
    unset($_POST['leaf_over']);
}
$id = $_COOKIE['user_id']; // id пользователя
$login = $_COOKIE['user_name']; // логин пользователя
@$db = new mysqli('localhost', 'calendar_admin', '1234', 'calendar');
if (mysqli_connect_errno()) {
    // Если не удалось подсоединиться к базе данных веб-календаря
    exit;
}
// Читается личный календарь пользователя
$query = "select event_date, event from user_planner where user_id='$id'";
$user_planner = $db->query($query);
if (!$user_planner) {
    // Если запрос не выполнен, произошла ошибка
    exit;
}
// Читается список групп пользователя из представления читается список групп пользователя
$query = "select name from user_parties_view where user_id='$id'";
$user_parties = $db->query($query);
if (!$user_parties) {
    // Если запрос не выполнен, произошла ошибка
    exit;
}
// Читаются групповые календари пользователя
$query = "select event_date, event from parties_planner where party_id in (select id from user_parties_view where user_id='$id')";
$parties_planner = $db->query($query);
if (!$parties_planner) {
    // Если запрос не выполнен, произошла ошибка
    //exit;
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Календарь | Личный кабинет</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700,900&display=swap" rel="stylesheet">
    <link type="text/css" href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Кадендарь</h1>
        </div>
        <div class="content">
            <?php
            echo "<h2>Личный кабинет пользователя $login</h2>";
            echo '<h3>Календарь</h3>';
            include('func.php'); // подключается файл с функцией для рисования календаря
            calendar($month, $year, $user_planner, $parties_planner); // рисуется календарь
            $month_year = $month . ' ' . $year;
            setcookie("month_year", $month_year); // сохраняем месяц и год, для которых нарисован календарь
            ?>
            <form action="lk.php" method="POST">
                <input name="leaf_over" type="submit" value="<<">
                <input name="leaf_over" type="submit" value=">>">
            </form>
            <?php
            // Проверяются личные напоминания пользователя
            $n = $user_planner->num_rows;
            if ($n) {
                // Если у пользователя есть личные напоминания
                echo '<div class="user_reminder">';
                echo '<h3>Ваши личные напоминания</h3><br>';
                $user_planner->data_seek(0); // устанавливаем внутренний указатель объекта на его начало
                for ($i = 0; $i < $n; $i++) {
                    $row = $user_planner->fetch_assoc();
                    echo "<p><strong>" . $row['event_date'] . "</strong>: " . $row['event'] . "</p><br>";
                }
                echo '</div>';
            }
            // Проверяются группы, в которых состоит пользователь
            $n = $user_parties->num_rows;
            if ($n) {
                // Если пользователь состоит в группах
                echo '<div class="parties">';
                echo '<h3>Ваши группы</h3><br>';
                $user_parties->data_seek(0); // устанавливаем внутренний указатель объекта на его начало
                for ($i = 0; $i < $n; $i++) {
                    $row = $user_parties->fetch_assoc();
                    echo "<p>" . $row['name'] . "</p><br>";
                }
                echo '</div>';
                // Проверяются групповые напоминания пользователя
                $n = $parties_planner->num_rows;
                if ($n) {
                    // Если у пользователя есть групповые напоминания
                    echo '<div class="parties_reminder">';
                    echo '<h3>Ваши групповые напоминания</h3><br>';
                    $parties_planner->data_seek(0); // устанавливаем внутренний указатель объекта на его начало
                    for ($i = 0; $i < $n; $i++) {
                        $row = $parties_planner->fetch_assoc();
                        echo "<p><strong>" . $row['event_date'] . "</strong>: " . $row['event'] . "</p><br>";
                    }
                    echo '</div>';
                }
            }
            ?>
            <form action="parties.php" method="POST">
                <input type="submit" name="parties" value="Группы">
            </form>
            <form action="index.php" method="POST">
                <input type="submit" name="exit" value="Выйти">
            </form>
        </div>
        <div class="clr"></div>
    </div>
    <div class="footer">
        Copyright &#169; Студент 2019 Все права защищены
    </div>
</body>

</html>