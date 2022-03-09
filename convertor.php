<?php
unset($argv[0]);
// Creates user object
class User {
    public function __construct($identifier, $email, $firstName, $lastName, $name=null, $cust1=null, $cust2=null, $cust3=null, $cust4=null, $cust5=null) {
        $this->identifier = $identifier;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->name = $firstName . " " . $lastName;
        $this->cust1 = $cust1;
        $this->cust2 = $cust2;
        $this->cust3 = $cust3;
        $this->cust4 = $cust4;
        $this->cust5 = $cust5;
    }
}
// Read CSV file into array of objects
function readCsv($filename) {
    $csv = array_map("str_getcsv", file($filename)); 
    $header = array_shift($csv);
    $counter = 0;
    // Create user object for each line
    foreach ($csv as $row) {
        $objArray[$counter] = new User($row[0], $row[1], $row[2], $row[3], null, $row[4], $row[5], $row[6], $row[7], $row[8]);
        $counter++;
    }
    return $objArray;
}

function cmp($a, $b) {
    return strcmp($a->identifier, $b->identifier);
}
// Merges lines with same identifiers
function mergeUsers($arr) {
    $mergedArr = $arr;
    $length = count($mergedArr);
    for($i = 0; $i < $length-1; $i++) {
        $j = $i;
        while((!is_null($mergedArr[$j+1]->identifier)) && $mergedArr[$i]->identifier == $mergedArr[$j+1]->identifier) {
            $mergedArr[$i]->name = $mergedArr[$i]->name . ';' . $mergedArr[$j+1]->firstName . ' ' . $mergedArr[$j+1]->lastName;
            $mergedArr[$i]->email = $mergedArr[$i]->email . ';' . $mergedArr[$j+1]->email;
            $j++;
            $mergedArr[$j] = false;
            $length--;
        }
    }
    return $mergedArr;
}
//Convert object array to array of Strings
function convertArr($arr) {
    $newArr = array();
    $rowArr = array();
    $length = count($arr);
    foreach ($arr as $k) {
        if(is_object($k)) {
            $newArr[] = array($k->identifier, $k->email, $k->firstName, $k->lastName, $k->cust1, $k->cust2, $k->cust3, $k->cust4, $k->cust5, $k->name);
        }
    }
    return $newArr;
}

// Get CLI args
$shortoptions = "i:o:id:";
$longoptions = ["in:", "out:", "header:"];
$options = getopt($shortoptions, $longoptions);
$infile = $options["in"];
$outfile = $options["out"];
// Default header row to 1 if no header args
if(array_key_exists("header", $options)) {
    $headerRow = $options["header"];
} else {$headerRow = 1;}

$csv = array_map("str_getcsv", file($infile)); 
$header = array_shift($csv);
$header[0] = "Identifier";
$objArray = readCsv($infile);
usort($objArray, "cmp");
$mergedArr = mergeUsers($objArray);
array_values(array_filter($mergedArr));
$newLength = count($mergedArr);
$convertedArr = convertArr($mergedArr);

$file = fopen($outfile,"w");

for($i = 0; $i < count($convertedArr); $i++) {
    if($headerRow == $i+1) {
        array_push($header, "Name");
        fputcsv($file, $header);
    }
    fputcsv($file, $convertedArr[$i]);
}
fclose($file);
?>