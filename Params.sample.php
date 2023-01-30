<?php

$DB_OLD_ADDRESS='old_db.example.com';
$DB_OLD_USER='admin';
$DB_OLD_PASS='xxxxxx';
$DB_OLD_DATABASE='glpi';

$DB_NEW_ADDRESS='new_db.example.com';
$DB_NEW_USER='glpi';
$DB_NEW_PASS='yyyyyy';
$DB_NEW_DATABASE='glpi';

$DB_OLD_LDAP_ID='1';
$DB_NEW_LDAP_ID='1';

$ENTITY_OLD_ID='0';
$ENTITY_NEW_ID='2';

//Default port 389
$LDAP_NEW_HOST = 'new_ldap.example.com';
$LDAP_NEW_BIND_USER = 'cn=guest,dc=example,dc=com';
$LDAP_NEW_BIND_PASS = 'zzzzzzzzz';
$LDAP_NEW_BASEDN = 'ou=people,dc=example,dc=com';

//Don't import these users
$EXCLUDE_USERS = array("glpi","post-only","tech","normal");



?>