<?php


////////////////////////////////////
//
// people-funktionen
//
////////////////////////////////////

// sql_people(): use the default for the time being


function sql_delete_people( $filters, $opts = array() ) {
  global $oUML, $login_people_id;

  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  $people = sql_people( $filters, AUTH );
  foreach( $people as $p ) {
    $people_id = $p['people_id'];
    $problems = priv_problems( 'people', 'delete', $p );
    if( "$people_id" === "$login_people_id" ) {
      $problems += new_problem( "eigener account nicht l{$oUML}schbar" );
    }
    if( ! $problems ) {
      $problems = sql_references( 'people', $people_id, "return=report,delete_action=$action" ); 
    }
    $rv = sql_handle_delete_action( 'people', $people_id, $action, $problems, $rv, 'log=1,authorized=1' );
  }
  return $rv;
}

function sql_save_person( $people_id, $values, $opts = array() ) {
  global $login_people_id;

  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $problems = array();
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    $problems = priv_problems( 'people', $people_id ? 'edit' : 'create', $people_id );
  }
  if( $people_id ) {
    logger( "start: update person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'people', array( 'person' => "people_id=$people_id" ) );
  } else {
    logger( "start: insert person", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'people' );
  }

  if( ! isset( $values['authentication_methods'] ) ) {
    if( isset( $values['authentication_method_simple'] ) && isset( $values['authentication_method_ssl'] ) ) {
      $values['authentication_methods'] = ',';
      if( $values['authentication_method_simple'] ) {
        $values['authentication_methods'] .= 'simple,';
      }
      if( $values['authentication_method_ssl'] ) {
        $values['authentication_methods'] .= 'ssl,';
      }
    }
  }
  unset( $values['authentication_method_simple'] );
  unset( $values['authentication_method_ssl'] );

  unset( $values['password_hashvalue'] );
  unset( $values['password_hashfunction'] );
  unset( $values['password_salt'] );

  if( isset( $values['sn'] ) && isset( $values['gn'] ) && ! isset( $values['cn'] ) ) {
    $values['cn'] = trim( $values['gn'] . ' ' . $values['sn'] );
  }

  if( ! have_priv( 'person', 'account', $people_id ) ) {
    unset( $values['uid'] );
    unset( $values['privs'] );
    unset( $values['privlist'] );
    unset( $values['authentication_methods'] );
  }

  $problems += validate_row( 'people', $values, "update=$people_id,action=soft,authorized=1" );
  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_person() [$people_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'people' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_person() [$people_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'people' );
  }

  if( $people_id ) {
    sql_update( 'people', $people_id, $values, AUTH );
    logger( "updated person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'people', array( 'person' => "people_id=$people_id" ) );
  } else {
    $people_id = sql_insert( 'people', $values, AUTH );
    logger( "new person [$people_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'people' );
  }
  return $people_id;
}


////////////////////////////////////
//
// kontoklassen-funktionen:
//
////////////////////////////////////

function sql_kontoklassen( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $authorized = adefault( $opts, 'authorized', 0 );
  if( ! $authorized ) {
    need_priv( 'books', 'read' );
  }

  $selects = sql_default_selects('kontoklassen');
  $selects['flag_vortragskonto'] = 'IF( kontoklassen.vortragskonto, 1, 0 )';
  $opts = default_query_options( 'kontoklassen', $opts, array( 'selects' => $selects ) );
  $opts['filters'] = sql_canonicalize_filters( 'kontoklassen', $filters );

  $opts['authorized'] = 1;
  return sql_query( 'kontoklassen', $opts );
}

function sql_one_kontoklasse( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $default = adefault( $opts, 'default', false );
  $authorized = adefault( $opts, 'authorized', 0 );
  return sql_kontoklassen( $filters, array( 'default' => $default, 'single_row' => true, 'authorized' => $authorized ) );
}


function sql_install_kontenrahmen( $version, $opts = array() ) {
  global $kontenrahmen_version; // from leitvariable
  global $kontenrahmen;

  need_priv( '*', '*' );
  need( isset( $kontenrahmen[ $version ] ) );

  logger( "updating table `kontoklassen`: install version $version", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'kontoklassen' );
  sql_delete_generic( 'kontoklassen', '1', 'action=soft,log=1,authorized=1' );
  foreach( $kontenrahmen[ $version ] as $kontoklasse ) {
    sql_insert( 'kontoklassen', $kontoklasse, 'authorized=1,update_cols=1' );
  }
  sql_update( 'leitvariable', 'name=kontenrahmen_version', "value=$version", AUTH );
  logger( "kontenrahmen $database_version has been written into table `kontoklassen`", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'kontoklassen' );
}

////////////////////////////////////
//
// hauptkonten-funktionen:
//
////////////////////////////////////

function sql_hauptkonten( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    need_priv( 'books', 'read' );
  }
  $joins = array( 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )' );
  $selects = sql_default_selects( array( 'hauptkonten', 'kontoklassen' => array( '.cn' => 'kontoklassen_cn', 'aprefix' => 'kontoklassen_' ) ) );
  $selects['hgb_klasse'] = "hauptkonten.hauptkonten_hgb_klasse";
  $selects['flag_vortragskonto'] = 'IF( kontoklassen.vortragskonto, 1, 0 )';
  $optional_selects['count_unterkonten'] = "( SELECT COUNT(*) FROM unterkonten WHERE unterkonten.hauptkonten_id = hauptkonten.hauptkonten_id )";

  $opts = default_query_options( 'hauptkonten', $opts, array(
    'joins' => $joins
  , 'selects' => $selects
  , 'orderby' => 'kontoklassen.seite, hauptkonten.rubrik, hauptkonten.titel, kontoklassen.geschaeftsbereich'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'hauptkonten', $filters, $opts['joins'], $selects );

  $opts['authorized'] = 1;
  return sql_query( 'hauptkonten', $opts );
}

function sql_one_hauptkonto( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $default = adefault( $opts, 'default', false );
  $authorized = adefault( $opts, 'authorized', 0 );
  return sql_hauptkonten( $filters, array( 'default' => $default, 'single_row' => true, 'authorized' => $authorized ) );
}


function sql_save_hauptkonto( $hauptkonten_id, $values, $opts = array() ) {
  global $uUML;

  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $problems = array();
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    $problems = priv_problems( 'hauptkonten', $hauptkonten_id ? 'edit' : 'create', $hauptkonten_id );
  }
  if( $hauptkonten_id ) {
    logger( "start: update hauptkonto [$hauptkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'hauptkonto', array( 'hauptkonto' => "hauptkonten_id=$hauptkonten_id" ) );
    $hk = sql_one_hauptkonto( $hauptkonten_id, AUTH );
    if( sql_posten( array( 'abgeschlossen', "hauptkonten_id=$hauptkonten_id" ), 'single_field=COUNT' ) ) {
      if( isset( $values['rubrik'] ) || isset( $values['titel'] ) || isset( $values['kontoklassen_id'] ) || isset( $values['hauptkonten_hgb_klasse'] ) ) {
        $problems += new_problem( "abgeschlossene Buchungen vorhanden - Stammdaten des Kontos sind schreibgesch{$uUML}tzt" );
      }
    }
  } else {
    logger( "start: insert hauptkonto", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'hauptkonto' );
  }
  $problems += validate_row( 'hauptkonten', $values, "update=$hauptkonten_id,action=soft,authorized=1" );

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_hauptkonto() [$hauptkonten_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'hauptkonten' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_hauptkonto() [$hauptkonten_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'hauptkonten' );
  }

  if( $hauptkonten_id ) {
    sql_update( 'hauptkonten', $hauptkonten_id, $values, AUTH );
   } else {
    $hauptkonten_id = sql_insert( 'hauptkonten', $values, AUTH );
    logger( "new hauptkonto [$hauptkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'hauptkonto', array( 'hauptkonto' => "hauptkonten_id=$hauptkonten_id" ) );
  }

  return $hauptkonten_id;
}

function sql_delete_hauptkonten( $filters, $opts = array() ) {
  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $hauptkonten = sql_hauptkonten( $filters, AUTH );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $hauptkonten as $hk ) {
    $hauptkonten_id = $hk['hauptkonten_id'];
    if( ! ( $problems = priv_problems( 'hauptkonten', 'delete', $hk ) ) ) {
      $problems = sql_references( 'hauptkonten', $hauptkonten_id, "return=report" );
    }
    $rv = sql_handle_delete_action( 'hauptkonten', $hauptkonten_id, $action, $problems, $rv, 'log=1,authorized=1' );
  }
  return $rv;
}



////////////////////////////////////
//
// unterkonten-funktionen:
//
////////////////////////////////////

function sql_unterkonten( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    need_priv( 'books', 'read' );
  }
  $joins = array(
    'hauptkonten' => 'hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )'
  , 'people' => 'LEFT people USING ( people_id )'
  , 'darlehen' => 'LEFT darlehen USING ( darlehen_id )'
  );
  $optional_joins = array(
    'posten' => 'LEFT posten USING ( unterkonten_id )'
  , 'buchungen' => 'LEFT buchungen USING ( buchungen_id )'
  );
  $selects = sql_default_selects( array(
    'unterkonten'
  , 'hauptkonten' => array( 'aprefix' => 'hauptkonten_' )
  , 'kontoklassen' => array( '.cn' => 'kontoklassen_cn', 'aprefix' => 'kontoklassen_' )
  , 'darlehen' => array( 'aprefix' => 'darlehen_' )
  ) );
  $selects['people_cn'] = 'people.cn';
  $selects['flag_vortragskonto'] = 'IF( kontoklassen.vortragskonto, 1, 0 )';
  // hauptkonten_hgb_klasse overrides unterkonten_hgb_klasse:
  $selects['hgb_klasse'] = "IF( hauptkonten_hgb_klasse = '', unterkonten_hgb_klasse, hauptkonten_hgb_klasse )";
  $optional_selects = array(
    'saldoS' => "IFNULL( SUM( posten.betrag * IF( posten.art = 'S', 1, 0 ) ), 0.0 )"
  , 'saldoH' => "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, 0 ) ), 0.0 )"
  , 'saldo' =>  "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) * IF( kontoklassen.seite = 'P', 1, -1 ) ) , 0.0 )"

  , 'saldoS_ausgefuehrt' => "IFNULL( SUM( posten.betrag * IF( posten.art = 'S', 1, 0 ) * IF( buchungen.flag_ausgefuehrt, 1, 0 ) ), 0.0 )"
  , 'saldoH_ausgefuehrt' => "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, 0 ) * IF( buchungen.flag_ausgefuehrt, 1, 0 ) ), 0.0 )"
  , 'saldo_ausgefuehrt' =>  "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) * IF ( buchungen.flag_ausgefuehrt, 1, 0 ) * IF( kontoklassen.seite = 'P', 1, -1 ) ) , 0.0 )"

  , 'saldoS_geplant' => "IFNULL( SUM( posten.betrag * IF( posten.art = 'S', 1, 0 ) * IF( buchungen.flag_ausgefuehrt, 0, 1 ) ), 0.0 )"
  , 'saldoH_geplant' => "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, 0 ) * IF( buchungen.flag_ausgefuehrt, 0, 1 ) ), 0.0 )"
  , 'saldo_geplant' =>  "IFNULL( SUM( posten.betrag * IF( posten.art = 'H', 1, -1 ) * IF( buchungen.flag_ausgefuehrt, 0, 1 ) * IF( kontoklassen.seite = 'P', 1, -1 ) ) , 0.0 )"
  );

  $opts = default_query_options( 'unterkonten', $opts, array(
    'joins' => $joins
  , 'optional_joins' => $optional_joins
  , 'selects' => $selects
  , 'optional_selects' => $optional_selects
  , 'orderby' => 'seite, rubrik, titel, unterkonten.unterkonten_id'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'unterkonten', $filters, $opts['joins'], $selects , array( 'stichtag' => array( '<=', 'buchungen.valuta' ) ) );

//       case 'hgb_klasse':
//         $key = "IF( hauptkonten_hgb_klasse != '', hauptkonten_hgb_klasse, unterkonten_hgb_klasse )";
//         $val = '^'.preg_replace( '/[.]/', '[.]', $val );  // sic!
//         $rel = '~=';
//         break;

  $opts['authorized'] = 1;
  return sql_query( 'unterkonten', $opts );
}

function sql_one_unterkonto( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $default = adefault( $opts, 'default', false );
  $authorized = adefault( $opts, 'authorized', 0 );
  return sql_unterkonten( $filters, array( 'default' => $default, 'single_row' => true, 'authorized' => $authorized ) );
}

function sql_unterkonten_saldo( $filters = array() ) {
  return sql_unterkonten( $filters, array(
    'group_by' => '*'
  , 'single_field' => 'saldo'
  , 'default' => '0.0'
  , 'more_joins' => 'posten, buchungen'
  , 'more_selects' => 'saldo'
  ) );
}

// function sql_unterkonten_saldo_geplant( $filters = array() ) {
//   return sql_unterkonten( $filters, array(
//     'group_by' => '*'
//   , 'single_field' => 'saldo_geplant'
//   , 'default' => '0.0'
//   , 'more_joins' => 'posten, buchungen'
//   , 'more_selects' => 'saldo_geplant'
//   ) );
//   return sql_unterkonten( $filters, 'group_by=*,single_field=saldo_geplant,default=0.0,more_joins=buchungen posten' );
// }
// 

function sql_save_unterkonto( $unterkonten_id, $values, $opts = array() ) {
  global $uUML, $aUML;

  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $problems = array();
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    $problems = priv_problems( 'unterkonten', $unterkonten_id ? 'edit' : 'create', $unterkonten_id );
  }
  if( $unterkonten_id ) {
    logger( "start: update unterkonto [$unterkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'unterkonto', array( 'unterkonto' => "unterkonten_id=$unterkonten_id" ) );
    unset( $values['hauptkonten_id'] );
    $uk = sql_one_unterkonto( $unterkonten_id, AUTH );
    if( sql_posten( array( 'abgeschlossen', "unterkonten_id=$unterkonten_id" ), 'single_field=COUNT' ) ) {
      foreach( $values as $key => $val ) {
        switch( $key ) {
          case 'kommentar':
          case 'attribute':
          case 'bank_url':
          case 'bank_cn':
          case 'flag_unterkonto_offen':
            continue;
          default:
            $problems += new_problem( "abgeschlossene Buchungen vorhanden - Stammdaten des Kontos sind schreibgesch{$uUML}tzt" );
        }
      }
    }
  } else {
    logger( "start: insert unterkonto", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'hauptkonto' );
    if( ! sql_one_hauptkonto( array( 'hauptkonten_id' => adefault( $values, 'hauptkonten_id', 0 ) ), 'default=0' ) ) {
      $problms += new_problem( "kein g{$uUML}ltiges Hauptkonto ausgew{$aUML}hlt" );
    }
  }
  $problems += validate_row( 'unterkonten', $values, "update=$unterkonten_id,action=soft,authorized=1" );

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_unterkonto() [$unterkonten_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'unterkonten' );
      }
    case 'soft':
      if( ! $problems ) {
        continue;
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_save_unterkonto() [$unterkonten_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'unterkonten' );
  }

  if( $unterkonten_id ) {
    sql_update( 'unterkonten', $unterkonten_id, $values, AUTH );
   } else {
    $unterkonten_id = sql_insert( 'unterkonten', $values, AUTH );
    logger( "new unterkonto [$unterkonten_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'unterkonto', array( 'unterkonto' => "unterkonten_id=$unterkonten_id" ) );
  }

  return $unterkonten_id;
}

function sql_delete_unterkonten( $filters, $opts = array() ) {
  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $unterkonten = sql_unterkonten( $filters, AUTH );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $unterkonten as $uk ) {
    $unterkonten_id = $uk['unterkonten_id'];
    if( ! ( $problems = priv_problems( 'unterkonten', 'delete', $uk ) ) ) {
      $problems = sql_references( 'unterkonten', $unterkonten_id, "return=report" );
    }
    $rv = sql_handle_delete_action( 'unterkonten', $unterkonten_id, $action, $problems, $rv, 'log=1,authorized=1' );
  }
  return $rv;
}


////////////////////////////////////
//
// buchungen-funktionen
//
////////////////////////////////////

function sql_buchungen( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    need_priv( 'books', 'read' );
  }

  $selects = sql_default_selects( 'buchungen' );
  $joins = array(
    'posten' => 'posten USING ( buchungen_id )'
  , 'unterkonten' => 'unterkonten USING ( unterkonten_id )'
  , 'hauptkonten' => 'hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )'
  );
  $opts = default_query_options( 'buchungen', $opts, array(
    'joins' => $joins
  , 'selects' => $selects
  , 'orderby' => 'geschaeftsjahr, valuta'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'buchungen', $filters, $opts['joins'], $selects );

  $opts['authorized'] = 1;
  return sql_query( 'buchungen', $opts );
}

function sql_one_buchung( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $default = adefault( $opts, 'default', false );
  $authorized = adefault( $opts, 'authorized', 0 );
  return sql_buchungen( $filters, array( 'single_row' => true, 'default' => $default, 'authorized' => $authorized ) );
}

// save, delete: use sql_buche()!



function sql_buche( $buchungen_id, $values = array(), $posten = array(), $opts = array() ) {
  global $aUML, $uUML, $oUML, $geschaeftsjahr_min, $geschaeftsjahr_max, $geschaeftsjahr_abgeschlossen, $geschaeftsjahr_thread, $valuta_letzte_buchung;

  $opts = parameters_explode( $opts );
  $vortragsbuchung = adefault( $opts, 'vortragsbuchung', 0 );
  need_priv('books','write' );
  logger( "sql_buche: [$buchungen_id]", LOG_LEVEL_DEBUG, $buchungen_id ? LOG_FLAG_UPDATE: LOG_FLAG_INSERT, 'buchungen' );

  $action = adefault( $opts, 'action', 'dryrun' );

  $problems = array();
  if( $buchungen_id ) {
    $buchung = sql_one_buchung( $buchungen_id, "default=0,authorized=1" );
    if( ! $buchung ) {
      $problems += new_problem( "sql_buche(): Buchung [$buchungen_id] nicht vorhanden" );
    } else {
      if( ! isset( $values['flag_ausgefuehrt'] ) ) {
        $values['flag_ausgefuehrt'] = $buchung['flag_ausgefuehrt'];
      } else {
        if( $buchung['flag_ausgefuehrt'] && ! $values['flag_ausgefuehrt'] ) {
          $problems += new_problem( "sql_buche(): Buchung bereits ausgef{$uUML}hrt, Status `geplant` ist nicht m{$oUML}glich" );
        }
      }
      if( ! isset( $values['valuta'] ) ) {
        $values['valuta'] = $buchung['valuta'];
      }
      if( ! isset( $values['geschaeftsjahr'] ) ) {
        $values['geschaeftsjahr'] = $buchung['geschaeftsjahr'];
      } else {
        if( "$geschaeftsjahr" !== "{$buchung['geschaeftsjahr']}" ) {
          $problems += new_problem( "sql_buche(): Gesch{$aUML}ftsjahr nicht {$aUML}nderbar" );
        }
      }
      if( ! isset( $values['vorfall'] ) ) {
        $values['vorfall'] = $buchung['vorfall'];
      }
    }
  }

  $geschaeftsjahr = adefault( $values, 'geschaeftsjahr', $geschaeftsjahr_thread );
  $flag_ausgefuehrt = adefault( $values, 'flag_ausgefuehrt', 1 );
  $valuta = adefault( $values, 'valuta', $valuta_letzte_buchung );
  $vorfall = adefault( $values, 'vorfall', '' );

  if( $vortragsbuchung ) {
    if( $valuta != 100 ) {
      $problems += new_problem( "sql_buche(): falsche valuta f{$uUML}r Vortragsbuchung" );
    }
  } else {
    if( ( $valuta < 101 ) || ( is_valuta_valid( $valuta, $geschaeftsjahr ) ) ) {
      $problems += new_problem( "sql_buche(): ung{$uUML}ltige valuta" );
    }
  }

  if( ( $geschaeftsjahr < $geschaeftsjahr_min ) || ( $flag_ausgefuehrt && ( $geschaeftsjahr > $geschaeftsjahr_max ) ) ) {
    $problems += new_problem( "sql_buche(): ung{$uUML}tiges Gesch{$aUML}ftsjahr" );
  } else if( $geschaeftsjahr <= $geschaeftsjahr_abgeschlossen ) {
    $problems += new_problem( "sql_buche(): Gesch{$aUML}ftsjahr ist abgeschlossen" );
  }

  $saldoH = 0;
  $saldoS = 0;
  $nS = $nH = 0;

  $posten_sanitized = array();
  foreach( $posten as $p ) {
    $uk_id = adefault( $p, 'unterkonten_id', 0 );
    $uk = sql_one_unterkonto( array( 'unterkonten_id' => $uk_id ), "default=0,authorized=1" );
    if( ! $uk ) {
      $problems += new_problem( "sql_buche(): Unterkonto [$uk_id] nicht vorhanden" );
      continue;
    }
    if( ! $uk['flag_unterkonto_offen'] ) {
      $problems += new_problem( "sql_buche(): Unterkonto [$uk_id] ist geschlossen" );
      continue;
    }
    if( $uk['vortragskonto'] ) {
      if( ( (int)$valuta !== 100 ) && ( (int)$valuta !== 1299 ) ) {
        $problems += new_problem( "sql_buche(): Unterkonto [$uk_id] ist Vortragskonto: nur Vortrag und Gewinnverwendung m{$oUML}glich" );
      }
    }
    $betrag = sprintf( '%.2lf', $p['betrag'] );
    if( $betrag < 0.005 ) { // bookkeeping is a 14ht century theory: no zero and no negative numbers!
      $problems += new_problem( "sql_buche(): ung{$uUML}ltiger Betrag" );
    }
    switch( $p['art'] ) {
      case 'H':
        $art = 'H';
        $nH++;
        $saldoH += $betrag;
        break;
      case 'S':
        $art = 'S';
        $nS++;
        $saldoS += $betrag;
        break;
      default:
        error( 'sql_buche(): undefinierter Posten', LOG_FLAG_CODE | LOG_FLAG_DATA, 'posten,buchungen' );
    }
    $posten_sanitized[] = array(
      'art' => $art
    , 'betrag' => $betrag
    , 'unterkonten_id' => $uk_id
    , 'beleg' => trim( adefault( $p, 'beleg', '' ) )
    );
  }

  if( $posten ) {
    if( $nS < 1 ) {
      $problems += new_problem( "sql_buche(): keine Soll-Posten vorhanden" );
    }
    if( $nH < 1 ) {
      $problems += new_problem( "sql_buche(): keine Haben-Posten vorhanden" );
    }
    if( abs( $saldoH - $saldoS ) >= 0.005 ) {
      $problems += new_problem( "sql_buche(): Erhaltungssatz verletzt" );
    }
  }

  $values_buchungen = array(
    'geschaeftsjahr' => $geschaeftsjahr
  , 'valuta' => $valuta
  , 'vorfall' => $vorfall
  , 'flag_ausgefuehrt' => $flag_ausgefuehrt
  );

  switch( $action ) {
    case 'hard':
      if( $problems ) {
        error( "sql_save_unterkonto() [$unterkonten_id]: ".reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_INPUT, 'unterkonten' );
      }
    case 'dryrun':
      return $problems;
    default:
      error( "sql_buche() [$buchung_id]: unsupported action requested: [$action]", LOG_FLAG_CODE, 'unterkonten' );
  }

  if( $buchungen_id ) {
    if( $posten ) {
      sql_update( 'buchungen', $buchungen_id, $values_buchungen, AUTH );
    } else {
      sql_delete( 'buchungen', $buchungen_id, AUTH );
    }
    sql_delete( 'posten', array( 'buchungen_id' => $buchungen_id ), AUTH );
    logger( "sql_buche: update: [$buchungen_id]", LOG_LEVEL_DEBUG, LOG_FLAG_UPDATE, 'buchung_update' );
  } else {
    if( $posten ) {
      $buchungen_id = sql_insert( 'buchungen', $values_buchungen, AUTH );
      logger( "sql_buche: inserted: [$buchungen_id]", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'buchung_neu' );
    } else {
      return 0; // was a nop
    }
  }
  foreach( $posten_sanitized as $v ) {
    $v['buchungen_id'] = $buchungen_id;
    sql_insert( 'posten', $v, AUTH );
  }
  if( ! $vortragsbuchung ) {
    if( $geschaeftsjahr < $geschaeftsjahr_max ) {
      sql_saldenvortrag_buchen( $geschaeftsjahr );
    }
    sql_update( 'leitvariable', array( 'name' => 'valuta_letzte_buchung' ), array( 'value' => $valuta ) );

  }
  return $buchungen_id;
}


function sql_saldenvortrag_buchen( $von_jahr ) {
  global $aUML, $uUML, $geschaeftsjahr_max, $geschaeftsjahr_abgeschlossen;

  need_priv( 'books', 'write' );
  logger( "sql_saldenvortrag_buchen: $von_jahr", LOG_LEVEL_NOTICE, LOG_FLAG_INSERT, 'vortrag' );

  $vortrag_geplant = array();
  $vortrag_ausgefuehrt = array();
  $posten_geplant = array();
  $posten_ausgefuehrt = array();

  $nach_jahr = $von_jahr + 1;
  need( $von_jahr < $geschaeftsjahr_max );
  need( $von_jahr >= $geschaeftsjahr_min );
  need( $nach_jahr > $geschaeftsjahr_abgeschlossen );

  $unterkonten = sql_unterkonten( true
  , array(
      'more_joins' => 'posten, buchungen'
    , 'more_selects' => 'saldo, saldo_geplant'
    , 'authorized' => 1
    )
  );
  foreach( $unterkonten as $uk ) {
    $unterkonten_id = $uk['unterkonten_id'];

    $saldo_ausgefuehrt = $uk['saldo'];
    $saldo_geplant = $uk['saldo_geplant'];

    if( $abs( $saldo_ausgefuehrt ) > 0.005 ) {
      if( $uk['kontenkreis'] === 'B' ) {
        $posten_ausgefuehrt[] = array(
          'beleg' => "Vortrag aus $jahr_von am " . $GLOBALS['today_mysql']
        , 'art' => ( ( ( $saldo_ausgefuehrt > 0 ) Xor ( $uk['seite'] === 'A' ) ) ? 'H' : 'S' )
        , 'betrag' => abs( $saldo_ausgefuehrt )
        , 'unterkonten_id' => $unterkonten_id
        );
      } else {
        $gb = $uk['geschaeftsbereich'];
        $vortrag_ausgefuehrt[ $gb ] = adefault( $vortrag, $gb, 0.0 ) + ( ( $uk['seite'] === 'P' ) ? $saldo_ausgefuehrt : - $saldo_ausgefuehrt );
      }
    }
    if( $abs( $saldo_geplant ) > 0.005 ) {
      if( $uk['kontenkreis'] === 'B' ) {
        $posten_geplant[] = array(
          'beleg' => "Vortrag (UNGEBUCHT) aus $jahr_von am " . $GLOBALS['today_mysql']
        , 'art' => ( ( ( $saldo_geplant > 0 ) Xor ( $uk['seite'] === 'A' ) ) ? 'H' : 'S' )
        , 'betrag' => abs( $saldo_geplant )
        , 'unterkonten_id' => $unterkonten_id
        );
      } else {
        $gb = $uk['geschaeftsbereich'];
        $vortrag_geplant[ $gb ] = adefault( $vortrag, $gb, 0.0 ) + ( ( $uk['seite'] === 'P' ) ? $saldo_geplant : - $saldo_geplant );
      }
    }
  }

  foreach( $vortrag_ausgefuehrt as $gb => $saldo ) {
    if( abs( $saldo ) < 0.005 ) {
      continue;
    }
    $vortragshauptkonten = sql_hauptkonten( array( 'vortragskonto' => $gb, 'flag_hauptkonto_offen' ) );
    need( count( $vortragshauptkonten ) === 1, "sql_saldenvortrag_buchen(): kein eindeutiges Hauptkonto angelegt f{$uUML}r Vortrag im Gesch{$aUML}ftsbereich $gb" );
    $vortrags_hk_id = $vortragshauptkonten[ 0 ]['hauptkonten_id'];

    $vortragsunterkonten = sql_unterkonten( array( 'hauptkonten_id' => $vortrags_hk_id, 'flag_unterkonto_offen' ) );
    if( $vortragsunterkonten ) {
      $vortrags_uk_id = $vortragsunterkonten[ 0 ]['unterkonten_id'];
    } else {
      $vortrags_uk_id = sql_insert( 'unterkonten', array(
        'cn' => "Vortrag ab Jahr $jahr_von"
      , 'hauptkonten_id' => $vortrags_hk_id
      ) );
      logger( "sql_saldenvortrag_buchen: unterkonto $vortrags_uk_id fuer vortrag $gb aus $jahr angelegt", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'vortrag' );
    }
    $posten_ausgefuehrt[] = array(
      'beleg' => "Jahresergebnis $jahr_von"
    , 'art' => ( $saldo >= 0 ? 'H' : 'S' )
    , 'betrag' => abs( $saldo )
    , 'unterkonten_id' => $vortrags_uk_id
    );
  }
  foreach( $vortrag_geplant as $gb => $saldo ) {
    if( abs( $saldo ) < 0.005 ) {
      continue;
    }
    $vortragshauptkonten = sql_hauptkonten( array( 'vortragskonto' => $gb, 'flag_hauptkonto_offen' ) );
    need( count( $vortragshauptkonten ) === 1, "sql_saldenvortrag_buchen(): kein eindeutiges Hauptkonto angelegt f{$uUML}r Vortrag im Gesch{$aUML}ftsbereich $gb" );
    $vortrags_hk_id = $vortragshauptkonten[ 0 ]['hauptkonten_id'];

    $vortragsunterkonten = sql_unterkonten( array( 'hauptkonten_id' => $vortrags_hk_id, 'flag_unterkonto_offen' ) );
    if( $vortragsunterkonten ) {
      $vortrags_uk_id = $vortragsunterkonten[ 0 ]['unterkonten_id'];
    } else {
      $vortrags_uk_id = sql_insert( 'unterkonten', array(
        'cn' => "Vortrag ab Jahr $jahr_von"
      , 'hauptkonten_id' => $vortrags_hk_id
      ) );
      logger( "sql_saldenvortrag_buchen: unterkonto $vortrags_uk_id fuer vortrag $gb aus $jahr angelegt", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'vortrag' );
    }
    $posten_geplant[] = array(
      'beleg' => "Jahresergebnis (UNGEBUCHT) $jahr_von"
    , 'art' => ( $saldo >= 0 ? 'H' : 'S' )
    , 'betrag' => abs( $saldo )
    , 'unterkonten_id' => $vortrags_uk_id
    );
  }

  $buchungen_id = sql_buchungen( "geschaeftsjahr=$geschaeftsjahr_nach,valuta=100,flag_ausgefuehrt=1", 'authorized=1,single_field=buchungen_id,default=0' );
  sql_buche(
    $buchungen_id
  , array(
      'valuta' => 100
    , 'vorfall' => "Vortrag aus $jahr_von"
    , 'flag_ausgefuehrt' => 1
    )
  , $posten_ausgefuehrt
  , 'action=hard,vortragsbuchung=1'
  );
  $buchungen_id = sql_buchungen( "geschaeftsjahr=$geschaeftsjahr_nach,valuta=100,flag_ausgefuehrt=0", 'authorized=1,single_field=buchungen_id,default=0' );
  logger( "sql_saldenvortrag_buchen: ".count( $posten_ausgefuehrt )." gebuchte Posten von $jahr_von nach $jahr_nach vorgetragen", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'vortrag' );
  sql_buche(
    $buchungen_id
  , array(
      'valuta' => 100
    , 'vorfall' => "Vortrag (UNGEBUCHT) aus $jahr_von"
    , 'flag_ausgefuehrt' => 0
    )
  , $posten_geplant
  , 'action=hard,vortragsbuchung=1'
  );
  logger( "sql_saldenvortrag_buchen: ".count( $posten_geplant )." geplante Posten von $jahr_von nach $jahr_nach vorgetragen", LOG_LEVEL_DEBUG, LOG_FLAG_INSERT, 'vortrag' );

  if( $jahr_nach < $geschaeftsjahr_max ) {
    sql_saldenvortrag_buchen( $jahr_nach );
  }
}



////////////////////////////////////
//
// posten-funktionen
//
////////////////////////////////////

function sql_posten( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    need_priv( 'books', 'read' );
  }

  $joins = array(
    'buchungen' => 'buchungen USING ( buchungen_id )'
  , 'unterkonten' => 'unterkonten USING ( unterkonten_id )'
  , 'hauptkonten' => 'hauptkonten USING ( hauptkonten_id )'
  , 'kontoklassen' => 'kontoklassen USING ( kontoklassen_id )'
  , 'people' => 'people USING ( people_id )'
  , 'things' => 'things USING ( things_id )'
  );

  $selects = sql_default_selects( array(
    'posten'
  , 'unterkonten' => array( 'aprefix' => 'unterkonten_' )
  , 'hauptkonten' => array( 'aprefix' => 'hauptkonten_' )
  , 'kontoklassen' => array( '.cn' => 'kontoklassen_cn', 'aprefix' => 'kontoklassen_' )
  , 'buchungen' => array( 'aprefix' => 'buchungen_' )
  ) );
  $selects['people_cn'] = 'people.cn';
  $selects['flag_vortragskonto'] = 'IF( kontoklassen.vortragskonto, 1, 0 )';
  // $selects['is_vortrag'] = "IF( buchungen.valuta <= '100', 1, 0 )";
  // $selects['saldo'] = "IFNULL( SUM( betrag ), 0.0 )";

  $opts = default_query_options( 'posten', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'buchungen.geschaeftsjahr, buchungen.valuta, buchungen.buchungen_id, art DESC'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'posten', $filters, $opts['joins'], $selects );

  $opts['authorized'] = 1;
  return sql_query( 'posten', $opts );
}
 
