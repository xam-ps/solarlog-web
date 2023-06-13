<?php
$filename = "brautlach/days.csv";
$file = fopen($filename, "r");
fgets($file);

$iterator = 0;
$data = [];
$num_days = 30;
$sum_per_day = 0;
$num_of_inverters = 4;

// for canvasjs
$data_points = [];

setlocale(LC_ALL, 'de_DE');
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
            <?php
            for ($i = 0; $i < $num_of_inverters; $i++) {
                echo "<th>Converter " . $i + 1 . "</th>";
                $data_points[$i] = [];
            }
            ?>
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
            array_push($data_points[$iterator], array("label" => $data[0], "y" => $data[2]/1000));
            $iterator++;
            if ($iterator == $num_of_inverters) {
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
    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
    <script>
        window.onload = function() {
            var chart = new CanvasJS.Chart("chartContainer", {
                axisY: {
                    title: "KWh"
                },
                data: [
                    <?php
                    for ($i = 0; $i < $num_of_inverters; $i++) {
                        echo '{ type: "line", dataPoints: ' . json_encode($data_points[$i], JSON_NUMERIC_CHECK) . '},';
                    }
                    ?>
                ]
            });
            chart.render();
        }
    </script>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</body>

</html>