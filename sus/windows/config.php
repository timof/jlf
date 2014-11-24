<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Konfiguration' );

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window' );

$fields = init_fields( array( 'F_thread' => 'u' ) );

handle_actions( array( 'deletePersistentVar' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deletePersistentVar':
    need( $message );
    $var = sql_persistent_vars( $message );
    if( ! $var )
      break;
    need( ( $var['people_id'] == 0 ) || ( $var['people_id'] == $login_people_id ) );
    sql_delete_persistent_vars( $message );
    break;
}

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( 'right', 'thread:' );
    open_td( '', filter_thread( $fields['F_thread'] ) );
  close_table();
close_div();

bigskip();

$filters = $fields['_filters'];


$fields = array(
  'default_girokonto_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_girokonto_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_girokonto_id' )
  )
, 'default_erfolgskonto_zinsaufwand_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_erfolgskonto_zinsaufwand_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_erfolgskonto_zinsaufwand_id' )
  )
);
$f = init_fields( $fields );

if( isset( $f['default_girokonto_id']['modified'] ) ) {
  $uk_id = $f['default_girokonto_id']['value'];
  if( ( $uk_id == 0 ) || sql_one_unterkonto( "unterkonten_id=$uk_id,seite=A,kontenkreis=B,flag_bankkonto,flag_unterkonto_offen", 0 ) ) {
    sql_update( 'leitvariable', 'name=default_girokonto_id-sus', array( 'value' => $uk_id ) );
    $default_girokonto_id = $uk_id;
  } else {
    $f['default_girokonto_id']['normalized'] = $default_girokonto_id;
    $f['default_girokonto_id']['class'] = 'problem';
  }
}
if( isset( $f['_changes']['default_erfolgskonto_zinsaufwand_id'] ) ) {
  $uk_id = $f['default_erfolgskonto_zinsaufwand_id']['value'];
  if( ( $uk_id == 0 ) || sql_one_unterkonto( "unterkonten_id=$uk_id,seite=A,kontenkreis=E,flag_unterkonto_offen", 0 ) ) {
    sql_update( 'leitvariable', 'name=default_erfolgskonto_zinsaufwand_id-sus', array( 'value' => $uk_id ) );
    $default_erfolgskonto_zinsaufwand_id = $uk_id;
  } else {
    $f['default_erfolgskonto_zinsaufwand_id']['normalized'] = $default_erfolgskonto_zinsaufwand_id;
    $f['default_erfolgskonto_zinsaufwand_id']['class'] = 'problem';
  }
}


open_table( 'hfill list' );

  open_tr( 'medskip' );
    open_th( '', 'default Girokonto:' );
    open_td( '', selector_unterkonto( $f['default_girokonto_id'], array(
      'filters' => "seite=A,kontenkreis=B,flag_bankkonto,flag_unterkonto_offen"
    , 'choices' => array( 0 => we( ' (none) ', ' (keins) ' ) )
    ) ) );

  open_tr( 'medskip' );
    open_th( '', 'default Erfolgskonto Zinsaufwand:' );
    open_td( '', selector_unterkonto( $f['default_erfolgskonto_zinsaufwand_id'], array(
      'filters' => "seite=A,kontenkreis=E,flag_unterkonto_offen"
    , 'choices' => array( 0 => we( ' (none) ', ' (keins) ' ) )
    ) ) );

close_table();

smallskip();

?>
