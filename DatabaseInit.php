<?php

try {
    $ldap = new AuxLDAP($LDAP_NEW_HOST, $LDAP_NEW_BIND_USER, $LDAP_NEW_BIND_PASS, $LDAP_NEW_BASEDN);
  } catch(Exception $e) {
    echo "LDAP Connection failed for $LDAP_NEW_HOST ->" . $e->getMessage() .PHP_EOL;
  }
  
  try {
    $conn = new PDO("mysql:host=$DB_OLD_ADDRESS;dbname=$DB_OLD_DATABASE", $DB_OLD_USER, $DB_OLD_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn_old = $conn;
    echo "Connected successfully for $DB_OLD_ADDRESS ".PHP_EOL;
  } catch(PDOException $e) {
      echo "Connection failed for $DB_OLD_ADDRESS ->" . $e->getMessage() .PHP_EOL;
  }
  
  try {
    $conn = new PDO("mysql:host=$DB_NEW_ADDRESS;dbname=$DB_NEW_DATABASE", $DB_NEW_USER, $DB_NEW_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn_new = $conn;
    echo "Connected successfully for $DB_NEW_ADDRESS ".PHP_EOL;
  } catch(PDOException $e) {
      echo "Connection failed for $DB_NEW_ADDRESS ->" . $e->getMessage() .PHP_EOL;
  }

?>