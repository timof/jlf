<?php
//
// setup tool
//
// This script must _not_ be accessible over the net during normal
// operation - it is for installation and maintenance only!
//
?><!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
  <title>Setup Tool</title>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' >
  <link rel='stylesheet' type='text/css' href='code/css.css'>
    <style type='text/css'>
      body, td, th {
        font-size:11pt;
      }
      table.list tr td {
        border:1px dotted #404040;
        padding: 0.4ex 1ex 0.4ex 1ex;
      }
    </style>
</head>
<body>
<h1>Setup Tool</h1>
<?

require_once('code/config.php');
require_once('code/basic.php');

$remote_ip = getenv('REMOTE_ADDR');
if( $allow_setup_from and ereg( '^'.$allow_setup_from, $remote_ip ) ) {
  true;
} else {
  ?>
    <div class='warn'>
      setup.php cannot be called from your IP, <? echo $remote_ip; ?>.
      this can be configured in <code>code/config.php</code>!
    </div>
  <?
  exit(1);
}

?>
<form name='setup_form' action='setup.rphp' method='post'>
<?

$details = 'check_5'; // default: edit config variables
if( isset( $_GET['details'] ) )
  $details = $_GET['details'];

$changes = array();
$js = '';
$problems = false;

function escape_val( $val ) {
  switch( $val ) {
    case 'CURRENT_TIMESTAMP';
      return $val;
    default:
      return "'$val'";
  }
}


function check_1() {
  //
  // (1) check server runtime environment:
  //

  $path = realpath( dirname( __FILE__ ) );
  $ruid = posix_getuid();
  $euid = posix_geteuid();
  $rgid = posix_getgid();
  $egid = posix_getegid();

  ?>
    <table class='list'>
      <tr>
        <th>Name / Port:</th>
        <td><? echo getenv( 'SERVER_NAME' ) . ' / ' . getenv( 'SERVER_PORT' );  ?></td>
      </tr>
      <tr>
        <th>Software:</th>
        <td><? echo getenv( 'SERVER_SOFTWARE' ); ?></td>
      </tr>
      <tr>
        <th>Pfad:</th>
        <td><? echo $path; ?></td>
      </tr>
      <tr>
        <th>ruid / euid:</th>
        <td><? echo $ruid . ' / ' . $euid; ?></td>
      </tr>
      <tr>
        <th>rgid / egid:</th>
        <td><? echo $rgid . ' / ' . $egid; ?></td>
      </tr>
    </table>
  <?
  return 0;
}

function check_2() {
  //
  // (2) check file system layout, permissions, ... (TODO: this is incomplete!)
  //
  global $jlf_application_name, $jlf_application_instance;

  function check_dir( $path ) {
    echo "check_dir: $path<br>";
    if( $path == 'CVS' or $path == 'attic' ) {
  
    }
    return true;
  }

  function check_file( $path ) {

    echo "check_file: $path<br>";

    return true;
  }

  function recurse_dir( $path ) {
    global $foodsoftdir, $ruid, $rgid, $euid, $egid;
    $dir = opendir( $path );
    if( $dir === FALSE ) {
      ?>
        <tr>
          <th class='warn'>
            Problem: cannot access directory
          </th>
          <td><kbd><? echo $path; ?></kbd></td>
        </tr>
        <tr>
          <td colspan='2' class='alert'>
            Suggestion:
                <? echo $foodsoftdir; ?> and all subdirectories below should have read and execute permission,
                but no write permissions, for the apache server process.
          </td>
        </tr>
      <?
      return false;
    }
    echo "hello";
    $ok = check_dir( $path );
    while( $path = readdir( $dir ) ) {
      echo "readdir: $path<br>";
      if( $path == '.' or $path == '..' )
        continue;
      if( is_dir( $path ) ) {
        $ok = $ok && recurse_dir( $path );
      } else {
        $ok = $ok && check_file( $path );
      }
    }
    return $ok;
  }

  $problems = false;

  // tut noch nichts:
  // recurse_dir( $foodsoft_path );

  ?>
    <table class='list'>
      <tr>
        <th>Application name:</th>
        <? if( isset( $jlf_application_name ) ) { ?>
          <td class='ok'><? echo $jlf_application_name; ?></td>
        <? } else { $problems = true; ?>
          <td class='warn'>$jlf_application_name not set</td>
        <? } ?>
      </tr>
      <tr>
        <th>Application instance:</th>
        <? if( isset( $jlf_application_instance ) ) { ?>
          <td class='ok'><? echo $jlf_application_instance; ?></td>
        <? } else { $problems = true; ?>
          <td class='warn'>$jlf_application_instance not set</td>
        <? } ?>
      </tr>
      <tr>
        <th>Application directory:</th>
        <? if( is_dir( $jlf_application_name ) ) { ?>
          <td class='ok'><? echo "directory $jlf_application_name exists"; ?></td>
        <? } else { $problems = true; ?>
          <td class='warn'><? echo "directory $jlf_application_name not found"; ?></td>
        <? } ?>
      </tr>
      <tr>
        <th>DB structure definition:</th>
        <? if( is_readable( "$jlf_application_name/structure.php" ) ) { ?>
          <td class='ok'><? echo "$jlf_application_name/structure.php exists and is readable"; ?></td>
        <? } else { $problems = true; ?>
          <td class='warn'><? echo "cannot access $jlf_application_name/structure.php"; ?></td>
        <? } ?>
      </tr>
      <tr>
        <th>Application master config file:</th>
        <? if( is_readable( "$jlf_application_name/leitvariable.php" ) ) { ?>
          <td class='ok'><? echo "$jlf_application_name/leitvariable.php exists and is readable"; ?></td>
        <? } else { $problems = true; ?>
          <td class='warn'><? echo "cannot access $jlf_application_name/leitvariable.php"; ?></td>
        <? } ?>
      </tr>
    </table>
  <?
  // echo "(Baustelle! Hier werden bisher noch keine tests durchgefuehrt)";

  return $problems;
}

