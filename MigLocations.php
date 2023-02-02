<?php

//Starting migrating locations ... users already migrated
$sql="SELECT id, entities_id, is_recursive, name, locations_id, completename, comment, 
      level, ancestors_cache, sons_cache, building, room, date_mod, date_creation 
      FROM glpi_locations ORDER BY id ASC;";
$stmt = $conn_old->prepare($sql);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo 'Migrating locations...'. PHP_EOL;

try{
  $conn_new->beginTransaction();

  foreach ($locations as &$loc) {

      $sql = "SELECT id FROM glpi_locations where 
             name = :name and 
             entities_id = :entity and
             locations_id = :locations_id";

      $stmt2 = $conn_new->prepare($sql);
      $stmt2->bindParam(':name', $loc['name'],PDO::PARAM_STR);
      $stmt2->bindParam(':entity', $ENTITY_NEW_ID,PDO::PARAM_INT);
      if($loc['locations_id'] <= 0 ){  
        $stmt2->bindParam(':locations_id', $loc['locations_id'], PDO::PARAM_INT);
      } 
      else{
        $new_location_id = findNewID($locations, $loc['locations_id']);
        $stmt2->bindParam(':locations_id', $new_location_id, PDO::PARAM_INT);
      }
      $stmt2->execute();
      $id = $stmt2->fetchColumn();

      //Location already exists on the database.. go to the next loc
      if( $id > 0){
        echo 'location '.$loc['name'].' already exists! '. PHP_EOL;
        $loc['new_id'] = $id; 
        continue;
      }else{

        $sql = "INSERT INTO glpi_locations
                (entities_id, is_recursive, name, locations_id, completename, comment, level, ancestors_cache, sons_cache, building, room, date_mod, date_creation) VALUES
                (:entities_id, :is_recursive, :name, :locations_id, :completename, :comment, :level, :ancestors_cache, :sons_cache, :building, :room, :date_mod, :date_creation )";    
              
        $stmt2 = $conn_new->prepare($sql);
        $stmt2->bindParam(':entities_id', $ENTITY_NEW_ID, PDO::PARAM_INT);
        $stmt2->bindParam(':is_recursive', $loc['is_recursive'],PDO::PARAM_INT);
        $stmt2->bindParam(':name', $loc['name'],PDO::PARAM_STR);
        
        if($loc['locations_id'] <= 0 ){
          $stmt2->bindParam(':locations_id', $loc['locations_id'], PDO::PARAM_INT);
        } 
        else{
          $new_location_id = findNewID($locations, $loc['locations_id']);
          $stmt2->bindParam(':locations_id', $new_location_id, PDO::PARAM_INT);
        }

        $stmt2->bindParam(':completename', $loc['completename'],PDO::PARAM_STR);
        $stmt2->bindParam(':comment', $loc['comment'],PDO::PARAM_STR);
        $stmt2->bindParam(':level', $loc['level'],PDO::PARAM_STR);

        if( isset($loc['ancestors_cache']) )
          $stmt2->bindParam(':ancestors_cache', $loc['ancestors_cache'],PDO::PARAM_STR);
        else{
          $nulo=null;
          $stmt2->bindParam(':ancestors_cache', $nulo, PDO::PARAM_STR);
        }

        if( isset($loc['sons_cache']) )
          $stmt2->bindParam(':sons_cache', $loc['sons_cache'],PDO::PARAM_STR);
        else{
          $nulo=null;
          $stmt2->bindParam(':sons_cache', $nulo, PDO::PARAM_STR);
        }
        
        $stmt2->bindParam(':building', $loc['building'],PDO::PARAM_STR);
        $stmt2->bindParam(':room', $loc['room'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_mod', $loc['date_mod'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_creation', $loc['date_creation'],PDO::PARAM_STR);
        $stmt2->execute();
        $loc['new_id'] = $conn_new->lastInsertId();  
        echo "Inserting location new ID: ".$loc['new_id']." -> ".$loc['id']."->".$loc['name']." ".PHP_EOL;  
      } 
    }
    $conn_new->commit();
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    $conn_new->rollBack();
  } 


  ?>