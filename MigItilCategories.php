<?php

//Insertion of Itil Categories

$sql="SELECT id, entities_id, name, completename, `level`,  is_helpdeskvisible, is_incident, is_request, is_problem, 
      is_change, date_mod, date_creation FROM automacao.glpi_itilcategories;";
$stmt = $conn_old->prepare($sql);
$stmt->execute();
$itil_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

try{
  
  $conn_new->beginTransaction();

  foreach ($itil_categories as &$cat) {

      $sql = "SELECT id FROM glpi_itilcategories where `name` = :name";
      $stmt2 = $conn_new->prepare($sql);
      $stmt2->bindParam(':name', $cat['name'],PDO::PARAM_STR);
      $stmt2->execute();
      $id = $stmt2->fetchColumn();

      //Category already exists on the database.. go to the next cat
      if( $id > 0){
        echo 'category '.$cat['name'].' already exists! '. PHP_EOL;
        $cat['new_id'] = $id; 
        continue;
      }else{

        $sql = "INSERT INTO glpi_itilcategories
                (entities_id, name, completename, `level`,  is_helpdeskvisible, is_incident, is_request, is_problem, 
                is_change, date_mod, date_creation) 
                VALUES
                (:entities_id, :name, :completename, :level, :is_helpdeskvisible, :is_incident, :is_request, :is_problem,
                :is_change, :date_mod, :date_creation )";    
              
        $stmt2 = $conn_new->prepare($sql);
        $stmt2->bindParam(':entities_id', $ENTITY_NEW_ID, PDO::PARAM_INT);
        $stmt2->bindParam(':name', $cat['name'],PDO::PARAM_STR);
        $stmt2->bindParam(':completename', $cat['completename'],PDO::PARAM_STR);
        $stmt2->bindParam(':level', $cat['level'],PDO::PARAM_INT);
        $stmt2->bindParam(':is_helpdeskvisible', $cat['is_helpdeskvisible'],PDO::PARAM_INT);
        $stmt2->bindParam(':is_incident', $cat['is_incident'],PDO::PARAM_INT);
        $stmt2->bindParam(':is_request', $cat['is_request'],PDO::PARAM_INT);
        $stmt2->bindParam(':is_problem', $cat['is_problem'],PDO::PARAM_INT);
        $stmt2->bindParam(':is_change', $cat['is_change'],PDO::PARAM_INT);
        $stmt2->bindParam(':date_mod', $cat['date_mod'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_creation', $cat['date_creation'],PDO::PARAM_STR);
        $stmt2->execute();
        $cat['new_id'] = $conn_new->lastInsertId();    
      } 
    }
    $conn_new->commit();
    
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    $conn_new->rollBack();
  } 


  ?>