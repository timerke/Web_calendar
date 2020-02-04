<?php
function calendar($month, $year, &$user_planner, &$parties_planner)
{
    // Определяются даты из личного календаря пользователя, которые намечены на $month месяц и $year год
    $n = $user_planner->num_rows; // общее количество дат в пользовательском массиве напоминаний
    $j = 0; // количество пользовательских дат с напоминаниями в $month месяце и $year году
    $user_days = null; // массив, куда будем записывать пользовательские даты в $month месяце и $year году
    for ($i = 0; $i < $n; $i++) {
        $row = $user_planner->fetch_assoc();
        $date = explode('-', $row['event_date']);
        if ($year == $date[0] && $month == $date[1]) {
            $user_days[$j++] = $date[2];
        }
    }
    // Определяются даты из группового календаря пользователя, которые намечены на $month месяц и $year год
    $n = $parties_planner->num_rows; // общее количество дат в групповом массиве напоминаний
    $j = 0; // количество пользовательских дат с напоминаниями в $month месяце и $year году
    $parties_days = null; // массив, куда будем записывать групповые даты в $month месяце и $year году
    for ($i = 0; $i < $n; $i++) {
        $row = $parties_planner->fetch_assoc();
        $date = explode('-', $row['event_date']);
        if ($year == $date[0] && $month == $date[1]) {
            $parties_days[$j++] = $date[2];
        }
    }
    // Определяется количество дней в месяце $month $year года
    $days = 31;
    while (!checkdate($month, $days, $year)) {
        $days--;
    }
    $day = 1; // счетчик для дней месяца
    $day_of_week = date('w', mktime(0, 0, 0, $month, $day, $year)); // вычисляем номер дня недели для первого дня месяца
    // Приводим номер дня недели к формату, где 0 - понедельник, ... , 6 - воскресенье
    $day_of_week -= 1;
    if ($day_of_week == -1) {
        $day_of_week = 6;
    }
    // Первая неделя
    $week_num = 0; // номер недели
    for ($i = 0; $i < 7; $i++) {
        if ($day_of_week == $i) {
            // Если дни недели совпадают, заполняем массив $week числами месяца
            $week[$week_num][$i] = $day;
            $day++;
            $day_of_week++;
        } else {
            $week[$week_num][$i] = "";
        }
    }
    // Последующие недели месяца
    while (true) {
        $week_num++;
        for ($i = 0; $i < 7; $i++) {
            $week[$week_num][$i] = $day;
            $day++;
            // Если достигли конца месяца, выходим из цикла for
            if ($day > $days) {
                break;
            }
        }
        // Если достигли конца месяца, выходим из цикла while
        if ($day > $days) {
            break;
        }
    }
    // Выводим содержимое массива $week в виде календаря
    echo "<form action='new_reminder.php' method='POST'>";
    echo '<table><br/>';
    echo "<tr><th colspan='7'>" . $month . "." . $year . "</th></tr>";
    echo "<tr><td>Понедельник</td>" .
        "<td>Вторник</td>" .
        "<td>Среда</td>" .
        "<td>Четверг</td>" .
        "<td>Пятница</td>" .
        "<td>Суббота</td>" .
        "<td>Воскресенье</td></tr>";
    for ($i = 0; $i < count($week); $i++) {
        echo "<tr>";
        for ($j = 0; $j < 7; $j++) {
            if (!empty($week[$i][$j])) {
                // Если в дне недели есть числа нужного месяца
                $flag = false; // флаг отвечает за совпадение даты с днем напоминания: если false, то в этот день напоминания нет
                if ($user_days != null) {
                    // Если в рассматриваемый месяц есть даты из пользовательского календаря
                    for ($k = 0; $k < count($user_days); $k++) {
                        if ($user_days[$k] == $week[$i][$j]) {
                            // Если дата из пользовательского календаря совпала с рассматриваемой датой
                            echo "<td><input class='user_planner' type='submit' name='new_reminder' value='" . $week[$i][$j] . "'></td>";
                            $flag = true;
                            break;
                        }
                    }
                }
                if ($parties_days != null) {
                    // Если в рассматриваемый месяц есть даты из группового календаря
                    for ($k = 0; $k < count($parties_days); $k++) {
                        if ($parties_days[$k] == $week[$i][$j]) {
                            // Если дата из шруппового календаря совпала с рассматриваемой датой
                            echo "<td><input class='parties_planner' type='submit' name='planner_day' value='" . $week[$i][$j] . "'></td>";
                            $flag = true;
                            break;
                        }
                    }
                }
                if (!$flag) {
                    echo "<td><input type='submit' name='planner_day' value='" . $week[$i][$j] . "'></td>";
                }
            } else echo "<td>&nbsp;</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
}
?>