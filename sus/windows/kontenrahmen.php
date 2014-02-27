<?php

sql_transaction_boundary('*','*');



$kontenrahmen = array(

  'verein' => array(
    array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'flag_bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'flag_sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )
  
  , array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '121', 'cn' => 'Vortrag ideller Bereich', 'vortragskonto' => 'ideeller Bereich', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '122', 'cn' => 'Vortrag Vermoegensverwaltung', 'vortragskonto' => 'Vermoegensverwaltung', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '123', 'cn' => 'Vortrag Zweckbetrieb', 'vortragskonto' => 'Zweckbetrieb', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '124', 'cn' => 'Vortrag wirtschaftlicher Geschaeftsbetrieb', 'vortragskonto' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontenkreis' => 'B', 'seite' => 'P' )
  
  , array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'ideeller Bereich', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'ideeller Bereich', 'kontenkreis' => 'E', 'seite' => 'A' )
  
  , array( 'kontoklassen_id' => '300', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'Vermoegensverwaltung', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '310', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'Vermoegensverwaltung', 'kontenkreis' => 'E', 'seite' => 'A' )
  
  , array( 'kontoklassen_id' => '400', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'Zweckbetrieb', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '410', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'Zweckbetrieb', 'kontenkreis' => 'E', 'seite' => 'A' )
  
  , array( 'kontoklassen_id' => '500', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '510', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => 'wirtschaftlicher Geschaeftsbetrieb', 'kontenkreis' => 'E', 'seite' => 'A' )
  )

, 'privat' => array(
    array( 'kontoklassen_id' => '10', 'cn' => 'Bankkonto', 'flag_bankkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '30', 'cn' => 'Debitor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '40', 'cn' => 'Sachkonto', 'flag_sachkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'A' )
  , array( 'kontoklassen_id' => '50', 'cn' => 'sonstige Aktiva', 'kontenkreis' => 'B', 'seite' => 'A' )
  
  , array( 'kontoklassen_id' => '100', 'cn' => 'Kreditor', 'flag_personenkonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '110', 'cn' => 'sonstige Passiva', 'kontenkreis' => 'B', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '120', 'cn' => 'Vortrag', 'vortragskonto' => 1, 'kontenkreis' => 'B', 'seite' => 'P' )
  
  , array( 'kontoklassen_id' => '200', 'cn' => 'Ertragskonto', 'geschaeftsbereich' => '', 'kontenkreis' => 'E', 'seite' => 'P' )
  , array( 'kontoklassen_id' => '210', 'cn' => 'Aufwandskonto', 'geschaeftsbereich' => '', 'kontenkreis' => 'E', 'seite' => 'A' )
  )

);

$choices_kontenrahmen = array();
foreach( $kontenrahmen as $key => $k ) {
  $choices_kontenrahmen[ $key ] = $key;
}

$f = init_var( 'version_neu', "global,type=w64,sources=http persistent,set_scopes=self,default=$kontenrahmen_version" );

handle_actions( array( 'installKontenrahmen' ) );
switch( $action ) {
  case 'installKontenrahmen':
    need( $version_neu, 'kein kontenrahmen gewaehlt' );
    need( isset( $kontenrahmen[ $version_neu ] ), 'kein gueltiger kontenrahmen gewaehlt' );
    sql_install_kontenrahmen( $version_neu );
    break;
}

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Optionen' );
    open_tr();
      open_th( 'right', 'Kontenrahmen:' );
      open_td();
        echo select_element( $f, array( 'choices' => $choices_kontenrahmen ) );
  close_table();
close_div();


$list_options = handle_list_options( true, 'profile', array(
  'nr' => 't'
, 'key' => 't'
, 'utc' => 't,s'
, 'script' => 't,s'
, 'sql' => 't,s'
, 'stack' => 't'
, 'rows_returned' => 't,s'
, 'wallclock_seconds' => 't,s'
) );

$filters = $fields['_filters'];
if( ! $fields['fscript']['value'] ) {
  $filters['sql'] = '';
}

$rows = sql_query( 'profile', array( 'filters' => $filters, 'orderby' => $list_options['orderby_sql'] ) );
if( ! $rows ) {
  open_div( '', 'no matching entries' );
  return;
}
$count = count( $rows );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;

open_list( $list_options );
  open_list_row('header');
    open_list_cell( 'nr' );
    open_list_cell( 'id' );
    open_list_cell( 'script' );
    open_list_cell( 'utc' );
    open_list_cell( 'wallclock_seconds', 'secs' );
    open_list_cell( 'rows_returned', 'rows' );
    open_list_cell( 'sql' );
    open_list_cell( 'stack' );
  foreach( $rows as $r ) {
    if( $r['nr'] < $limits['limit_from'] )
      continue;
    if( $r['nr'] > $limits['limit_to'] )
      break;
    $s = $r['wallclock_seconds'];
    if( $s >= 1 ) {
      $class = 'redd;bold';
    } else if( $s >= 0.1 ) {
      $class = 'dgreen;bold';
    } else {
      $class = '';
    }
    open_list_row( "class=td:$class" );
      $id = $r['profile_id'];
      open_list_cell( 'nr', inlink( 'profileentry', "profile_id=$id,text={$r['nr']}" ), 'class=number' );
      open_list_cell( 'id', any_link( 'profile', $id ), 'class=number' );
      $t = $r['script'];
      open_list_cell( 'script', inlink( '', "fscript=$t,text=$t" ) );
      open_list_cell( 'utc', $r['utc'] );
      open_list_cell( 'wallclock_seconds', sprintf( '%8.3lf', $s ), 'number' );
      open_list_cell( 'rows_returned', $r['rows_returned'], 'number' );
      open_list_cell( 'sql', substr( $r['sql'], 0, 300 ) );
      $stack = json_decode( $r['stack'], 1 );
      $t = '[length:'.strlen( $r['stack'] ).']';
      if( isarray( $stack ) ) {
        foreach( $stack as $s ) {
          $t .= span_view( 'qquadr', adefault( $s, 'function', '???' ) ). ' ';
        }
      } else {
        $t .= $stack;
      }
      open_list_cell( 'stack', $t );
  }
close_list();

?>
