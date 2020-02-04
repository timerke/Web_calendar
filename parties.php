<!DOCTYPE html>
<?php
$id = $_COOKIE['user_id']; // id пользователя
$login = $_COOKIE['user_name']; // login пользователя
@$db = new mysqli('localhost', 'calendar_admin', '1234', 'calendar');
if (mysqli_connect_errno()) {
    // Если не удалось подсоединиться к базе данных веб-календаря
    exit;
}
if (array_key_exists('register', $_POST) && $_POST['register'] == 'Зарегистрироваться') {
    // Если была запущена форма для регистрации в группе
    $party_id = $_POST['party']; // id группы, в которой нужно зарегистрироваться
    // Формируется запрос для вступления в группу
    $query = "insert into users_parties values ($id, $party_id)";
    $result = $db->query($query);
    if (!$result) {
        // Если запрос не выполнен, произошла ошибка
        exit;
    }
}
if (array_key_exists('create', $_POST) && $_POST['create'] == 'Создать') {
    // Если была запущена форма для создания новой группы
    $new_party = $_POST['new_party']; // название новой группы
    // Формируется запрос для проверки, что групп с таким названием нет
    $query = "select 1 from parties where name='$new_party'";
    $result = $db->query($query);
    if (!$result) {
        // Если запрос не выполнен, произошла ошибка
        exit;
    } else if ($result->num_rows == 0) {
        // Не найдено ни одной группы с таким названием, группу можно создать.
        // Формируется соответствующий запрос
        $query = "insert into parties values (null, '$new_party')";
        $result = $db->query($query);
        if (!$result) {
            // Если запрос не выполнен, произошла ошибка
            exit;
        }
    }
}
// Читается список групп, в которых пользователь не зарегистрирован
$query = "select * from parties where id not in (select id from user_parties_view where user_id='$id')";
$parties = $db->query($query);
if (!$parties) {
    // Если запрос не выполнен, произошла ошибка
    exit;
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Календарь | Группы</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700,900&display=swap" rel="stylesheet">
    <link type="text/css" href="style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Кадендарь</h1>
        </div>
        <div class="content">
            <form action="parties.php" method="POST">
                <?php
                $n = $parties->num_rows;
                if ($n) {
                    // Есть группы, куда может вступить пользователь
                    echo '<div class="parties">';
                    echo '<h2>Доступные группы</h2>';
                    $parties->data_seek(0); // устанавливаем внутренний указатель объекта на его начало
                    for ($i = 0; $i < $n; $i++) {
                        $row = $parties->fetch_assoc();
                        echo "<label for='" . $row['id'] . "'><p>" . $row['name'] . "</p></label>";
                        echo "<input id='" . $row['id'] . "' type='radio' name='party' value='" . $row['id'] . "'><br>";
                    }
                    echo '<input type="submit" name="register" value="Зарегистрироваться">';
                    echo '</div>';
                }
                ?>
                <h2>Создайте группу</h2>
                <label for="new_party">Название группы </label>
                <input type="text" name="new_party" id="new_party"><br>
                <input type="submit" name="create" value="Создать">
            </form>
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