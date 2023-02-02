<?php

require_once "AuxLDAP.php";

include 'Config.php';
include 'Params.php';

$conn_old = '';
$conn_new = '';

include 'DatabaseInit.php';

try {

  include 'Utils.php';

  include 'MigUsersMailsProfiles.php';
  include 'MigLocations.php';
  include 'MigItilCategories.php';
  include 'MigTickets.php';
  include 'MigDocuments.php'; 

} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

$conn_old = '';
$conn_new = '';

function arrayParaString($array1) {

  $result = "'" . implode ( "', '", $array1 ) . "'";
  return $result;
}

?>