function sql_one_posten( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $default = adefault( $opts, 'default', false );
  $authorized = adefault( $opts, 'authorized', 0 );
  return sql_posten( $filters, array( 'default' => $default, 'single_row' => true, 'authorized' => $authorized ) );
}

function sql_posten_saldo( $filters = array() ) {
  return sql_posten( $filters, 'single_field=saldo,groupby=*' );
}

// save, delete: use sql_buche()!

////////////////////////////////////
//
// darlehen-funktionen:
//
////////////////////////////////////

function sql_darlehen( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( ! ( $authorized = adefault( $opts, 'authorized', 0 ) ) ) {
    need_priv( 'books', 'read' );
  }

  $joins = array(
    'darlehenkonto' => 'LEFT unterkonten ON darlehenkonto.unterkonten_id = darlehen.darlehen_unterkonten_id '
  , 'hauptkonten' => 'LEFT hauptkonten ON hauptkonten.hauptkonten_id = darlehenkonto.hauptkonten_id'
  , 'kontoklassen' => 'LEFT kontoklassen  ON hauptkonten.kontoklassen_id = kontoklassen.kontoklassen_id'
  , 'zinskonto' => 'LEFT unterkonten ON zinskonto.unterkonten_id = darlehen.zins_unterkonten_id'
  , 'people' => 'LEFT people ON people.people_id = darlehenkonto.people_id'
  );

  $selects = sql_default_selects( array(
    'darlehen'
  , 'people' => array( 'aprefix' => 'people_' )
  , 'hauptkonten' => array( 'aprefix' => 'hauptkonten_' )
  , 'darlehenkonto' => array( 'prefix' => 'darlehenkonto_', 'table' => 'unterkonten' )
  , 'zinskonto' => array( 'prefix' => 'zinskonto_', 'table' => 'unterkonten' )
  , 'kontoklassen' => array( 'aprefix' => 'kontoklassen_' )
  ) );
  $selects['zahlungsplan_count'] = "( SELECT COUNT(*) FROM zahlungsplan WHERE ( zahlungsplan.darlehen_id = darlehen.darlehen_id ) )";

  $opts = default_query_options( 'darlehen', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'geschaeftsjahr,people_cn'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'darlehen,hauptkonten,people', $filters, $opts['joins'] );

  $opts['authorized'] = 1;
  return sql_query( 'darlehen', $opts );
}

