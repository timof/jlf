<?php

$f = init_fields( array(
  'groups_id'
, 'REGEX' => 'size=40,auto=1'
, 'year' => 'u4,min=2012,max=2999,allow_null=0,initval='.$current_year
), '' );

open_div('menubox table filters');
  open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( '', we('Group:','Bereich:') );
    open_td( '', filter_group( $f['groups_id'] ) );
  open_tr();
    open_th( '', we('Year:','Jahr:') );
    open_td( '', filter_year( $f['year'] ) );
  open_tr();
    open_th( '', we('Search:','Suche:') );
    open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' . filter_reset_button( $f['REGEX'] ) );
close_div();

publicationslist_view( '', 'allow_download=1' );

?>
