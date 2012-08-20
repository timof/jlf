<?php

function read_record() {
  global $do_echo;
  $values = array();
  while( true ) {
    $line = fgets( STDIN );
    if( $line === false ) {
      return $values;
    }
    $line = str_replace( "\n", '', $line );
    if( $do_echo ) {
      echo "$line\n";
    }
    if( $line == '' ) {
      if( ! $values ) {
        continue;
      } else {
        return $values;
      }
    }
    preg_match( '/^([[:word:]]+):(:)?[[:space:]](.*)$/', $line, /* & */ $matches );
    if( count( $matches ) == 4 ) {
      $key = $matches[ 1 ];
      $value = $matches[ 3 ];
      switch( $matches[ 2 ] ) {
        case '':
          break;
        case ':':
          $value = base64_decode( $value );
          break;
        case 'h':
          $value = bin2hex( $value );
          break;
      }
      $values[ $key ] = $value;
    }
  }
}

function cl_query( $table ) {
  global $verbose;
  $opts = read_record();
  if( $verbose ) {
    debug( $opts, 'opts' );
  }
  $rows = sql_query( $table, $opts );
  if( $rows ) {
    echo ldif_encode( $rows );
  } else {
    echo "(no match)";
  }
}

function cl_insert( $table ) {
  $id = 0;
  while( ( $values = read_record() ) ) {
    unset( $values[ $table.'_id' ] );
    $id = sql_save( $table, 0, $values );
  }
  return $id;
}

function cl_update( $table, $id ) {
  $values = read_record();
  need( $id );
  if( $values ) {
    return sql_save( $table, $id, $values );
  }
  return 0;
}

function cl_sql( $sql ) {
  global $verbose;
  $result = sql_do( $sql );
  if( $verbose ) {
    debug( $sql, 'sql' );
  }
  if( mysql_num_rows( $result ) > 0 ) {
    echo ldif_encode( mysql2array( $result ) );
  } else {
    echo "(no match)";
  }
}


?>
