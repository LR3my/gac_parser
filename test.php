<?php

function placeholders($text, $count=0, $separator=","){
    $result = array();
    if($count > 0){
        for($x=0; $x<$count; $x++){
            $result[] = $text;
        }
    }

    return implode($separator, $result);
}

$pdo = new PDO(
    'mysql:host=localhost;dbname=gac',
    'gac',
    'x6QEX<7"f~5-');

$datafields = array('fielda', 'fieldb' );

$data[] = array('fielda' => 'value', 'fieldb' => 'value1' );
$data[] = array('fielda' => 'value', 'fieldb' => 'value1' );

$pdo->beginTransaction(); // also helps speed up your inserts.
$insert_values = array();
foreach($data as $d){
    $question_marks[] = '('  . placeholders('?', sizeof($d)) . ')';
    $insert_values = array_merge($insert_values, array_values($d));
}

$sql = "INSERT INTO table (" . implode(",", $datafields ) . ") VALUES " .
       implode(',', $question_marks);

       var_dump($insert_values);die;

$stmt = $pdo->prepare($insert_values);
try {
    $stmt->execute($insert_values);
} catch (PDOException $e){
    echo $e->getMessage();
}
$pdo->commit();
