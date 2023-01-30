<?php

//Starting migrating tickets ... users mails profiles locations itil_categories already migrated

$sql="SELECT id, entities_id, name, date, closedate, solvedate, date_mod, users_id_lastupdater, status, users_id_recipient, content, 
urgency, impact, priority, itilcategories_id, type, global_validation, time_to_resolve, time_to_own, begin_waiting_date, 
waiting_duration, close_delay_stat, solve_delay_stat, takeintoaccount_delay_stat, actiontime, is_deleted, locations_id, 
validation_percent, date_creation FROM glpi_tickets;";
$stmt = $conn_old->prepare($sql);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

try{
  $conn_new->beginTransaction();
  foreach ($tickets as &$tick) {
      $sql = "INSERT INTO glpi_tickets
              (entities_id, name, date, closedate, solvedate, date_mod, users_id_lastupdater, status, users_id_recipient, content, 
              urgency, impact, priority, itilcategories_id, type, global_validation, time_to_resolve, time_to_own, begin_waiting_date, 
              waiting_duration, close_delay_stat, solve_delay_stat, takeintoaccount_delay_stat, actiontime, is_deleted, locations_id, 
              validation_percent, date_creation) VALUES
              (:entities_id, :name, :date, :closedate, :solvedate, :date_mod, :users_id_lastupdater, :status, :users_id_recipient, :content, 
              :urgency, :impact, :priority, :itilcategories_id, :type, :global_validation, :time_to_resolve, :time_to_own, :begin_waiting_date, 
              :waiting_duration, :close_delay_stat, :solve_delay_stat, :takeintoaccount_delay_stat, :actiontime, :is_deleted, :locations_id, 
              :validation_percent, :date_creation)";    
            
      $stmt2 = $conn_new->prepare($sql);
      $stmt2->bindParam(':entities_id', $ENTITY_NEW_ID, PDO::PARAM_INT);
      $stmt2->bindParam(':name', $tick['name'],PDO::PARAM_STR);
      $stmt2->bindParam(':date', $tick['date'],PDO::PARAM_STR);
      $stmt2->bindParam(':closedate', $tick['closedate'],PDO::PARAM_STR);
      $stmt2->bindParam(':solvedate', $tick['solvedate'],PDO::PARAM_STR);
      $stmt2->bindParam(':date_mod', $tick['date_mod'],PDO::PARAM_STR);
      
      $users_id_lastupdater = getNewUserID($tick['users_id_lastupdater']);
      $stmt2->bindParam(':users_id_lastupdater', $users_id_lastupdater, PDO::PARAM_INT);
      
      $stmt2->bindParam(':status', $tick['status'],PDO::PARAM_INT);

      $users_id_recipient = getNewUserID($tick['users_id_recipient']);
      $stmt2->bindParam(':users_id_recipient', $users_id_recipient, PDO::PARAM_INT);
      
      $stmt2->bindParam(':content', $tick['content'],PDO::PARAM_STR);
      $stmt2->bindParam(':urgency', $tick['urgency'],PDO::PARAM_INT);
      $stmt2->bindParam(':impact', $tick['impact'],PDO::PARAM_INT);
      $stmt2->bindParam(':priority', $tick['priority'],PDO::PARAM_INT);

      $itilcategories_id = findNewID($itil_categories,$tick['itilcategories_id']);
      $stmt2->bindParam(':itilcategories_id', $itilcategories_id, PDO::PARAM_INT);

      $stmt2->bindParam(':type', $tick['type'],PDO::PARAM_INT);
      $stmt2->bindParam(':global_validation', $tick['global_validation'],PDO::PARAM_INT);
      $stmt2->bindParam(':time_to_resolve', $tick['time_to_resolve'],PDO::PARAM_STR);
      $stmt2->bindParam(':time_to_own', $tick['time_to_own'],PDO::PARAM_STR);
      $stmt2->bindParam(':begin_waiting_date', $tick['begin_waiting_date'],PDO::PARAM_STR);
      $stmt2->bindParam(':waiting_duration', $tick['waiting_duration'],PDO::PARAM_STR);
      $stmt2->bindParam(':close_delay_stat', $tick['close_delay_stat'],PDO::PARAM_STR);
      $stmt2->bindParam(':solve_delay_stat', $tick['solve_delay_stat'],PDO::PARAM_STR);
      $stmt2->bindParam(':takeintoaccount_delay_stat', $tick['takeintoaccount_delay_stat'],PDO::PARAM_STR);
      $stmt2->bindParam(':actiontime', $tick['actiontime'],PDO::PARAM_STR);
      $stmt2->bindParam(':is_deleted', $tick['is_deleted'],PDO::PARAM_INT);

      $locations_id = findNewID($locations,$tick['locations_id']);
      $stmt2->bindParam(':locations_id', $locations_id, PDO::PARAM_STR);
      $stmt2->bindParam(':validation_percent', $tick['validation_percent'],PDO::PARAM_STR);
      $stmt2->bindParam(':date_creation', $tick['date_creation'],PDO::PARAM_STR);
      $stmt2->execute();
      $tick['new_id'] = $conn_new->lastInsertId();    
      echo "Inserting ticket nº : ".$tick['new_id'].PHP_EOL;
    }
    $conn_new->commit();
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    $conn_new->rollBack();
  }

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




  

  function getNewUserID($oldId) {

    global $users;
    global $conn_old;  

    $result = findNewID($users,$oldId);

    if($result == 0){
      $sql = "SELECT gue.email as mail FROM glpi_users gu, glpi_useremails gue where gu.id = gue.users_id 
              and gu.id = :oldid ";
      $stmt = $conn_old->prepare($sql);
      $stmt->bindParam(':oldid', $oldId, PDO::PARAM_STR);
      $stmt->execute();
      $mail = $stmt->fetchColumn();
      return findNewIDbyMail($users,$mail);
    }else{
      return $result;
    }  
  }

  function findNewID($arr, $id){
    foreach ($arr as $item) {
      if ($item['id'] == $id){
        //print_r($item);
        //echo('old id = '.$oldId. ' new id = '.$u['new_id']). PHP_EOL;
        return $item['new_id'];
      }    
    }
    return 0;
  }

  function findNewIDbyMail($arr, $mail){

    foreach ($arr as $item) {
      if ($item['mail'] == $mail){
        return $item['new_id'];
      }    
    }
    return 0;
  }


  ?>