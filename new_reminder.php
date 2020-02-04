<!DOCTYPE html>
<?php
// На какую дату напоминание
$month = explode(' ', $_COOKIE['month_year'])[0]; // месяц даты напоминания
$year = explode(' ', $_COOKIE['month_year'])[1]; // год даты напоминания
if (array_key_exists('planner_day', $_POST)) {
    $day = $_POST['planner_day']; // день даты напоминания
    setcookie('planner_day', $day);
    unset($_POST['planner_day']);
} else {
    $day = $_COOKIE['planner_day']; // день даты напоминания
}
$id = $_COOKIE['user_id']; // id пользователя
$login = $_COOKIE['user_name']; // login пользователя
$status = 0; // если статус 0 - то пользователь только вошел; если 1, то напоминание не было соханено;
// если 2, то напоминание было сохранено
@$db = new mysqli('localhost', 'calendar_admin', '1234', 'calendar');
if (mysqli_connect_errno()) {
    // Если не удалось подсоединиться к базе данных веб-календаря
    exit;
}
// Читается список групп пользователя
$query = "select id, name from user_parties_view where user_id='$id'";
$user_parties = $db->query($query);
if (!$user_parties) {
    // Если запрос не выполнен, произошла ошибка
    exit;
}
if (array_key_exists('save', $_POST) && $_POST['save'] == 'Сохранить') {
    // Если была запущена форма для сохранения, то нужно сохранить напоминание в базу данных
    $reminder = $_POST['reminder']; // что именно надо сохранить
    // Проверяется: напоминание групповое или личное
    $n = $user_parties->num_rows;
    $flag = false; // параметр отвечает за то, какое напоминание сохраняется: личное - false, групповое - true
    if (isset($_POST['party'])) {
        // Если пользователь выбрал групповое напоминание, то формируется запрос
        $query = "insert into parties_planner values (null," . $_POST['party'] . ",'" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) .
            "-" . str_pad($day, 2, '0', STR_PAD_LEFT) . "','" . $reminder . "')";
        $flag = true;
    }
    if (!$flag) {
        // Формируется запрос для сохранения личного напоминания
        $query = "insert into user_planner values (null," . $id . ",'" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) .
            "-" . str_pad($day, 2, '0', STR_PAD_LEFT) . "','" . $reminder . "')";
    }
    $result = $db->query($query);
    if (!$result) {
        // Если запрос не выполнен, произошла ошибка
        exit;
    }
    $status = $result ? 2 : 1; // в базу данных было сохранено напоминание или нет
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Календарь | Новое напоминание</title>
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
            echo "<h2>Напоминание для пользователя $login на дату $day.$month.$year</h2>";
            if ($status == 2) {
                // Если напоминание было сохранено
                echo '<p>Напоминание сохранено.</p>';
            } elseif ($status == 1) {
                // Если напоминание не было сохранено
                echo '<p>Напоминание не было сохранено. Попробуйте позже.</p>';
            } else {
                // Если напоминание еще не сохранялось
                echo '<form action="new_reminder.php" method="POST"><br>' .
                    '<textarea name="reminder" cols="30" rows="5"></textarea><br>';
                // Выводятся группы, в которых состоит пользователь
                $n = $user_parties->num_rows;
                if ($n) {
                    // Если пользователь состоит в группах
                    echo '<div class="user_parties">';
                    echo '<h3>Ваши группы</h3><br/>';
                    $user_parties->data_seek(0); // перевод внутреннего указателя объекта в начало
                    for ($i = 0; $i < $n; $i++) {
                        $row = $user_parties->fetch_assoc();
                        echo "<label for='" . $row['id'] . "'><p>" . $row['name'] . "</p></label>";
                        echo "<input id='" . $row['id'] . "' type='radio' name='party' value='" . $row['id'] . "'><br>";
                    }
                    echo '</div>';
                }
                echo '<input type="submit" name="save" value="Сохранить"><br>' .
                    '</form>';
            }
            ?>
            <form action="lk.php" method="POST">
                <input type="submit" name="return" value="Вернуться">
            </form>
        </div>
        <div class="clr"></div>
    </div>
    <div class="footer">
        Copyright &#169; Студент 2019 Все права защищены
    </div>
</body>

</html>