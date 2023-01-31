<?php

$sql="SELECT id, documents_id, items_id, itemtype, entities_id, is_recursive, date_mod, users_id, timeline_position, 
     date_creation, `date` FROM glpi_documents_items;";
  
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $documents_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  try{
    $conn_new->beginTransaction();
    foreach ($documents_items as &$di) {
      $sql = "INSERT INTO glpi_documents_items 
        (documents_id, items_id, itemtype, entities_id, is_recursive, date_mod, users_id, timeline_position, 
        date_creation, date) VALUES
        (:documents_id, :items_id, :itemtype, :entities_id, :is_recursive, :date_mod, :users_id, :timeline_position, 
        :date_creation, :date);";
        
        $stmt2 = $conn_new->prepare($sql);

        $documents_id = findNewID($documents, $di['documents_id']);
        $stmt2->bindParam(':documents_id', $documents_id, PDO::PARAM_INT);

        $items_id = findNewID($tickets, $di['items_id']);
        $stmt2->bindParam(':items_id', $items_id, PDO::PARAM_INT);

        $stmt2->bindParam(':itemtype', $di['itemtype'],PDO::PARAM_STR);
        $stmt2->bindParam(':entities_id', $ENTITY_NEW_ID, PDO::PARAM_STR);
        $stmt2->bindParam(':is_recursive', $di['is_recursive'],PDO::PARAM_INT);
        $stmt2->bindParam(':date_mod', $di['date_mod'],PDO::PARAM_STR);

        $users_id = getNewUserID($di['users_id']);
        $stmt2->bindParam(':users_id', $users_id, PDO::PARAM_INT);

        $stmt2->bindParam(':timeline_position', $di['timeline_position'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_creation', $di['date_creation'],PDO::PARAM_STR);
        $stmt2->bindParam(':date', $di['date'],PDO::PARAM_STR);
        
        $stmt2->execute();
        $di['new_id'] = $conn_new->lastInsertId();    
        echo "Inserting document item nº : ".$di['new_id'].PHP_EOL;
    }
    $conn_new->commit();
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage() . PHP_EOL;
      $conn_new->rollBack();
    }

?>