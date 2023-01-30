<?php

$sql="SELECT id, itemtype, items_id, date, users_id, users_id_editor, content, is_private, requesttypes_id, date_mod, 
     date_creation, timeline_position, sourceitems_id, sourceof_items_id
     FROM glpi_itilfollowups;";
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $itil_followups = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  try{
    $conn_new->beginTransaction();
    foreach ($itil_followups as &$if) {
      $sql = "INSERT INTO glpi_itilfollowups 
        (itemtype, items_id, date, users_id, content, is_private, 
        requesttypes_id, date_mod, date_creation, timeline_position, sourceitems_id, sourceof_items_id) VALUES
        (:itemtype, :items_id, :date, :users_id, :content, :is_private, 
        :requesttypes_id, :date_mod, :date_creation, :timeline_position, :sourceitems_id, :sourceof_items_id);";
        
        $stmt2 = $conn_new->prepare($sql);
        $stmt2->bindParam(':itemtype', $if['itemtype'], PDO::PARAM_STR);
        
        $items_id = findNewID($tickets, $if['items_id']);
        $stmt2->bindParam(':items_id', $items_id, PDO::PARAM_INT);

        $stmt2->bindParam(':date', $if['date'],PDO::PARAM_STR);

        $users_id = getNewUserID($if['users_id']);
        $stmt2->bindParam(':users_id', $users_id,PDO::PARAM_INT);

        $stmt2->bindParam(':content', $if['content'],PDO::PARAM_STR);
        $stmt2->bindParam(':is_private', $if['is_private'],PDO::PARAM_INT);
        $stmt2->bindParam(':requesttypes_id', $if['requesttypes_id'],PDO::PARAM_INT);
        $stmt2->bindParam(':date_mod', $if['date_mod'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_creation', $if['date_creation'],PDO::PARAM_STR);
        $stmt2->bindParam(':timeline_position', $if['timeline_position'],PDO::PARAM_STR);
        $stmt2->bindParam(':sourceitems_id', $if['sourceitems_id'],PDO::PARAM_INT);
        $stmt2->bindParam(':sourceof_items_id', $if['sourceof_items_id'],PDO::PARAM_INT);
        
        $stmt2->execute();
        $if['new_id'] = $conn_new->lastInsertId();    
        echo "Inserting follow up nº : ".$if['new_id'].PHP_EOL;
    }
    $conn_new->commit();
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage() . PHP_EOL;
      $conn_new->rollBack();
    }

?>