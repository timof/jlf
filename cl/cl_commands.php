<?php

function read_filters( $min_indent = 0, $op = '&&' ) {
  $line = '';
  while( true ) {
    if( ( $l = fgets( STDIN ) ) === false ) {
      break;
    }
    if( $l ) {
      $line .= ' ' . $l;
    }
  }
  return $line;
}


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
    // debug( $line, 'line' );
    // debug( $matches, 'matches' );
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

function cl_query( $args ) {
  global $verbose;
  $table = $args[ 2 ];
  $filters = $args[ 3 ];
  $selects = array();
  $opts = array();
  for( $i = 4; isset( $args[ $i ] ); $i++ ) {
    $a = $args[ $i ];
    if( ! $a )
      continue;
    $c = substr( $a, 0, 2 );
    $a = substr( $a, 2 );
    switch( $c ) {
      case 's:':
        $selects += explode( ',', $a );
        break;
      case 'o:':
        $opts['orderby'] = $a;
        break;
      default:
        error( "undefined command: $c", LOG_FLAG_INPUT, 'cli' );
    }
  }
  if( $filters === '-' ) {
    $filters = read_filters();
  }
  if( $verbose ) {
    debug( $filters, 'filters' );
  }
  if( function_exists( $n = "sql_$table" ) ) {
    $rows = $n( $filters, $opts );
  } else {
    if( $filters ) {
      $opts['filters'] = $filters;
    }
    $rows = sql_query( $table, $opts );
  }
  if( $rows ) {
    if( $selects ) {
      foreach( $rows as & $row ) {
        foreach( $row as $key => $value ) {
          if( ! in_array( $key, $selects ) ) {
            unset( $row[ $key ] );
          }
        }
      }
    }
    echo ldif_encode( $rows );
  } else {
    echo "(no match)";
  }
}

function cl_insert( $table ) {
  $id = 0;
  while( ( $values = read_record() ) ) {
    unset( $values[ $table.'_id' ] );
    debug( $values, "insert: $table:" );
    $id = sql_save( $table, 0, $values );
  }
  return $id;
}

function cl_update( $table, $id ) {
  global $tables;

  need( isset( $tables[ $table ] ), 'no such table' );
  $values = read_record();
  if( ! $id ) {
    need( ( $id = adefault( $values, $table.'_id' ) ), 'need primary key id for update' );
  }
  unset( $values[ $table.'_id' ] );
  if( $values ) {
    debug( $values, "update: {$table}[$id]:" );
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