function check_3() {
  //
  // (3) check MySQL server connection
  //
  global $jlf_mysql_db_server, $jlf_mysql_db_name, $jlf_mysql_db_user, $jlf_mysql_db_password;

  $problems = false;
  do {
    ?>
      <table class='list'>
        <tr>
          <th>server:</th>
            <? if( isset( $jlf_mysql_db_server ) ) { ?>
              <td class='ok'><? echo $jlf_mysql_db_server; ?></td>
            <? } else { $problems = true; ?>
              <td class='warn'>$jlf_mysql_db_server not set</td>
            <? } ?>
          </td>
        </tr>
        <tr>
          <th>database:</th>
            <? if( isset( $jlf_mysql_db_name ) ) { ?>
              <td class='ok'><? echo $jlf_mysql_db_name; ?></td>
            <? } else { $problems = true; ?>
              <td class='warn'>$jlf_mysql_db_name not set</td>
            <? } ?>
          </td>
        </tr>
        <tr>
          <th>user:</th>
            <? if( isset( $jlf_mysql_db_user ) ) { ?>
              <td class='ok'><? echo $jlf_mysql_db_user; ?></td>
            <? } else { $problems = true; ?>
              <td class='warn'>$jlf_mysql_db_user not set</td>
            <? } ?>
          </td>
        </tr>
        <tr>
          <th>password:</th>
          <? if( isset( $jlf_mysql_db_password ) ) { ?>
            <td class='ok'>(a password is set)</td>
          <? } else { $problems = true; ?>
            <td class='warn'>$jlf_mysql_db_pwd not set</td>
          <? } ?>
        </tr>
    <?
    if( $problems )
      break;
    ?>
      <tr>
        <th>mysql_connect():</th>
    <?
    $db = mysql_connect( $jlf_mysql_db_server, $jlf_mysql_db_user, $jlf_mysql_db_password );
    if( $db ) {
      ?> <td class='ok'>connection to MySQL server OK </td></tr> <?
    } else {
      ?>
        <td class='warn'>
          connection to MySQL server failed:
          <div class='warn'><? echo mysql_error(); ?></div>
        </dt>
      <?
      $problems = true;
    }
    ?> </tr> <?
    if( $problems )
      break;

    ?>
      <tr>
        <th>mysql_select_db():</th>
    <?
    $db_selected = mysql_select_db( $jlf_mysql_db_name, $db );
    if( $db_selected ) {
      ?> <td class='ok'>connection to database OK </td></tr> <?
    } else {
      ?>
        <td class='warn'>
          connection to database failed:
          <div class='warn'><? echo mysql_error(); ?></div>
        </dt>
      <?
      $problems = true;
    }
    ?> </tr> <?
  } while( 0 );

  ?> </table> <?

  if( $problems ) {
    ?>
      <div class='alert'>
        cannot access database.
        please check settings in code/config.php!
      </div>
    <?
  }

  return $problems;
}

