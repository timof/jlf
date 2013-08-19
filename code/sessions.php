<?php // code/sessions.php

need_priv('*','*');

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

echo html_tag( 'h1', '', "sessions:" );


$fields = init_fields( array(
  'REGEX' => 'a,size=40,auto=1'
//  'people_id' => 'u'
) );

handle_action( array( 'update' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'deleteLogentry':
    menatwork();
}



open_div('menubox');
  open_table('css filters');
    open_caption( 'center th', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
//    open_tr();
//      open_th( '', 'person:' );
//      open_td( '', filter_person( $fields['people_id'] ) );
    open_tr();
      open_th( '', 'search:' );
      open_td( '', string_element( $fields['REGEX'] ) );
  close_table();
close_div();


sessions_view( $fields['_filters'] );

?>
