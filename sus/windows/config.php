<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Konfiguration' );

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window' );

$fields = init_fields( array( 'F_thread' => 'u' ) );

handle_actions( array() );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deletePersistentVar':
    need( $message );
    $var = sql_persistent_vars( $message );
    if( ! $var ) {
      break;
    }
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


$fields_generic = array(
  'ust_satz_1_prozent' => array(
    'type' => 'F6'
  , 'sources' => 'http initval'
  , 'size' => 6
  , 'initval' => $ust_satz_1_prozent
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'ust_satz_1_prozent' )
  )
, 'ust_satz_2_prozent' => array(
    'type' => 'F6'
  , 'sources' => 'http initval'
  , 'size' => 6
  , 'initval' => $ust_satz_2_prozent
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'ust_satz_2_prozent' )
  )
, 'groessenklasse' => array(
    'type' => 'A8'
  , 'sources' => 'http initval'
  , 'initval' => $groessenklasse
  , 'pattern' => array( 'gross', 'mittel', 'klein', 'kleinst' )
  )
);

$fg = init_fields( $fields_generic );
foreach( $fields_generic as $name => $field ) {
  if( isset( $fg['_changes'][ $name ] ) ) {
    $v = $fg[ $name ]['value'];
    if( $v !== NULL ) {
      sql_update( 'leitvariable', "name=$name-*", array( 'value' => $v ) );
      $$name = $v;
      $info_messages[] = 'gespeichert: '.$leitvariable[ $name ]['meaning'];
    } else {
      $fg[ $name ]['normalized'] = $$name;
      $fg[ $name ]['class'] = 'problem';
    }
  }
}



$fields_konten = array(
  'default_girokonto_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_girokonto_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_girokonto_id' )
  , 'filters' => "seite=A,kontenkreis=B,flag_bankkonto,flag_unterkonto_offen"
  )
, 'default_erfolgskonto_zinsaufwand_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_erfolgskonto_zinsaufwand_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_erfolgskonto_zinsaufwand_id' )
  , 'filters' => "seite=A,kontenkreis=E,flag_unterkonto_offen"
  )
, 'default_bestandskonto_ustschuld_1_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_bestandskonto_ustschuld_1_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_bestandskonto_ustschuld_1_id' )
  , 'filters' => "seite=P,kontenkreis=B,flag_unterkonto_offen,flag_steuerkonto,ust_satz=1"
  )
, 'default_bestandskonto_ustschuld_2_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_bestandskonto_ustschuld_2_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_bestandskonto_ustschuld_2_id' )
  , 'filters' => "seite=P,kontenkreis=B,flag_unterkonto_offen,flag_steuerkonto,ust_satz=2"
  )
, 'default_bestandskonto_vorsteuerforderung_1_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_bestandskonto_vorsteuerforderung_1_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_bestandskonto_vorsteuerforderung_1_id' )
  , 'filters' => "seite=A,kontenkreis=B,flag_unterkonto_offen,flag_steuerkonto,ust_satz=1"
  )
, 'default_bestandskonto_vorsteuerforderung_2_id' => array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => $default_bestandskonto_vorsteuerforderung_2_id
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'default_bestandskonto_vorsteuerforderung_2_id' )
  , 'filters' => "seite=A,kontenkreis=B,flag_unterkonto_offen,flag_steuerkonto,ust_satz=2"
  )
);
$fk = init_fields( $fields_konten );

foreach( $fields_konten as $name => $field ) {
  if( isset( $fk['_changes'][ $name ] ) ) {
    $uk_id = $fk[ $name ]['value'];
    if( ( $uk_id == 0 ) || sql_one_unterkonto( array( '&&', "unterkonten_id=$uk_id", $field['filters'] ), 0 ) ) {
      sql_update( 'leitvariable', "name=$name-sus", array( 'value' => $uk_id ) );
      $$name = $uk_id;
      $info_messages[] = 'gespeichert: '.$leitvariable[ $name ]['meaning'];
    } else {
      $fk[ $name ]['normalized'] = $$name;
      $fl[ $name ]['class'] = 'problem';
    }
  }
}



$gbs = uid_choices_geschaeftsbereiche();
if( ! $gbs ) {
  $gbs = array( value2uid( '' ) => '' );
}
$gbfields = array();
foreach( $gbs as $gb ) {
  $hex = ( $gb ? hex_encode( $gb ) : 'z' );
  $gbfields[ $hex ] = array(
    'type' => 'u'
  , 'sources' => 'http initval'
  , 'initval' => adefault( $autovortragskonten, $hex, 0 )
  , 'readonly' => ! have_priv( 'leitvariable', 'write', 'autovortragskonten' )
  );
}
$gbf = init_fields( $gbfields );

$need_save = 0;
foreach( $gbs as $gb ) {
  $hex = ( $gb ? hex_encode( $gb ) : 'z' );
  if( isset( $gbf['_changes'][ $hex ] ) ) {
    $uk_id = $gbf[ $hex ]['value'];
    if( ( $uk_id == 0 ) || sql_one_unterkonto( array( 'unterkonten_id' => $uk_id, 'seite' => 'P', 'kontenkreis' => 'B', 'flag_unterkonto_offen' => 1, 'vortragskonto' => ( $gb ? $gb : '1' ) ), 0 ) ) {
      $autovortragskonten[ $hex ] = $uk_id;
      $need_save = 1;
    } else {
      $gbf[ $hex ]['normalized'] = $gbf[ $hex ]['initval'];
      $gbf[ $hex ]['class'] = 'problem';
      $gbf['_problems'] =& $gbf[ $hex ]['raw'];
    }
  }
}
if( $need_save && ! $gbf['_problems'] ) {
  sql_update( 'leitvariable', array( 'name' => 'autovortragskonten-sus' ), array( 'value' => parameters_implode( $autovortragskonten ) ) );
  $info_messages[] = 'Autovortragskonten gespeichert';
}

open_table( 'hfill list th:left' );
  
  open_tr( 'medskip' );
    open_th( '', "Groessenklasse" );
    open_td( '', selector_groessenklasse( $fg['groessenklasse'] ) );

  open_tr( 'medskip' );
    open_th( '', "Umsatzsteuer Satz 1 (regul{$aUML}r) in %" );
    open_td( '', string_element( $fg['ust_satz_1_prozent'] ) );

  open_tr( 'medskip' );
    open_th( '', "Umsatzsteuer Satz 2 (erm{$aUML}{$SZLIG}igt) in %" );
    open_td( '', string_element( $fg['ust_satz_2_prozent'] ) );

  foreach( $fields_konten as $name => $field ) {
    open_tr( 'medskip' );
    open_th( '', $leitvariable[ $name ]['meaning'] );
    open_td( '', selector_unterkonto( $fk[ $name], array( 'filters' => $field['filters'] , 'choices' => array( 0 => we( ' (none) ', ' (keins) ' ) ) ) ) );
  }

  foreach( $gbs as $gb ) {
    open_tr( 'medskip' );
      $hex = ( $gb ? hex_encode( $gb ) : 'z' );
      open_th( '', $gb ? "Vortragskonto $gb" : "Vortragskonto" );
      open_td( '', selector_unterkonto( $gbf[ $hex ], array(
        'filters' => array( 'seite' => 'P', 'kontenkreis' => 'B', 'flag_unterkonto_offen' => 1, 'vortragskonto' => ( $gb ? $gb : '1' ) )
      , 'choices' => array( 0 => we( ' (none) ', ' (keins) ' ) )
      ) ) );
  }

close_table();

smallskip();

?>