function check_4() {
  global $tables, $changes, $jlf_application_name;
  //
  // (4) database connection established: check tables, columns, indices:
  //

  $problems = false;
  require_once( "code/structure.php" );
  $jlf_tables = $tables;
  require_once( "$jlf_application_name/structure.php" );
  $tables = tree_merge( $jlf_tables, $tables );
  expand_table_macros();
  foreach( $tables as $name => $table ) {
    foreach( $table['cols'] as $col => $props ) {
      if( isset( $props['sql_default'] ) ) {
        $tables[$name]['cols'][$col]['default'] = $props['sql_default'];
      } else if( ! isset( $props['default'] ) ) {
        $tables[$name]['cols'][$col]['default'] = '';
      }
      if( ! isset( $props['null'] ) ) {
        $tables[$name]['cols'][$col]['null'] = 'NO';
      }
      if( ! isset( $props['collation'] ) ) {
        if( preg_match( '/text|char/', $props['sql_type'] ) ) {
          $tables[$name]['cols'][$col]['collation'] = 'ascii_bin';
        } else {
          $tables[$name]['cols'][$col]['collation'] = NULL;
        }
      }
    }
  }

  function add_table( $want_table ) {
    global $tables, $changes;
    $s = "CREATE TABLE `$want_table` ( \n";
    $komma = ' ';
    foreach( $tables[$want_table]['cols'] as $col => $props ) {
      $s .= "$komma `$col` {$props['sql_type']} ";
      if( ( $collation = $props['collation'] ) ) {
        $charset = preg_replace( '/_.*$/', '', $collation );
        $s .= "CHARACTER SET $charset COLLATE $collation ";
      }
      if( isset( $props['null'] ) && ( $props['null'] != 'NO' ) ) {
        $s .= 'NULL ';
      } else {
        $s .= 'NOT NULL ';
      }
      if( isset( $props['default'] ) && ( $props['default'] !== '' ) ) {
        $s .= 'default ' . escape_val( $props['default'] ) .' ';
      }
      if( isset( $props['extra'] ) ) {
        $s .= $props['extra'];
      }
      $s .= "\n";
      $komma = ',';
    }
    foreach( $tables[$want_table]['indices'] as $want_index => $props ) {
      $collist = $props['collist'];
      $cols = explode( ',', $collist );
      $comma = '';
      $collist = '';
      foreach( $cols as $c ) {
        $c = trim( $c );
        preg_match( '/^([a-zA-Z0-9_]+)([(]\d+[)])?$/', $c, & $matches );
        $collist .= $comma.'`'.$matches[1].'`';
        if( isset( $matches[ 2 ] ) ) {
          $collist .= $matches[ 2 ];
        }
        $comma = ', ';
      }
      if( $want_index == 'PRIMARY' ) {
        $s .= ", PRIMARY KEY ( $collist ) ";
      } else {
        $s .= ', ';
        if( $props['unique'] ) {
          $s .= "UNIQUE ";
        }
        $s .= "KEY `$want_index` ( $collist ) ";
      }
    }
    $s .= ') ENGINE=MyISAM  DEFAULT CHARSET=utf8;';
    $changes[] = $s;
  }

  function add_col( $want_table, $want_col, $op = 'ADD' ) {
    global $tables, $changes;
    $col = $tables[$want_table]['cols'][$want_col];
    $type = $col['sql_type'];
    $null = ( $col['null'] == 'NO' ? 'NOT NULL' : 'NULL' );
    $default = ( ( isset( $col['default'] ) && ( $col['default'] !== '' ) ) ? "default " . escape_val( $col['default'] ) : '' );
    $extra = ( isset( $col['extra'] ) ? $col['extra'] : '' );
    if( ( $collation = $col['collation'] ) ) {
      $charset = preg_replace( '/_.*$/', '', $collation );
      $collation = "CHARACTER SET $charset COLLATE $collation ";
    } else {
      $collation = '';
    }
    $s = " ALTER TABLE $want_table $op COLUMN `$want_col` $type $collation $null $default $extra;";
    $changes[] = $s;

  }

  function add_index( $want_table, $want_index ) {
    global $tables, $changes;
    $index = $tables[$want_table]['indices'][$want_index];
    $collist = $index['collist'];
    $cols = explode( ',', $collist );
    $comma = '';
    $collist = '';
    foreach( $cols as $c ) {
      $c = trim( $c );
      preg_match( '/^([a-zA-Z0-9_]+)([(]\d+[)])?$/', $c, & $matches );
      $collist .= $comma.'`'.$matches[1].'`';
      if( isset( $matches[ 2 ] ) ) {
        $collist .= $matches[ 2 ];
      }
      $comma = ', ';
    }
    $s = " ALTER TABLE $want_table ADD ";
    if( $want_index == 'PRIMARY' ) {
      $s .= "PRIMARY KEY ( $collist )";
    } else {
      if( $index['unique'] ) {
        $s .= "UNIQUE ";
      }
      $s .= "KEY `$want_index` ( $collist );";
    }
    $changes[] = $s;
  }

  function delete_table( $table ) {
    global $changes;
    $changes[] = "DROP TABLE $table; ";
  }

  function delete_col( $table, $col ) {
    global $changes;
    $changes[] = "ALTER TABLE $table DROP $col;";
  }
  function delete_index( $table, $index ) {
    global $changes;
    if( $index == 'PRIMARY' )
      $changes[] = "ALTER TABLE $table DROP PRIMARY KEY;";
    else
      $changes[] = "ALTER TABLE $table DROP INDEX $index;";
  }

  function fix_col( $table, $col ) {
    add_col( $table, $col, 'MODIFY' );
  }
  function fix_index( $table, $index ) {
    delete_index( $table, $index );
    add_index( $table, $index );
  }

  if( $_POST['action'] == 'repair' ) {
    foreach( $_POST as $name => $value ) {
      $v = explode( '_', $name );
      switch( $v[0] ) {
        case 'add':
          switch( $v[1] ) {
            case 'table':
              add_table( $_POST['table_'.$v[2]] );
              break;
            case 'col':
              add_col( $_POST['table_'.$v[2] ], $_POST['col_'.$v[2]] );
              break;
            case 'index':
              add_index( $_POST['table_'.$v[2] ], $_POST['index_'.$v[2]] );
              break;
          }
          break;
        case 'delete':
          switch( $v[1] ) {
            case 'table':
              delete_table( $_POST['table_'.$v[2]] );
              break;
            case 'col':
              delete_col( $_POST['table_'.$v[2] ], $_POST['col_'.$v[2]] );
              break;
            case 'index':
              delete_index( $_POST['table_'.$v[2] ], $_POST['index_'.$v[2]] );
              break;
          }
          break;
        case 'fix':
          switch( $v[1] ) {
            case 'col':
              fix_col( $_POST['table_'.$v[2] ], $_POST['col_'.$v[2]] );
              break;
            case 'index':
              fix_index( $_POST['table_'.$v[2] ], $_POST['index_'.$v[2]] );
              break;
          }
          break;
      }
    }
  }
  if( count( $changes ) > 0 )
    return 0;

  ?> <table class='list'> <?

  $thead = "
    <tr>
      <th>column</th>
      <th>type</th>
      <th>null</th>
      <th>default</th>
      <th>collation</th>
      <th>extra</th>
      <th>status</th>
    </tr>
  ";
  $ihead = "
    <tr>
      <th>name</th>
      <th colspan='4'>column(s)</th>
      <th>unique</th>
      <th>status</th>
    </tr>
  ";

  $id = 0;
  foreach( $tables as $table => $want ) {
    ?><tr><th colspan='7' style='padding-top:1em;text-align:center;'>table: <? echo $table; ?></th></tr><?

    $sql = "SHOW FULL COLUMNS FROM $table; ";
    $result = mysql_query( $sql );
    if( ! $result ) {
      ?>
        <tr>
          <td class='warn' colspan='6'>
            failed: <code><? echo $sql; ?></code>
          </td>
          <td class='warn' style='text-align:right;'>
            create table? <input type='checkbox' name='add_table_<? echo $id; ?>'>
            <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
          </td>
        </tr>
      <?
      $problems = true;
      $id++;
      continue;
    }
    echo $thead;
    $want_cols = $want['cols'];
    $want_indices = $want['indices'];
    while( $row = mysql_fetch_array( $result ) ) {
      $field = $row['Field'];
      ?>
        <tr>
          <td><? echo $field; ?></td>
          <td><? echo $row['Type']; ?></td>
          <td><? echo $row['Null']; ?></td>
          <td><? echo $row['Default']; ?></td>
          <td><? echo $row['Collation']; ?></td>
          <td><? echo $row['Extra']; ?></td>
      <?
      if( isset( $want_cols[$field] ) ) {
        $want_col = $want_cols[$field];
        $s = '';
        $mismatch = false;
        if( $want_col['sql_type'] != $row['Type'] ) {
          $mismatch = true;
          $s .= "<td class='warn'>{$want_col['sql_type']}</td>";
        } else {
          $s .= "<td>&nbsp;</td>";
        }
        if( $want_col['null'] != $row['Null'] ) {
          $mismatch = true;
          $s .= "<td class='warn'>{$want_col['null']}</td>";
        } else {
          $s .= "<td>&nbsp;</td>";
        }
        if( $want_col['default'] != $row['Default'] ) {
          $mismatch = true;
          $s .= "<td class='warn'>{$want_col['default']}</td>";
        } else {
          $s .= "<td>&nbsp;</td>";
        }
        if( ( $row['Collation'] !== NULL ) && ( $want_col['collation'] != $row['Collation'] ) ) {
          $mismatch = true;
          $s .= "<td class='warn'>{$want_col['collation']}</td>";
        } else {
          $s .= "<td>&nbsp;</td>";
        }
        if( $want_col['extra'] != $row['Extra'] ) {
          $mismatch = true;
          $s .= "<td class='warn'>{$want_col['extra']}</td>";
        } else {
          $s .= "<td>&nbsp;</td>";
        }
        if( $mismatch ) {
          ?>
              <td class='warn'>error</td>
            </tr>
            <tr>
              <td class='alert' style='text-align:right;'>should be:</td>
              <? echo $s; ?>
              <td class='alert' style='text-align:right;'>
                fix column? <input type='checkbox' name='fix_col_<? echo $id; ?>'>
              <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
              <input type='hidden' name='col_<? echo $id; ?>' value='<? echo $field; ?>'>
              </td>
            </tr>
          <?
          $problems = true;
          $id++;
        } else {
          ?>
            <td class='ok'>OK</td>
            </tr>
          <?
        }
        unset( $want_cols[$field] );
      } else {
        ?>
            <td class='alert' style='text-align:right;'>
              column not required; delete? <input type='checkbox' name='delete_col_<? echo $id; ?>'>
              <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
              <input type='hidden' name='col_<? echo $id; ?>' value='<? echo $field; ?>'>
            </td>
          </tr>
        <?
        $id++;
      }
    }
    foreach( $want_cols as $want_col => $want_props ) {
      ?>
        <tr>
          <td class='warn'><? echo $want_col; ?></td>
          <td class='warn'><? echo $want_props['sql_type']; ?></td>
          <td class='warn'><? echo $want_props['null']; ?></td>
          <td class='warn'><? echo $want_props['default']; ?></td>
          <td class='warn'><? echo $want_props['extra']; ?></td>
          <td class='alert' style='text-align:right;'>
            missing column; add? <input type='checkbox' name='add_col_<? echo $id; ?>'>
            <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
            <input type='hidden' name='col_<? echo $id; ?>' value='<? echo $want_col; ?>'>
          </td>
        </tr>
      <?
      $problems = true;
      $id++;
    }

    ?><tr><th colspan='7' style='text-align:left;'>indices:</th></tr><?
    echo $ihead;
    $result = mysql_query( "SHOW INDEX FROM $table; " );
    $iname = '';
    $icols = '';
    while( ( $row = mysql_fetch_array( $result ) ) or $iname ) {
      if( $row and ( $iname == $row['Key_name'] ) ) {
        $icols .= ", {$row['Column_name']}";
      } else {
        if( $iname ) {
          ?>
            <tr>
              <td><? echo $iname; ?></td>
              <td colspan='4'><? echo $icols; ?></td>
              <td><? echo $iunique; ?></td>
          <?
          if( isset( $want_indices[$iname] ) ) {
            $want_index = $want_indices[$iname];
            $s = '';
            $mismatch = false;
            if( preg_replace( '/[(]\d+[)]/', '', $want_index['collist'] ) != $icols ) {
              $mismatch = true;
              $s .= "<td class='warn' colspan='4'>{$want_index['collist']}</td>";
            } else {
              $s .= "<td colspan='4'>&nbsp;</td>";
            }
            if( $want_index['unique'] != $iunique ) {
              $mismatch = true;
              $s .= "<td class='warn'>{$want_index['unique']}</td>";
            } else {
              $s .= "<td>&nbsp;</td>";
            }
            if( $mismatch ) {
              ?>
                  <td class='warn'>Fehler</td>
                </tr>
                <tr>
                  <td class='alert' style='text-align:right;'>Sollwert:</td>
                  <? echo $s; ?>
                  <td class='alert' style='text-align:right;'>
                    fix index?  <input type='checkbox' name='fix_index_<? echo $id; ?>'>
                  <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
                  <input type='hidden' name='index_<? echo $id; ?>' value='<? echo $iname; ?>'>
                  </td>
                </tr>
              <?
              $problems = true;
              $id++;
            } else {
              ?>
                <td class='ok'>OK</td>
                </tr>
              <?
            }
            unset( $want_indices[$iname] );
          } else {
            ?>
                <td class='alert' style='text-align:right;'>
                  index not required; delete? <input type='checkbox' name='delete_index_<? echo $id; ?>'>
                  <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
                  <input type='hidden' name='index_<? echo $id; ?>' value='<? echo $iname; ?>'>
                </td>
              </tr>
            <?
            $id++;
          }
        }
        if( $row ) {
          $iname = $row['Key_name'];
          $icols = $row['Column_name'];
          $iunique = ( $row['Non_unique'] == '0' ? 1 : 0 );
        } else {
          $iname = '';
        }
      }
    }
    foreach( $want_indices as $want_index => $want_props ) {
      ?>
        <tr>
          <td class='warn'><? echo $want_index; ?></td>
          <td class='warn' colspan='4'><? echo $want_props['collist']; ?></td>
          <td class='warn'><? echo $want_props['unique']; ?></td>
          <td class='alert' style='text-align:right;'>
            missing index; add? <input type='checkbox' name='add_index_<? echo $id; ?>'>
            <input type='hidden' name='table_<? echo $id; ?>' value='<? echo $table; ?>'>
            <input type='hidden' name='index_<? echo $id; ?>' value='<? echo $want_index; ?>'>
          </td>
        </tr>
      <?
      $problems = true;
      $id++;
    }
    ?><tr><td colspan='7' style='text-align:left;'>&nbsp;</td></tr><?
  }

  ?> </table> <?

  return $problems;
}

