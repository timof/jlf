<?php

echo html_tag( 'h1', '', 'Gegenstände' );

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window' );

$fields = init_fields( array(
  'anschaffungsjahr' => "min=$geschaeftsjahr_min,max=$geschaeftsjahr_max,initval=0,allow_null=0"
, 'geschaeftsjahr' => "min=$geschaeftsjahr_min,max=$geschaeftsjahr_max,initval=$geschaeftsjahr_thread,allow_null=0"
, 'cn' => 'type=h,size=20,auto=1,relation=~'
) );
$filters = $fields['_filters'];

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', 'Anschaffungsjahr:' );
    open_td( '', filter_anschaffungsjahr( $fields['anschaffungsjahr'] ) );
  open_tr();
    open_th( '', 'Gesch'.H_AMP.'auml;ftsjahr:' );
    open_td( '', filter_geschaeftsjahr( $fields['geschaeftsjahr'] ) );
  open_tr();
    open_th( '', 'Bezeichnung:' );
    open_td( '', string_element( $fields['cn'] ) );
close_table();

medskip();

thingslist_view( $filters, '' );

?>