function sql_one_darlehen( $filters = array(), $opts = array() ) {
  $opts = parameters_explode( $opts );
  $default = adefault( $opts, 'default', false );
  $authorized = adefault( $opts, 'authorized', 0 );
  return sql_darlehen( $filters, array( 'default' => $default, 'single_row' => true, 'authorized' => $authorized ) );
}

function sql_delete_darlehen( $filters, $opts = array() ) {
  need_priv('books','read');
  $opts = parameters_explode( $opts );
  $action = adefault( $opts, 'action', 'dryrun' );
  $darlehen = sql_darlehen( $filters, AUTH );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $darlehen as $d ) {
    $id = $d['darlehen_id'];
    if( ! ( $problems = priv_problems( 'darlehen', 'delete', $d ) ) ) {
      $problems = sql_references( 'darlehen', $id, "return=report" );
    }
    $rv = sql_handle_delete_action( 'unterkonten', $unterkonten_id, $action, $problems, $rv, 'log=1,authorized=1' );
  }
  return $rv;
}




// sql_zahlungsplan_berechnen: bisher: nur annuitaetendarlehen: theorie:
// (!! nur korrekt ohne vorzeitige zinsausschuettung, also fuer i == s !!)
//   s : jahr zinslauf start
//   i : jahr zinsauszahlung start (i >= s)
//   t : jahr tilgung start (t >= s)
//   e : jahr tilgung ende (e >= t)
//   k_j: schuld am anfang jahr j
//   f := t - s  anzahl tilgungsfreie jahre (f >= 0)
//   n := e - t + 1  anzahl raten (n >= 1)
//   z : zinssatz
//   a : annuitaet
// es gilt:
//   k_t = k_s * (1+z)^f
//   k_{t+1} = k_t * (1+z) - a
//   ...
//   k_{t+n} = k_t * (1+z)^n - a * \sum_{l=0}^{n-1} (1+z)^l
//           = k_s * (1+z)^(f+n) - a * ((1+z)^n - 1) / z
// mit der bedingung k_{t+n} == k_{e+1} = 0 folgt:
//   a = k_s * z * (1+z)^{f+n} / ( (1+z)^n - 1 )
//
function sql_zahlungsplan_berechnen( $darlehen_id, $opts = array() ) {
  menatwork();
  logger( "sql_zahlungsplan_berechnen: [$darlehen_id]", LOG_FLAG_INFO, LOG_LEVEL_INSERT, 'zahlungsplan' );

  $opts = parameters_explode( $opts );
  $darlehen = sql_one_darlehen( $darlehen_id );

  $darlehen_unterkonten_id = $darlehen['darlehen_unterkonten_id'];
  if( ! ( $zins_unterkonten_id = $darlehen['zins_unterkonten_id'] ) )
    $zins_unterkonten_id = $darlehen_unterkonten_id;

  // berechnen:

  $jahr_start = max( adefault( $opts, 'jahr_start', 0 ), $darlehen['geschaeftsjahr_darlehen'] );

  $s = max( $jahr_start, $darlehen['geschaeftsjahr_zinslauf_start'] );
  $i = max( $s, $darlehen['geschaeftsjahr_zinsauszahlung_start'] );
  $t = max( $s, $darlehen['geschaeftsjahr_tilgung_start'] );
  $e = $darlehen['geschaeftsjahr_tilgung_ende'];
  $z = $darlehen['zins_prozent'] / 100.0;

  $f = $t - $s;
  $n = $e - $t + 1;

  need( $f >= 0 );
  need( $n > 0 );

  need( ( $darlehen_unterkonten_id = sql_get_folge_unterkonten_id( $darlehen_unterkonten_id, $jahr_start ) ) );
  need( ( $zins_unterkonten_id = sql_get_folge_unterkonten_id( $zins_unterkonten_id, $jahr_start ) ) );

  $zahlungsplan = array();
  if( $jahr_start == $darlehen['geschaeftsjahr_darlehen'] ) {
    $stand_darlehen = $darlehen['betrag_abgerufen'];
    $stand_zins = 0.0;
    $zahlungsplan[] = array(
      'darlehen_id' => $darlehen_id
    , 'geschaeftsjahr' => $darlehen['geschaeftsjahr_darlehen']
    , 'kommentar' => "Einzug Darlehensbetrag"
    , 'betrag' => $stand_darlehen
    , 'unterkonten_id' => $darlehen_unterkonten_id
    , 'art' => 'H'
    , 'zins' => 0
    , 'valuta' => $darlehen['valuta_betrag_abgerufen']
    );
  } else {
    $stand_darlehen = sql_unterkonten_saldo( "$darlehen_unterkonten_id,valuta=0100" );
    if( $zins_unterkonten_id != $darlehen_unterkonten_id ) {
      $stand_zins = sql_unterkonten_saldo( "$zins_unterkonten_id,valuta=0100" );
    } else {
      $stand_zins = 0.0;
    }
  }

  // $a berechnen - nur korrekt ohne vorzeitige ausschuettung, daher unten in der schleife nochmal!
  //   $k_s = $stand_darlehen + $stand_zins;
  //   if( $z >= 0.0001 ) {
  //     $a = r2( $k_s * pow( 1.0 + $z, $f + $n ) * $z / ( pow( 1.0 + $z, $n ) - 1.0 ) );
  //   } else {
  //     $a = r2( $k_s / $n );
  //   }

  
  if( adefault( $opts, 'delete' ) ) {
    sql_delete_zahlungsplan( "darlehen_id=$darlehen_id,geschaeftsjahr>=$jahr_start" );
  } else {
    need( ! sql_zahlungsplan( "darlehen_id=$darlehen_id,geschaeftsjahr>=$jahr_start" ), 'bereits zahlungsplan vorhanden' );
  }

  for( $j = $s; $j <= $e; $j++ ) {
    debug( $j, 'j' );
    debug( $stand_darlehen, 'stand_darlehen' );
    debug( $stand_zins, 'stand_zins' );
  //   debug( $z, 'z' );
  //   debug( $f, 'f' );
  //   debug( $n, 'n' );
  //   debug( $a, 'a' );
    // need( ( $darlehen_unterkonten_id = sql_get_folge_unterkonten_id( $darlehen_unterkonten_id, $j ) ) );
    // need( ( $zins_unterkonten_id = sql_get_folge_unterkonten_id( $zins_unterkonten_id, $j ) ) );

    $zins_neu = 0.0;
    if( ( $j >= $s ) && ( $z >= 0.0001 ) ) {
      $zins_neu = r2 ( 0.001 + ( $stand_zins + $stand_darlehen ) * $z );
      $zahlungsplan[] = array(
        'darlehen_id' => $darlehen_id
      , 'geschaeftsjahr' => $j
      , 'kommentar' => "Zinsgutschrift Jahr $j"
      , 'betrag' => $zins_neu
      , 'unterkonten_id' => $zins_unterkonten_id   // aus darlehen - folgekonto braucht noch nicht zu existieren!
      , 'art' => 'H'
      , 'zins' => 1
      , 'valuta' => '1231'
      );
    }

    if( $j >= $darlehen['geschaeftsjahr_tilgung_start'] ) {

      if( $j == $e ) {
        $tilgung = $stand_darlehen + $stand_zins + $zins_neu;
      } else {
        $k_s = $stand_darlehen + $stand_zins;
        $n = $e - $j + 1;
        if( $j == $t ) {
          if( $z >= 0.0001 ) {
            $a = r2( $k_s * pow( 1.0 + $z, $n ) * $z / ( pow( 1.0 + $z, $n ) - 1.0 ) );
          } else {
            $a = r2( $k_s / $n );
          }
        }
        $tilgung = $a;
      }
      $tilgung_alt = $tilgung - $zins_neu;
      need( $tilgung_alt > 0, 'ueberschuldet!' );
      $tilgung_zins_alt = r2( $stand_zins / ( $stand_darlehen + $stand_zins ) * $tilgung_alt );

      $tilgung_darlehen = $tilgung_alt - $tilgung_zins_alt;
      $tilgung_zins = $tilgung_zins_alt + $zins_neu;

      if( $tilgung_darlehen > 0.005 ) {
        $zahlungsplan[] = array(
          'darlehen_id' => $darlehen_id
        , 'geschaeftsjahr' => $j
        , 'kommentar' => "Tilgung Darlehen Jahr $j"
        , 'betrag' => $tilgung_darlehen
        , 'unterkonten_id' => $darlehen_unterkonten_id
        , 'art' => 'S'
        , 'zins' => 0
        , 'valuta' => '1231'
        );
      }
      if( $tilgung_zins > 0.005 ) {
        $zahlungsplan[] = array(
          'darlehen_id' => $darlehen_id
        , 'geschaeftsjahr' => $j
        , 'kommentar' => "Auszahlung Zins Jahr $j"
        , 'betrag' => $tilgung_zins
        , 'unterkonten_id' => $zins_unterkonten_id
        , 'art' => 'S'
        , 'zins' => 1
        , 'valuta' => '1231'
        );
      }
      $stand_darlehen -= $tilgung_darlehen;
      $stand_zins = $stand_zins + $zins_neu - $tilgung_zins;

    } else if( $j >= $darlehen['geschaeftsjahr_zinsauszahlung_start'] ) {

      $tilgung_zins = $zins_neu;
      if( $tilgung_zins > 0.005 ) {
        $zahlungsplan[] = array(
          'darlehen_id' => $darlehen_id
        , 'geschaeftsjahr' => $j
        , 'kommentar' => "Auszahlung Zins Jahr $j"
        , 'betrag' => $tilgung_zins
        , 'unterkonten_id' => $zins_unterkonten_id
        , 'art' => 'S'
        , 'zins' => 1
        , 'valuta' => '1231'
        );
      }

    } else {
      $stand_zins += $zins_neu;

    }

  }

  foreach( $zahlungsplan as $zp ) {
    sql_insert( 'zahlungsplan', $zp );
  }

}


?>