function check_5() {
  global $leitvariable, $changes, $jlf_application_name;
  //
  // (5) setup leitvariable database:
  //

  $problems = false;
  require_once( "code/leitvariable.php" );
  $jlf_leitvariable = $leitvariable;
  require_once( "$jlf_application_name/leitvariable.php" );
  $leitvariable = tree_merge( $jlf_leitvariable, $leitvariable );
  $id = 1;

  if( $_POST['action'] == 'repair' ) {
    foreach( $_POST as $name => $value ) {
      $v = explode( '_', $name );
      if( $v[0] != 'leit' )
        continue;
      $action = $v[1];
      $id = $v[2];
      $name = $_POST['leit_name_'.$id];
      switch( $action ) {
        case 'set':
          $value = $_POST['leit_value_'.$id];
          $props = $leitvariable['name'];
          $local = $props['local'];
          $runtime_editable = $props['runtime_editable'];
          $changes[] .= "
            INSERT INTO leitvariable ( name, value )
            VALUES ( '$name', '$value' )
            ON DUPLICATE KEY UPDATE value = '$value';
          ";
          break;
        case 'delete':
          $name = $_POST['leit_name_'.$id];
          $changes[] = "DELETE FROM leitvariable WHERE name='$name';";
      }
    }
  }
  if( count( $changes ) > 0 )
    return 0;

  ?>
  <table class='list'>
    <tr>
      <th>variable</th>
      <th>meaning</th>
      <th>value</th>
      <th>action</th>
    </tr>
  <?

  for( $runtime_editable = 0; $runtime_editable <= 1; ++$runtime_editable ) {
    if( $runtime_editable ) {
      ?>
        <th colspan='4'>runtime configuration (stored in database):
          <div class='small'>can be adjusted at any time</div>
        </th>
      <?
    } else {
      ?>
        <th colspan='4'>installation-time configuration (stored in database):
          <div class='small'>do not change during production use</div>
        </th>
      <?
    }
    foreach( $leitvariable as $name => $props ) {
      if( $props['runtime_editable'] != $runtime_editable )
        continue;
      $need_entry = 0;
      $need_change = 0;
      $checked = '';
      $rows = ( isset($props['rows']) ? $props['rows'] : 1 );
      $cols = ( isset($props['cols']) ? $props['cols'] : 20 );
      ?>
        <tr>
          <th><? echo $name; ?></th>
          <td>
          <?
            echo $props['meaning'];
            if( isset( $props['comment'] ) )
              echo "<div class='small'>".$props['comment']."</div>";
            $readonly = '';
            if( isset( $props['readonly'] ) ) {
              if( $props['readonly'] ) {
                $readonly = "readonly='readonly'";
              }
            }
            if( $readonly ) {
              $value = $props['default'];
            } else {
              $result = mysql_query( "SELECT * FROM leitvariable WHERE name='$name'" );
              if( $result and ( $row = mysql_fetch_array( $result ) ) ) {
                $value = $row['value'];
                if( isset( $props['pattern'] ) ) {
                  if( ! preg_match( $props['pattern'], $value ) ) {
                    ?>
                      <div class='warn'>
                        illegal value - please correct!
                        <br>
                        pattern: <? echo htmlspecialchars( $props['pattern'], ENT_QUOTES ); ?>
                      </div>
                    <?
                    $need_change = 1;
                    $problems = true;
                  }
                }
              } else {
                $value = $props['default'];
                ?><div class='warn'>not yet in database!</div><?
                $need_entry = 1;
                $problems = true;
              }
            }
          ?>
          </td><td>
            <? if( $rows > 1 ) { ?>
              <textarea name='leit_value_<? echo $id; ?>' rows='<? echo $rows; ?>' cols='<? echo $cols; ?>'
                onchange="document.getElementById('checkbox_<? echo $id; ?>').checked = true;"
                <? echo $readonly; ?>
              ><? echo $value; ?></textarea>
            <? } else { ?>
              <input type='text' name='leit_value_<? echo $id; ?>' size='<? echo $cols; ?>' value='<? echo $value; ?>'
                onchange="document.getElementById('checkbox_<? echo $id; ?>').checked = true;"
                <? echo $readonly; ?>
              />
            <? } ?>
            <input type='hidden' name='leit_name_<? echo $id; ?>' value='<? echo $name; ?>'>
          </td><td>
            <?
              if( $readonly ) {
                echo "(read only)";
              } else if( $need_entry ) {
                echo "<input id='checkbox_$id' type='checkbox' name='leit_set_$id' value='set' checked='checked'> insert?";
              } else if( $need_change ) {
                echo "<input id='checkbox_$id' type='checkbox' name='leit_set_$id' value='set' checked='checked'> change?";
              } else {
                echo "<input id='checkbox_$id' type='checkbox' name='leit_set_$id' value='set'> change?";
              }
            ?>
          </td>
        </tr>
      <?
      $id++;
    }
  }

  $result = mysql_query( "SELECT * FROM leitvariable" );
  $header_written = false;
  while( $row = mysql_fetch_array( $result ) ) {
    if( isset( $leitvariable[$row['name']] ) )
      continue;
    if( ! $header_written ) {
      ?><th colspan='3' class='alert'>unexpected configuration variable found:
          <div class='small'>(please delete to avoid side effects)</div>
        </th><?
      $header_written = true;
    }
    ?>
      <tr>
        <th><? echo $row['name']; ?></th>
        <td class='alert'>(meaning undefined)</td>
        <td><? echo $row['value']; ?></td>
        <td>
          <input type='hidden' name='leit_name_<? echo $id; ?>' value='<? echo $row['name']; ?>'>
          <input type='checkbox' name='leit_delete_<? echo $id; ?>' value='delete'> delete?
        </td>
      </tr>
    <?
    $problems = true;
    $id++;
  }

  ?> </table> <?

  return $problems;
}

