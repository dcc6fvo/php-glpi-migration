<?php

$sql="SELECT id, tickets_id, users_id, type, use_notification, alternative_email FROM glpi_tickets_users;";
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $tickets_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  try{
    $conn_new->beginTransaction();
    foreach ($tickets_users as $tu) {

      $sql = "INSERT INTO glpi_tickets_users (tickets_id, users_id, type, use_notification, alternative_email) 
              VALUES (:tickets_id, :users_id, :type, :use_notification, :alternative_email );";
      $stmt2 = $conn_new->prepare($sql);
      
      $tickets_id = findNewID($tickets, $tu['tickets_id']);
      $stmt2->bindParam(':tickets_id', $tickets_id , PDO::PARAM_INT);

      $users_id = getNewUserID($tu['users_id']);
      $stmt2->bindParam(':users_id', $users_id, PDO::PARAM_INT);

      $stmt2->bindParam(':type', $tu['type'],PDO::PARAM_STR);
      $stmt2->bindParam(':use_notification', $tu['use_notification'],PDO::PARAM_STR);
      $stmt2->bindParam(':alternative_email', $tu['alternative_email'],PDO::PARAM_STR);
      $stmt2->execute();
      echo "Inserting ticket-users relationships.. ID ".$tu['id'].PHP_EOL;
    }
  $conn_new->commit();
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    $conn_new->rollBack();
  }
?>