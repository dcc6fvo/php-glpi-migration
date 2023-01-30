<?php

$exc=arrayParaString($EXCLUDE_USERS);

  $sql="SELECT gu.id, gu.name,gu.password,gu.mobile,gu.realname,gu.firstname,gu.locations_id,gu.use_mode,gu.is_active,gu.auths_id,gu.authtype,gu.is_deleted,gu.profiles_id,gu.entities_id,
      gu.usertitles_id,gu.usercategories_id,gu.user_dn,gu.is_deleted_ldap,gu.picture,gu.itil_layout,gu.highcontrast_css,gu.groups_id,gu. users_id_supervisor,gu.default_central_tab,
      gue.is_default, gue.is_dynamic, gue.email as mail
      FROM glpi_users gu, glpi_useremails gue where name not in ($exc) and gu.id = gue.users_id order by id desc;";
    
  $stmt = $conn_old->prepare($sql);
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($users as &$user) {

    try {
      $sql = "SELECT id FROM glpi_useremails where email = :mail";
      $stmt2 = $conn_new->prepare($sql);
      $stmt2->bindParam(':mail', $user['mail'],PDO::PARAM_STR);
      $stmt2->execute();
      $id = $stmt2->fetchColumn();

      //User already exist on the database.. go to the next user
      if( $id > 0){
        echo 'user '.$user['mail'].' already exists! '. PHP_EOL;
        $user['new_id'] = $id;   
        continue;

      //User doesn't exist on the database.. we need to insert the user from the old database to the new one 
      }else{
        $arr = $ldap->getNewUserInfo($user['mail'],$user['name']);
        $user['new_dn'] = $arr['dn'];
        $user['new_uid'] = $arr['uid'];      

        $sql = "INSERT INTO glpi_users 
                (name,password,mobile,realname,firstname,locations_id,use_mode,is_active,auths_id,authtype,is_deleted,profiles_id,entities_id,usertitles_id,usercategories_id,user_dn,
                  is_deleted_ldap, picture, itil_layout, highcontrast_css, groups_id, users_id_supervisor, default_central_tab ) VALUES
                (:name, :password, :mobile, :realname, :firstname, :locations_id, :use_mode, :is_active, :auths_id, :authtype, :is_deleted, :profiles_id, :entities_id, :usertitles_id, :usercategories_id, :user_dn,
                  :is_deleted_ldap, :picture, :itil_layout, :highcontrast_css, :groups_id, :users_id_supervisor, :default_central_tab )";
        
        try{
          $conn_new->beginTransaction();

          $stmt2 = $conn_new->prepare($sql);
          $stmt2->bindParam(':name', $user['new_uid'],PDO::PARAM_STR);
          $stmt2->bindParam(':password', $user['password'],PDO::PARAM_STR);
          $stmt2->bindParam(':mobile', $user['mobile'],PDO::PARAM_STR);
          $stmt2->bindParam(':realname', $user['realname'],PDO::PARAM_STR);
          $stmt2->bindParam(':firstname', $user['firstname'],PDO::PARAM_STR);
          $stmt2->bindParam(':locations_id',$user['locations_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':use_mode', $user['use_mode'],PDO::PARAM_INT);
          $stmt2->bindParam(':is_active', $user['is_active'],PDO::PARAM_INT);
          $stmt2->bindParam(':auths_id', $user['auths_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':authtype', $user['authtype'],PDO::PARAM_INT);
          $stmt2->bindParam(':is_deleted', $user['is_deleted'],PDO::PARAM_INT);
          $stmt2->bindParam(':profiles_id', $user['profiles_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':entities_id', $user['entities_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':usertitles_id', $user['usertitles_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':usercategories_id', $user['usercategories_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':user_dn', $user['new_dn'], PDO::PARAM_STR);
          $stmt2->bindParam(':is_deleted_ldap', $user['is_deleted_ldap'],PDO::PARAM_INT);
          $stmt2->bindParam(':picture', $user['picture'],PDO::PARAM_STR);
          $stmt2->bindParam(':itil_layout', $user['itil_layout'],PDO::PARAM_STR);
          $stmt2->bindParam(':highcontrast_css', $user['highcontrast_css'],PDO::PARAM_INT);
          $stmt2->bindParam(':groups_id', $user['groups_id'],PDO::PARAM_INT);
          $stmt2->bindParam(':users_id_supervisor', $user['users_id_supervisor'],PDO::PARAM_INT);
          $stmt2->bindParam(':default_central_tab', $user['default_central_tab'],PDO::PARAM_INT);
          $stmt2->execute();
                  
          $user['new_id'] = $conn_new->lastInsertId();    

          $sql = "INSERT INTO glpi_useremails
                  (users_id, is_default, is_dynamic, email) VALUES
                  (:id, :is_default, :is_dynamic, :email)";
          $stmt2 = $conn_new->prepare($sql);
          $stmt2->bindParam(':id', $user['new_id'], PDO::PARAM_INT);
          $stmt2->bindParam(':is_default', $user['is_default'],PDO::PARAM_STR);
          $stmt2->bindParam(':is_dynamic', $user['is_dynamic'],PDO::PARAM_STR);
          $stmt2->bindParam(':email', $user['mail'],PDO::PARAM_STR);
          $stmt2->execute();

          $sql="SELECT id, users_id, profiles_id, entities_id, is_recursive, is_dynamic, is_default_profile FROM glpi_profiles_users where users_id=:id ;";
          $stmt = $conn_old->prepare($sql);
          $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
          $stmt->execute();
          $old_profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($old_profiles as $old_profile) {
          
            $sql = "INSERT INTO glpi_profiles_users
                    (users_id, profiles_id, entities_id, is_recursive, is_dynamic, is_default_profile) VALUES
                    (:id, :profiles_id, :entities_id, :is_recursive, :is_dynamic, :is_default_profile)";
            $stmt2 = $conn_new->prepare($sql);
            $stmt2->bindParam(':id', $user['new_id'], PDO::PARAM_STR);
            
            if ($old_profile['profiles_id'] == $ADMIN_ID || $old_profile['profiles_id'] == $SUPER_ADMIN_ID || $old_profile['profiles_id'] == $TECH_ID ){
              $stmt2->bindParam(':profiles_id', $TECH_ID,PDO::PARAM_STR);
              $stmt2->bindParam(':entities_id', $ENTITY_NEW_ID,PDO::PARAM_STR);
            }
            else{          
              $stmt2->bindParam(':profiles_id', $old_profile['profiles_id'],PDO::PARAM_STR);
              $stmt2->bindParam(':entities_id', $old_profile['entities_id'],PDO::PARAM_STR);
            }
            $stmt2->bindParam(':is_recursive', $old_profile['is_recursive'],PDO::PARAM_STR);
            $stmt2->bindParam(':is_dynamic', $old_profile['is_dynamic'],PDO::PARAM_STR);
            $stmt2->bindParam(':is_default_profile', $old_profile['is_default_profile'],PDO::PARAM_STR);
            $stmt2->execute();

          }
        
          $conn_new->commit();
        }
        catch(PDOException $e) {
          echo "Error: " . $e->getMessage().PHP_EOL;
          $conn_new->rollBack();
        }
      }
    }
    catch(PDOException $e) {
      echo "Error: " . $e->getMessage().PHP_EOL;
    }  
    //break;
  }

?>