function check_6() {
  global $changes;
  $problems = false;

  if( isset( $_POST['add_person'] ) ) {
    $person_cn = $_POST['person_cn'];
    $person_uid = $_POST['person_uid'];
    $password = $_POST['person_password'];
    if( $password ) {
      $urandom_handle = fopen( '/dev/urandom', 'r' );
      $bytes = 4;
      $salt = '';
      while( $bytes > 0 ) {
        $c = fgetc( $urandom_handle );
        $salt .= sprintf( '%02x', ord($c) );
        $bytes--;
      }
      $hash = crypt( $password, $salt );
      $changes[] = "INSERT INTO people (
            cn
          , uid
          , authentication_methods
          , password_hashvalue
          , password_salt
          , password_hashfunction
        ) VALUES (
          '$person_cn'
        , '$person_uid'
        , 'simple'
        , '$hash'
        , '$salt'
        , 'crypt'
        )
      ";
    } else {
      $changes[] = "INSERT INTO people (
            cn
          , uid
          , authentication_methods
        ) VALUES (
          '$person_cn'
        , '$person_uid'
        , 'ssl'
        )
      ";
    }
  }
  if( $changes )
    return false;

  ?>
    <table class='list'>
      <tr>
        <th colspan='2'>user list</th>
      </tr>
      <tr>
        <td>current status:</td>
  <?

  $result = mysql_query( "SELECT count(*) as count FROM people " );
  $row = mysql_fetch_array( $result );
  $num = $row['count'];
  if( $num > 0 ) {
    ?>
      <td class='ok'><? echo $num; ?> users in database</td>
      </tr>
    <?
  } else {
    $problems = true;
    ?>
      <td class='warn'>no users yet</td>
        <div class='small'>
          to bootstrap the application, please make sure to have at least one user in the database!
        </div>
      </td>
      </tr>
    <?
  }
  ?>
      <tr>
        <td>add user:</td>
        <td>
          uid: <input type='text' size='4' value='admin' name='person_uid'>
          &nbsp;
          common name: <input type='text' size='20' value='admin' name='person_cn'>
          &nbsp;
          password: <input type='text' size='20' value='' name='person_password'>
          &nbsp;
          insert? <input type='checkbox' name='add_person' value='add_person'>
          <div style='padding:1em;' class='small'>
            <ul>
              <li>
                use this form only to add a user to the database, if you cannot login
                (no users at all, password forgotten, ...).
              </li>
              <li>
                if you specify a non-empty password, the only allowed authenticaton method will be
                <b title='ordinary user + password authentification'>simple</b>.
                <br>
                if password is left empty, the only allowed authenticaton method will be
                <b title='authentication by client certificate (only works over SSL/TLS)'>ssl</b>.
              </li>
            </ul>
          </div>
        </td>
      </tr>
    </table>
  <?

  return $problems;
}


