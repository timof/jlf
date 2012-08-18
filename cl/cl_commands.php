<?php

function read_record() {
  $values = array();
  while( true ) {
    $line = fgets( STDIN );
    if( $line === false ) {
      return $values;
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
  $opts = read_record();
  debug( $opts, 'opts' );
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
    $id = sql_insert( $table, $values );
    continue;
  }
  return $id;
}

function cl_update( $table, $id ) {
  $values = read_record();
  if( $values ) {
    return sql_update( $table, $id, $values );
  }
  return 0;
}

function cl_sql( $sql ) {
  $result = sql_do( $sql );
  if( mysql_num_rows( $result ) > 0 ) {
    echo ldif_encode( mysql2array( $result ) );
  } else {
    echo "(no match)";
  }
}


?>
