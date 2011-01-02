<?
///////////////////////////////
//
// user-functions
//
///////////////////////////////

function ldap_users( $keys = array() ) {
  $keys['objectclass'] = 'posixaccount';
  return ldap_entries( 'ou=people,' . LDAP_BASEDN, $keys );
}

function ldap_accountdomains() {
  static $accountdomains;
  if( ! isset( $accountdomains ) ) {
    $accountdomains = array();
    $keys['accountdomain'] = '*';
    $keys['objectclass'] = 'posixaccount';
    $users = ldap_entries( 'ou=people,' . LDAP_BASEDN, $keys );
    for( $n = 0; $n < $users['count'] ; $n++ ) {
      $user = $users[$n];
      if( isset( $user['accountdomain'] ) ) {
        for( $i = 0; $i < $user['accountdomain']['count'] ; $i++ ) {
          $a = $user['accountdomain'][$i];
          if( isset( $accountdomains[$a] ) ) {
            $accountdomains[$a]['users']++;
          } else {
            $accountdomains[$a] = array();
            $accountdomains[$a]['users'] = 1;
            $accountdomains[$a]['hosts'] = 0;
          }
        }
      }
    }
    $keys['objectclass'] = 'physikhost';
    $hosts = ldap_entries( 'ou=hosts,' . LDAP_BASEDN, $keys );
    for( $n = 0; $n < $hosts['count'] ; $n++ ) {
      $host = $hosts[$n];
      if( isset( $host['accountdomain'] ) ) {
        for( $i = 0; $i < $host['accountdomain']['count'] ; $i++ ) {
          $a = $host['accountdomain'][$i];
          if( isset( $accountdomains[$a] ) ) {
            $accountdomains[$a]['hosts']++;
          } else {
            $accountdomains[$a] = array();
            $accountdomains[$a]['users'] = 0;
            $accountdomains[$a]['hosts'] = 1;
          }
        }
      }
    }
  }
  return $accountdomains;
}

function ldap_accountdomains_host( $hostdn ) {
  $host = ldap_entry( $hostdn );
  $r = array();
  if( isset( $host['accountdomain'] ) ) {
    for( $i = 0; $i < $host['accountdomain']['count'] ; $i++ ) {
      $r[] = $host['accountdomain'][$i];
    }
  }
  return $r;
}

function options_accountdomains(
  $selected = 0
, $option_0 = false
) {
  $output='';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( ! $selected ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . ">$option_0</option>";
  }
  foreach( ldap_accountdomains() as $name => $a ) {
    $output = "$output
      <option value='$name'";
    if( $selected == $name ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . "> $name </option>";
  }
  if( $selected >=0 ) {
    // $selected stand nicht zur Auswahl; vermeide zufaellige Anzeige:
    $output = "<option value='0' selected>(bitte accountdomain wählen)</option>" . $output;
  }
  return $output;
}

?>
