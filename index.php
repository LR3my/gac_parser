<?php

require_once __DIR__.'/vendor/autoload.php';

use Library\Parser as Parser;

function rutime($ru, $rus, $index)
{
    return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
        - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
}

$rustart = getrusage();
$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

$import = false;

$pdo = new \PDO(
    'mysql:host=localhost;dbname=test_gac',
    'test_gac',
    'test_gac');


if (isset($_FILES['phone_log_file']) && !empty($_FILES['phone_log_file']) &&
    in_array($_FILES['phone_log_file']['type'],$mimes)) {

    $fileName = 'storage/' . $_FILES['phone_log_file']['name'];
    move_uploaded_file($_FILES['phone_log_file']['tmp_name'], $fileName);
    $dataFields = ['subscriber_number',
        'type',
        'date',
        'time',
        'phone_call_actual_duration',
        'phone_call_billed_duration',
        'data_real_volume',
        'data_billed_volume'];
    $parser = new Parser($fileName, $pdo);
    $parser->setFields($dataFields);
    $parser->truncateTable();
    $parser->parseFile();
    $parser->closeFile();
    $import = true;
}



$sumCallTime = $pdo->query("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(phone_call_actual_duration))) AS timeSum 
    FROM logs WHERE type = 'phone_call' AND date > '2012-02-15'")->fetch();

$volumes = $pdo->query("SELECT SUM(data_billed_volume) as volume, subscriber_number FROM logs 
WHERE (time < '08:00:00' OR time > '18:00:00') and data_billed_volume is not null 
GROUP BY subscriber_number ORDER BY volume DESC LIMIT 10")->fetchAll();

$countSms = $pdo->query("select count(id) FROM logs WHERE type = 'SMS'")->fetch();
$ru = getrusage();
?>

<html>
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
              integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>Envoyer votre fichier à analyser</h1>
                    <form action="index.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="phone_log_file">Votre fichier csv</label>
                            <input type="file" class="form-control-file" id="phone_log_file" name="phone_log_file">
                        </div>
                        <button type="submit" class="btn btn-primary">Valider</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">

                    <?php
                    if ($import) {
                    ?>
                        <hr/>
                    <p>
                        Fichier importé avec succès ! <br/>
                        Le traitement a pris <?php echo rutime($ru, $rustart, "utime");?>ms
                    </p>
                    <?php
                    }
                    ?>
                    <table class="table">
                        <tr>
                            <th>
                                Retrouver la durée totale réelle des appels effectués après le 15/02/2012 (inclus)
                            </th>
                            <td><?php echo $sumCallTime['timeSum'];?> minutes</td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                Retrouver le TOP 10 des volumes data facturés en dehors de la tranche horaire 8h00-
                                18h00, par abonné
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <table border="1">
                                    <tr>
                                        <th>Volume</th>
                                        <th>Abonnée</th>
                                    </tr>
                                <?php
                                foreach($volumes as $volume) {
                                    echo '<tr><td>' . $volume['volume'] .'</td><td>' . $volume['subscriber_number'] .'</td></tr>';
                                }
                                ?>
                                </table>

                            </td>
                        </tr>
                        <tr>
                            <th>Retrouver la quantité totale de SMS envoyés par l'ensemble des abonnés</th>
                            <td><?php echo $countSms[0];?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
                integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
                integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </body>
</html>