$checks = array(
  'check_1' => 'HTTP server / run time environment'
, 'check_2' => 'installed files and directories, access privileges'
, 'check_3' => 'connection to MySQL server'
, 'check_4' => 'database structure'
, 'check_5' => 'configuration variables'
, 'check_6' => 'user database'
);

foreach( $checks as $f => $title ) {
  ?>
    <h2 style='padding:1em 0em 0ex 0em;'><? echo $title; ?>:</h2>
    <div id='details_<? echo $f; ?>' style='display:none;'>
      <?  $result = $f(); ?>
    </div>
    <div id='nodetails_<? echo $f; ?>' style='display:block;'>
      <?
        if( $result ) {
          ?> <div class='warn' style='padding:1ex;'> ERROR <?
        } elseif( $changes ) {
          ?> <div class='alert' style='padding:1ex;'> applying changes... <?
        } else {
          ?> <div class='ok' style='padding:1ex;'> no errors detected <?
        }
      ?>
      <a href='setup.rphp?details=<? echo $f; ?>' style='margin:1ex;'>details...</a></div>
    </div>
  <?
  if( $result or ( $f == $details ) ) {
    $js .= "
      document.getElementById('details_$f').style.display = 'block';
      document.getElementById('nodetails_$f').style.display = 'none';
    ";
  } else {
    $js .= "
      document.getElementById('details_$f').style.display = 'none';
      document.getElementById('nodetails_$f').style.display = 'block';
    ";
  }
  if( $result ) {
    $problems = true;
    break;
  }
  if( count( $changes ) > 0 ) {
    break;
  }
}

