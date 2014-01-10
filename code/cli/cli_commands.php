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

function cli_query( $args ) {
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
//   if( function_exists( $n = "sql_$table" ) ) {
//     sql_transaction_boundary('*');
//     $rows = $n( $filters, $opts );
//     sql_transaction_boundary('');
//   } else {
    if( $filters ) {
      $opts['filters'] = $filters;
    }
    sql_transaction_boundary('*');
      $rows = sql_query( $table, $opts );
    sql_transaction_boundary('');
//  }
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

function cli_insert( $table ) {
  $id = 0;
  while( ( $values = read_record() ) ) {
    unset( $values[ $table.'_id' ] );
    debug( $values, "insert: $table:" );
    sql_transaction_boundary( '', $table );
      $id = sql_insert( $table, $values );
    sql_transaction_boundary();
  }
  return $id;
}

function cli_update( $table, $id ) {
  global $tables;

  need( isset( $tables[ $table ] ), 'no such table' );
  $values = read_record();
  if( ! $id ) {
    need( ( $id = adefault( $values, $table.'_id' ) ), 'need primary key id for update' );
  }
  unset( $values[ $table.'_id' ] );
  if( $values ) {
    debug( $values, "update: {$table}[$id]:" );
    sql_transaction_boundary( '', $table );
      return sql_update( $table, $id, $values );
    sql_transaction_boundary();
  }
  return 0;
}

function cli_sql( $sql ) {
  global $verbose;

  sql_transaction_boundary('*');
  $result = sql_do( $sql );
  sql_transaction_boundary();
  if( $verbose ) {
    debug( $sql, 'sql' );
  }
  if( mysql_num_rows( $result ) > 0 ) {
    echo ldif_encode( mysql2array( $result ) );
  } else {
    echo "(no match)";
  }
}

function cli_garbage_collection( $type = 'generic-common', $application = '' ) {
  require_once( "code/garbage.php" );
  sql_transaction_boundary('*');
  switch( $type ) {
    case 'generic-common':
    case 'gc':
      sql_garbage_collection_generic_common();
      break;
    case 'generic-app':
    case 'ga':
      need( "$application" );
      sql_garbage_collection_generic_app( "application=$application" );
      break;
    case 'specific-app':
    case 'a':
      need( "$application" );
      $handler = "sql_garbage_collection_$application";
      $handler();
      break;
  }
  sql_transaction_boundary();
}

// cli_html_defuse(): to reproduce the effect of the htmlDefuse extfilter for scripts which output html over cli
//
function cli_html_defuse( $s ) {
  // str_replace() will iteratively replace already replaced substrings, so we need several calls:
  // we also replace szlig by html entity to protect in transport
  $s = str_replace( '&', '&amp;', $s );
  $s = str_replace(
    array( '\''    , '"'     , '<'   , '>'   , 'ß' )
  , array( '&#039;', '&quot;', '&lt;', '&gt;', '&szlig;' )
  , $s
  );
  return str_replace(
    array( "\x11", "\x12", "\x13", "\x14", "\x15" )
  , array( '\''  , '"'   , '<'   , '>'   , '&'    )
  , $s
  );
}

function cli_umlauts_defuse( $s ) {
  $s = str_replace(
    array( 'ä', 'Ä', 'ü', 'Ü', 'ö', 'ÖP', 'ß' )
  , array( 'ae', 'Ae', 'ue', 'Ue', 'oe', 'Oe', 'ss' )
  , $s
  );
  return $s;
}

?>
