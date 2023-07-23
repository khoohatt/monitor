<?php
    require 'DB.class.php';

    $db = new DB;

    if (isset($_REQUEST['date'])) {
        $req = $_REQUEST['date'];
        $res = $db -> dbSelectHistory(date('Y-m-d H:m:s', strtotime($req)));
    } else {
        $res = $db -> dbSelect();
    }
    
    $first = true;
    while ($row = $res -> fetch()) {
        if ($first == true) {
            $first = false;

            echo '<tr><th>Время снятия данных</th>
            <td>'; echo $row['shot_time']; echo '</td></tr>

            <tr><th>Объем свободной памяти</th>
            <td>'; echo convBytes($row['memory_free']); echo '</td></tr>

            <tr><th>Процент используемой памяти</th>
            <td>'; echo $row['memory_used']; echo '</td></tr>

            <tr><th>Количество процессов</th>
            <td>'; echo $row['num_of_procs']; echo '</td></tr>
            </tr>~';
        }

            echo '<tr>
            <td tip="'; echo $row['proc_args']; echo'">'; echo $row['proc_exe']; echo '</td>
            <td>'; echo $row['create_time']; echo '</td>
            <td value='; echo $row['memory_usage']; echo '>'; echo convBytes($row['memory_usage']); echo '</td>
            <td>'; echo $row['threads']; echo '</td>
            <td>'; echo $row['proc_id']; echo '</td>
            </tr>';
        }

    function convBytes($bytes) {
        $i = floor(log($bytes) / log(1024));
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }
?>