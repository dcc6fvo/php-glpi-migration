<?php

$sql="SELECT id, entities_id, is_recursive, name, filename, filepath, documentcategories_id, mime, date_mod, comment, 
      is_deleted, link, users_id, tickets_id, sha1sum, is_blacklisted, tag, date_creation
      FROM glpi_documents;";
  
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  echo 'Migrating documents...'. PHP_EOL;

  try{
    $conn_new->beginTransaction();
    foreach ($documents as &$doc) {
      $sql = "INSERT INTO glpi_documents 
        (entities_id, is_recursive, name, filename, filepath, documentcategories_id, mime, date_mod, comment, 
        is_deleted, link, users_id, tickets_id, sha1sum, is_blacklisted, tag, date_creation) VALUES
        (:entities_id, :is_recursive, :name, :filename, :filepath, :documentcategories_id, :mime, :date_mod, :comment, 
        :is_deleted, :link, :users_id, :tickets_id, :sha1sum, :is_blacklisted, :tag, :date_creation);";
        
        $stmt2 = $conn_new->prepare($sql);
        $stmt2->bindParam(':entities_id', $ENTITY_NEW_ID, PDO::PARAM_STR);
        $stmt2->bindParam(':is_recursive', $doc['is_recursive'],PDO::PARAM_INT);
        $stmt2->bindParam(':name', $doc['name'],PDO::PARAM_STR);
        $stmt2->bindParam(':filename', $doc['filename'],PDO::PARAM_STR);
        $stmt2->bindParam(':filepath', $doc['filepath'],PDO::PARAM_STR);
        $stmt2->bindParam(':documentcategories_id', $doc['documentcategories_id'],PDO::PARAM_INT);
        $stmt2->bindParam(':mime', $doc['mime'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_mod', $doc['date_mod'],PDO::PARAM_STR);
        $stmt2->bindParam(':comment', $doc['comment'],PDO::PARAM_STR);
        $stmt2->bindParam(':is_deleted', $doc['is_deleted'],PDO::PARAM_INT);
        $stmt2->bindParam(':link', $doc['link'],PDO::PARAM_STR);
        
        $users_id = getNewUserID($doc['users_id']);
        $stmt2->bindParam(':users_id', $users_id, PDO::PARAM_INT);

        $tickets_id = findNewID($tickets, $doc['tickets_id']);
        $stmt2->bindParam(':tickets_id', $tickets_id, PDO::PARAM_INT);

        $stmt2->bindParam(':sha1sum', $doc['sha1sum'],PDO::PARAM_STR);
        $stmt2->bindParam(':is_blacklisted', $doc['is_blacklisted'],PDO::PARAM_INT);
        $stmt2->bindParam(':tag', $doc['tag'],PDO::PARAM_STR);
        $stmt2->bindParam(':date_creation', $doc['date_creation'],PDO::PARAM_STR);

        $stmt2->execute();
        $doc['new_id'] = $conn_new->lastInsertId();    
        echo "Inserting document nº : ".$doc['new_id'].PHP_EOL;
    }
    $conn_new->commit();
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage() . PHP_EOL;
      $conn_new->rollBack();
    }

    include 'MigDocumentsItems.php';

?>