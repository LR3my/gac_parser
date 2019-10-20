<?php
/**
 * Created by PhpStorm.
 * User: Titi
 * Date: 20/10/2019
 * Time: 17:01
 */

namespace Library;

class Parser
{

    public $handle;
    public $fields = [];
    public $markers;
    public $markersArray = [];
    public $inserts = [];
    public $values = [];
    /**
     * @var \PDO
     */
    public $pdo;


    public function __construct($filePath, $pdo)
    {
        $this->handle = fopen($filePath, 'r');
        $this->setPdo($pdo);
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function guessType($row, $rowData)
    {
        if ($this->validateDate($row[5], 'H:i:s')) {
            $rowData['type'] = 'phone_call';
            $rowData['phone_call_actual_duration'] = $row[5];
            $rowData['phone_call_billed_duration'] = $row[6];
        } elseif (is_numeric($row[5])) {
            $rowData['type'] = 'data';
            $rowData['data_real_volume'] = $row[5];
            $rowData['data_billed_volume'] = $row[6];
        }
        return $rowData;
    }

    public function setupMarkers()
    {
        $result = array();
        $c = count($this->fields);
        if($c > 0){
            for($i=0; $i<$c; $i++){
                $result[] = "?";
            }
        }
        $this->markers = "(" . implode(", ", $result) . ")";
    }

    public function multipleInserts()
    {
        $this->pdo->beginTransaction();
        $query = "INSERT INTO logs (" . implode(",", $this->fields) . ") 
        VALUES " . implode(',', $this->markersArray);
        $statement = $this->pdo->prepare($query);

        try {
            $statement->execute($this->inserts);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $this->pdo->commit();
    }

    public function resetsVar()
    {
        $this->inserts = [];
        $this->markersArray = [];
    }


    function parseFile()
    {
        $this->setupMarkers();
        $this->resetsVar();
        $cpt = 1;
        while (($row = fgetcsv($this->handle, 1000, ";")) !== FALSE) {
            $rowFullDate = $row[3] . ' ' . $row[4];
            if ($this->validateDate($rowFullDate, 'd/m/Y H:i:s')) {
                $d = \DateTime::createFromFormat('d/m/Y', $row[3]);
                $rowData = [
                    'subscriber_number' => $row[2],
                    'type' => 'SMS',
                    'date' => $d->format('Y-m-d'),
                    'time' => $row[4],
                    'phone_call_actual_duration' => null,
                    'phone_call_billed_duration' => null,
                    'data_real_volume' => null,
                    'data_billed_volume' => null
                ];

                $rowData = $this->guessType($row, $rowData);
                $this->inserts = array_merge($this->inserts, array_values($rowData));
                $this->markersArray[] = $this->markers;
                if ($cpt % 100 == 0) {
                    $this->multipleInserts();
                    $this->resetsVar();
                }
                $cpt++;
            }
        }

        if (!empty($this->inserts)) {
            $this->multipleInserts();
        }
    }

    public function closeFile()
    {
        fclose($this->handle);
    }

    public function truncateTable()
    {
        $this->pdo->query('TRUNCATE logs');
    }

}