<?php
$filename = "brautlach/days.csv";
$file = fopen($filename, "r");
fgets($file);

$iterator = 0;
$data = [];
$num_days = 30;
$sum_per_day = 0;

setlocale(LC_ALL,'de_DE');
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PV Dashboard</title>
    <style>
        td {
            text-align: center;
        }
    </style>
</head>

<body>
    <?php
    if (file_exists($filename)) {
        echo "Last update: " . date("d.m.Y H:i", filemtime($filename)) . "<br><br>";
    }
    ?>
    <table>
        <tr>
            <th>Date</th>
            <th>Converter 1</th>
            <th>Converter 2</th>
            <th>Converter 3</th>
            <th>Converter 4</th>
            <th>Total</th>
        </tr>
        <?php

        while (!feof($file)) {
            if ($num_days < 1) {
                break;
            }
            $data = fgetcsv($file, null, ';');
            if (!$iterator) {
                print("<tr><td>");
                print($data[0]);
                print("</td>");
            }
            echo sprintf('<td>%d</td>', $data[2] / 1000);
            $sum_per_day = $sum_per_day + $data[2] / 1000;
            $iterator++;
            if ($iterator == 4) {
                echo sprintf('<td>%d</td>', $sum_per_day);
                print("</tr>");
                $iterator = 0;
                $num_days--;
                $sum_per_day = 0;
            }
        }

        fclose($file);
        ?>
    </table>
</body>

</html>