if( count( $changes ) > 0 ) {
  ?>
    <h3 clas='alert' style='padding-top:2em;'>changes to database:</h3>
    <table class='list'>
      <tr>
        <th>SQL query:</th>
        <th>result:</th>
      </tr>
  <?
  foreach( $changes as $s ) {
    ?>
      <tr>
        <td><pre> <? echo htmlspecialchars( "$s\n", ENT_QUOTES ); ?></pre></td>
    <?
    $result = false;
    $result = mysql_query( $s );
    if( $result ) {
      ?>
        <td class='ok'>OK</td>
        </tr>
      <?
    } else {
      ?>
        <td class='warn'>
          failed:
          <div><? echo mysql_error(); ?></div>
        </td>
        </tr>
      <?
      $problems = true;
      break;
    }
  }
  ?> </table> <?
}

if( count( $changes ) == 0 ) {
  ?>
  <input type='hidden' name='action' value='repair'>
  <div style='text-align:left;padding:1em 1em 2em 1em;'>
    <input type='submit' style='padding:1ex;' value='submit' title='save / apply changes'>
  </div>
  <?
} else {
  ?>
  <div style='text-align:left;padding:1em 1em 2em 1em;'>
    <input type='submit' style='padding:1ex;' value='reload page'>
  </div>
  <?
}

?>

</form>
<script type='text/javascript'>
  <? echo $js; ?>
</script>
</body>
</html>

