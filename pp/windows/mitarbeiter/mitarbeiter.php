<?php

$f = init_fields( array( 'groups_id' , 'REGEX' => 'size=40,auto=1' ), '' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', html_span( 'floatright', filter_reset_button( $f ) ) . 'Filter' );
  open_tr();
    open_th( '', we('Group:','Bereich:') );
    open_td( '', filter_group( $f['groups_id'] ) );
  open_tr();
    open_th( '', we('Search:','Suche:') );
    open_td( '', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'] ) );
close_table();

peoplelist_view( $f['_filters'], "allow_download=1" );

?>
