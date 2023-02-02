<?php

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

  if($id == 0)
    return $id;

  foreach ($arr as $item) {
    if ($item['id'] == $id){
      if ( isset($item['new_id']) )  
        return $item['new_id'];
      else 
        return 0;
    }    
  }

  return 0;
}

function findNewIDbyMail($arr, $mail){

  foreach ($arr as $item) {

    if ($item['mail'] == $mail){
      if ( isset($item['new_id']) ) 
        return $item['new_id'];
      else{
        return 0;
      }
    }    
  }
  return 0;
}

?>