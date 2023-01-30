<?php

$sql="SELECT id, itemtype, items_id, solutiontypes_id, solutiontype_name, content, date_creation, date_mod, 
      date_approval, users_id, user_name, users_id_editor, users_id_approval, user_name_approval, status, itilfollowups_id
      FROM glpi_itilsolutions;";
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $itil_solutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  try{
    $conn_new->beginTransaction();
    foreach ($itil_solutions as &$is) {
      $sql = "INSERT INTO glpi_itilsolutions 
        (itemtype, items_id, solutiontypes_id, solutiontype_name, content, date_creation, date_mod, 
        date_approval, users_id, user_name, users_id_editor, users_id_approval, user_name_approval, status, itilfollowups_id) 
        VALUES
        (:itemtype, :items_id, :solutiontypes_id, :solutiontype_name, :content, :date_creation, :date_mod, 
        :date_approval, :users_id, :user_name, :users_id_editor, :users_id_approval, :user_name_approval, :status, :itilfollowups_id);";
        
        $stmt2 = $conn_new->prepare($sql);
        $stmt2->bindParam(':itemtype', $is['itemtype'], PDO::PARAM_STR);
        
        $items_id = findNewID($tickets, $is['items_id']);
        $stmt2->bindParam(':items_id', $items_id, PDO::PARAM_INT);

        $stmt2->bindParam(':solutiontypes_id', $is['solutiontypes_id'], PDO::PARAM_INT);
        $stmt2->bindParam(':solutiontype_name', $is['solutiontype_name'], PDO::PARAM_INT);
        $stmt2->bindParam(':content', $is['content'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_creation', $is['date_creation'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_mod', $is['date_mod'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_approval', $is['date_approval'],PDO::PARAM_STR);

        $users_id = getNewUserID($is['users_id']);
        $stmt2->bindParam(':users_id', $users_id,PDO::PARAM_INT);

        $stmt2->bindParam(':user_name', $is['user_name'],PDO::PARAM_STR);

        $users_id_editor = getNewUserID($is['users_id_editor']);
        $stmt2->bindParam(':users_id_editor', $users_id_editor,PDO::PARAM_INT);

        $users_id_approval = getNewUserID($is['users_id_approval']);
        $stmt2->bindParam(':users_id_approval', $users_id_approval,PDO::PARAM_INT);

        $stmt2->bindParam(':user_name_approval', $is['user_name_approval'],PDO::PARAM_STR);  
        $stmt2->bindParam(':status', $is['status'],PDO::PARAM_INT);
        $stmt2->bindParam(':itilfollowups_id', $is['itilfollowups_id'],PDO::PARAM_INT);

        $stmt2->execute();
        $is['new_id'] = $conn_new->lastInsertId();    
        echo "Inserting solution nº : ".$is['new_id'].PHP_EOL;
    }
    $conn_new->commit();
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage() . PHP_EOL;
      $conn_new->rollBack();
    }

?>