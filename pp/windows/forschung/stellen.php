<?php

$f = init_fields( array(
  'groups_id'
, 'REGEX' => 'size=40,auto=1'
, 'degree' => 'u4,min=2012,max=2999,allow_null=0,initval='.$current_year
), '' );

$f = init_fields( array( 'groups_id', 'degree_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_div('menubox table filters');
  open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
  open_tr();
    open_th( '', we('Group:','Bereich:') );
    open_td( '', filter_group( $f['groups_id'] ) );
  open_tr();
    open_th( '', we('Programme/Degree:','Studiengang/Abschluss:' ) );
    open_td( '', filter_degree( $f['degree_id'] ) );
  open_tr();
    open_th( '', we('search:','Suche:') );
    open_td( '', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'] ) );
close_div();

positionslist_view( $f['_filters'], 'allow_download=1' );

?>
