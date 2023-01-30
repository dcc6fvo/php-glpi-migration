<?php

// basic sequence with LDAP is connect, bind, search, interpret search
// result, close connection

class AuxLDAP {

    private $ldap_host = '';
    private $ldap_bind_user = '';
    private $ldap_bind_pass = '';
    private $ldap_base_dn = '';
    private $ldap_conn = '';
    
    public static $instance;


    public function __construct($host, $user, $pass, $basedn) 
    {
        $this->ldap_host = $host;
        $this->ldap_bind_user = $user;
        $this->ldap_bind_pass = $pass;
        $this->ldap_base_dn = $basedn;
        #print(" $this->ldap_host $this->ldap_bind_user $this->ldap_bind_pass ");

        $ldapconn = ldap_connect($this->ldap_host) or die("Could not connect to LDAP server." .PHP_EOL);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
        if (ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            echo "Using LDAPv3 \n";
        } else {
            echo "Failed to set protocol version to 3 ".PHP_EOL;
        }

        if ($ldapconn) {

            // binding to ldap server
            $ldapbind = ldap_bind($ldapconn, $this->ldap_bind_user, $this->ldap_bind_pass);
        
            // verify binding
            if ($ldapbind) {
                echo "LDAP $host bind successful...".PHP_EOL;
                $this->ldap_conn = $ldapconn;
            } else {
                echo "LDAP $host bind failed...".PHP_EOL;
            }
                
        }
    }

    public static function getInstance() {

        if (!isset(self::$instance))
            self::$instance = new AuxLDAP();

        return self::$instance;
    }

    public function closeConnection(){
        echo "Closing connection ".PHP_EOL;
        ldap_close($this->ldap_conn);
    }

    public function getNewUserInfo($email,$uid) {

        $ds = $this->ldap_conn;
        $base = $this->ldap_base_dn;

        if ($ds) {

            $sr=ldap_search($ds,$base,"mail=$email");
            $info = ldap_get_entries($ds, $sr);

            if ($info["count"] > 0)
                return array("dn" => $info[0]["dn"], "uid" => $info[0]["uid"][0]);

            else{
                echo "Couldn't find ".$email." in the new ldap base, returning null for DN ".PHP_EOL;
                return array("dn" => '', "uid" => "$uid");
            }    
        } else {
            echo "Unable to get information from LDAP server ".PHP_EOL;
        }

    }


    public function getNewUserDN2($mail) {

        $ds=ldap_connect("localhost");  // must be a valid LDAP server!
        echo "connect result is " . $ds ." ".PHP_EOL;

        if ($ds) {
            
            echo "Binding ...".PHP_EOL;
            
            $r=ldap_bind($ds);     // this is an "anonymous" bind, typically
                                // read-only access
            echo "Bind result is " . $r . "<br />";

            echo "Searching for (sn=S*) ...";
            // Search surname entry
            $sr=ldap_search($ds, "o=My Company, c=US", "sn=S*");
            echo "Search result is " . $sr . "<br />";

            echo "Number of entries returned is " . ldap_count_entries($ds, $sr) . "<br />";

            echo "Getting entries ...<p>";
            $info = ldap_get_entries($ds, $sr);
            echo "Data for " . $info["count"] . " items returned:<p>";

            for ($i=0; $i<$info["count"]; $i++) {
                echo "dn is: " . $info[$i]["dn"] . "<br />";
                echo "first cn entry is: " . $info[$i]["cn"][0] . "<br />";
                echo "first email entry is: " . $info[$i]["mail"][0] . "<br /><hr />";
            }

            echo "Closing connection";
            ldap_close($ds);

        } else {
            echo "<h4>Unable to connect to LDAP server</h4>";
        }

    }
}

?>
