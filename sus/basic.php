<?php

define( 'PERSON_PRIV_READ', 0x01 );
define( 'PERSON_PRIV_WRITE', 0x02 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

function kontenkreis_name( $kontenkreis) {
  switch( $kontenkreis ) {
    case 'E':
      return 'Erfolgskonto';
    case 'B':
      return 'Bestandskonto';
    case '':
    case '0':
      return '(kein Kontenkreis gew'.H_AMP.'uml;hlt)';
    default:
      error( 'undefinierter Kontenkreis', LOG_FLAG_DATA, 'kontenkreis' );
  }
}

function seite_name( $seite, $plural = false ) {
  $plural = ( $plural ? 'a' : '' );
  switch( $seite ) {
    case 'A':
      return 'Aktiv' . $plural;
    case 'P':
      return 'Passiv' . $plural;
    case '':
    case '0':
      return '(keine Seite gew'.H_AMP.'uml;hlt)';
    default:
      error( 'undefinierte Seite', LOG_FLAG_DATA, 'seite' );
  }
}

function r2( $x ) {
  return (double) sprintf( '%.2lf', $x );
}


function have_minimum_person_priv( $priv, $people_id = 0 ) {
  if( $people_id ) {
    $person = sql_person( $people_id );
    $p = $person['privs'];
  } else {
    $p = $GLOBALS['login_privs'];
  }
  return ( $p >= $priv );
}

function restrict_view_filters( $filters, $section ) {
  global $login_privs, $login_people_id, $logged_in, $login_groups_ids;

  // just a stub for the time being:
  //
  return $filters;
}


// have_priv(), need_priv(), restrict_view_filters():
// subproject sus is single-user, for the time being:
//
function have_priv( $section, $action, $item = 0 ) {
  global $login_privs, $login_privlist, $login_people_id, $logged_in, $login_groups_ids, $boards;
  global $teaching_survey_open, $teaching_survey_year, $teaching_survey_term;

  need( $section );
  need( $action );
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    return true;
  }
  if( ! $logged_in ) {
    return false;
  }
  if( $action === '*' ) {
    return false;
  }
  if( $section === '*' ) {
    return false;
  }
  if( $action === 'create' ) {
    $item = parameters_explode( $item );
  }
  switch( $section ) {
    case 'kontenrahmen':
      switch( $action ) {
        case 'read':
        case 'list':
          if( have_minimum_person_priv( PERSON_PRIV_WRITE ) ) {
            return true;
          }
          return false;
        default:
          return false;
      }
    case 'books':
    case 'person':
    case 'people':
    case 'things':
    case 'hauptkonten':
    case 'buchungen':
      switch( $action ) {
        case 'create':
        case 'delete':
        case 'edit':
        case 'write':
          if( have_minimum_person_priv( PERSON_PRIV_WRITE ) ) {
            return true;
          }
          return false;
        case 'read':
        case 'list':
          if( have_minimum_person_priv( PERSON_PRIV_READ ) ) {
            return true;
          }
          if( $item && isnumber( $item ) ) {
            $buchung = sql_one_buchung(
              array( 'buchungen_id' => $item, 'valuta >=' => 101, 'flag_personenkonto' => 1, 'people_id' => $login_people_id, )
            , 'authorized=1,default=0'
            );
            if( $buchung ) {
              return true;
            }
          }
          return false;
        default:
          return false;
      }
    case 'unterkonten':
      switch( $action ) {
        case 'create':
        case 'delete':
        case 'edit':
        case 'write':
          if( have_minimum_person_priv( PERSON_PRIV_WRITE ) ) {
            return true;
          }
          return false;
        case 'read':
        case 'list':
          if( have_minimum_person_priv( PERSON_PRIV_READ ) ) {
            return true;
          }
          if( $item ) {
            $uk = ( is_array( $item ) ? $item : sql_one_unterkonto( $item, 'authorized=1' ) );
            if( $uk['flag_personenkonto'] && ( $uk['people_id'] === $login_people_id ) ) {
              return true;
            }
          }
          return false;
        default:
          return false;
      }
  }
  switch( "$section,$action" ) {
    case 'person,account':
      return false;
    case 'person,password':
      if( $item ) {
        $person = ( is_array( $item ) ? $item : sql_person( $item, 'authorized=1' ) );
        if( $person['people_id'] === $login_people_id ) {
          return true;
        }
      }
      return false;
    default:
      error( "undefined priv query: [$section,$action]", LOG_FLAG_CODE, 'privs' );
  }

  return false;
}


function need_priv( $section, $action, $item = 0 ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung'), LOG_FLAG_AUTH | LOG_FLAG_USER, 'privs' );
  }
}

function init_session( $login_sessions_id ) {
  global $show_debug_button;
  if( have_priv( '*', '*' ) ) {
    $show_debug_button = true;
  }
  return true;
}

function is_valid_valuta( $valuta, $geschaeftsjahr ) {
  $valuta = (int)$valuta;
  if( ( $valuta == 100 ) || ( $valuta == 1299 ) ) {
    return true;
  }
  return checkdate( (int)( $valuta / 100 ), $valuta % 100, $geschaeftsjahr );
}

function get_month_ultimo( $month, $year ) {
  $month = (int)$month;
  need( ( $month >= 1 ) && ( $month <= 12 ) );
  for( $day = 29; checkdate( $month, $day, $year ); $day++ )
    ;
  return $day - 1;
}

function days_in_year( $year ) {
  return checkdate( 2, 29, $year ) ? 366 : 365;
}

function julian_date( $valuta, $year ) {
  if( $valuta <= 100 ) {
    return 0;
  }
  if( $valuta >= 1231 ) {
    return days_in_year( $year );
  }
  $unix = mktime( 0, 0, 0, $valuta / 100, $valuta % 100, $year, 0 );
  return (int)date( 'z', $unix ) + 1;
}



?>
