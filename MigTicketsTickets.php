<?php

$sql="SELECT id, tickets_id_1, tickets_id_2, link FROM glpi_tickets_tickets;";
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $tickets_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  try{
    $conn_new->beginTransaction();
    foreach ($tickets_tickets as $tt) {
      $sql = "INSERT INTO glpi_tickets_tickets (tickets_id_1, tickets_id_2, link) VALUES
      (:tickets_id_1, :tickets_id_2, :link);";
        $stmt2 = $conn_new->prepare($sql);

        $tickets_id_1 = findNewID($tickets, $tt['tickets_id_1']);
        $stmt2->bindParam(':tickets_id_1', $tickets_id_1, PDO::PARAM_INT);

        $tickets_id_2 = findNewID($tickets, $tt['tickets_id_2']);
        $stmt2->bindParam(':tickets_id_2', $tickets_id_2, PDO::PARAM_INT);

        $stmt2->bindParam(':link', $tt['link'],PDO::PARAM_INT);
        $stmt2->execute();
        echo "Inserting ticket correlation.. ID ".$tt['id'].PHP_EOL;
    }
    $conn_new->commit();
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage() . PHP_EOL;
      $conn_new->rollBack();
    }

?>