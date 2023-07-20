<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$filenameDaily = "brautlach/days.csv";
$dailyFile = fopen($filenameDaily, "r");
fgets($dailyFile);

$filenameMonthly = "brautlach/months.csv";
$monthlyFile = fopen($filenameMonthly, "r");
fgets($monthlyFile);

$iterator = 0;
$data = [];
$num_days = 30;
$sum_per_day = 0;
$sum_per_month = 0;
$num_of_inverters = 4;

// for canvasjs
$data_points = [];
for ($i = 0; $i < $num_of_inverters; $i++) {
    $data_points[$i] = [];
}

setlocale(LC_ALL, 'de_DE');

function makeTableHead($num_of_inverters)
{
?>
    <tr>
        <th>Date</th>
        <?php
        for ($i = 0; $i < $num_of_inverters; $i++) {
            echo "<th>Inverter " . $i + 1 . "</th>";
        }
        ?>
        <th>Total</th>
    </tr>
<?php
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PV Dashboard</title>
    <style>
        body {
            max-width: 1200px;
            margin: 0 auto;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        h2 {
            text-align: center;
        }

        #tableWrapper {
            overflow: hidden;
            overflow-y: scroll;
            height: 268px;
        }

        table {
            margin: 0 auto;
        }

        td {
            text-align: center;
        }

        nav {
            text-align: center;
        }

        nav ul {
            list-style: none;
            cursor: pointer;
        }

        nav li {
            display: inline-block;
            font-size: 1.2rem;
            margin: 5px;
            padding: 4px 10px;
            background: rgb(247, 255, 217);
            background: linear-gradient(90deg, rgba(247, 255, 217, 1) 0%, rgba(201, 226, 255, 1) 100%);
            border-radius: 3px;
            transition: transform 100ms;
            width: 100px;
        }

        nav li:hover {
            transform: scale(1.1);
        }

        #update {
            text-align: right;
            font-size: 0.8rem;
        }

        #perMonthPage {
            display: none;
        }
    </style>
</head>

<body>
    <p id="update">
        <?php
        if (file_exists($filenameDaily)) {
            echo "Last update: " . date("d.m.Y H:i", filemtime($filenameDaily)) . "<br><br>";
        }
        ?>
    </p>
    <nav>
        <ul>
            <li id="perDayButton">Tage</li>
            <li id="perMonthButton">Monate</li>
        </ul>
    </nav>
    <div id="perDayPage">
        <h2>Übersicht über die letzten 30 Tage</h2>
        <div id="tableWrapper">
            <table>
                <?php
                makeTableHead($num_of_inverters);

                while (!feof($dailyFile)) {
                    if ($num_days < 1) {
                        break;
                    }
                    $data = fgetcsv($dailyFile, null, ';');
                    if (!$iterator) {
                        print("<tr><td>");
                        print($data[0]);
                        print("</td>");
                    }
                    echo sprintf('<td>%d</td>', $data[2] / 1000);
                    $sum_per_day = $sum_per_day + $data[2] / 1000;
                    array_push($data_points[$iterator], array("label" => $data[0], "y" => $data[2] / 1000));
                    $iterator++;
                    if ($iterator == $num_of_inverters) {
                        echo sprintf('<td>%d</td>', $sum_per_day);
                        print("</tr>");
                        $iterator = 0;
                        $num_days--;
                        $sum_per_day = 0;
                    }
                }

                fclose($dailyFile);
                ?>
            </table>
        </div>
        <div id="chartContainer" style="height: 370px; width: 100%;"></div>
    </div>
    <div id="perMonthPage">
        <h2>Übersicht über die letzten Monate</h2>
        <div id="tableWrapper">
            <table>
                <?php
                makeTableHead($num_of_inverters);

                $iterator = 0;

                while (!feof($monthlyFile)) {
                    $data = fgetcsv($monthlyFile, null, ';');
                    if (!$iterator) {
                        print("<tr><td>");
                        print(substr(($data[0] ?? 0), 3));
                        print("</td>");
                    }
                    echo sprintf('<td>%d</td>', ($data[2] ?? 0) / 1000);
                    $sum_per_month = $sum_per_month + ($data[2] ?? 0) / 1000;
                    $iterator++;
                    if ($iterator == $num_of_inverters) {
                        echo sprintf('<td>%d</td>', $sum_per_month);
                        print("</tr>");
                        $iterator = 0;
                        $sum_per_month = 0;
                    }
                }

                fclose($monthlyFile);
                ?>
            </table>
        </div>
    </div>

    <script>
        function showDayPage() {
            var dayPage = document.getElementById("perDayPage");
            var monthPage = document.getElementById("perMonthPage");
            dayPage.style.display = "block";
            monthPage.style.display = "none";
        }

        function showMonthPage() {
            var monthPage = document.getElementById("perMonthPage");
            var dayPage = document.getElementById("perDayPage");
            monthPage.style.display = "block";
            dayPage.style.display = "none";
        }

        window.onload = function() {
            document.getElementById("perDayButton").addEventListener("click", showDayPage);
            document.getElementById("perMonthButton").addEventListener("click", showMonthPage);

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