<?php

sql_transaction_boundary( '*' );
// sql_transaction_boundary( 'rooms,owning_group=groups,contact=people,contact2=people' );

echo html_tag( 'h1', '', we('Labs and Contact Persons','Labore und Laborverantwortliche') );

$f = init_fields( array( 'groups_id', 'REGEX' => 'size=40,auto=1' ) , '' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('Search:','Suche:') );
      open_td( '', ' / '.string_element( $f['REGEX'] ).' / ' );
  close_table();

close_div();

$list_options = handle_list_options( true, 'rooms', array(
  'roomnumber' => 's,t=1,h='.we('roomnumber','Raumnummer')
, 'groups_id' => 's=owning_group_cn,t=1,h='.we('group','Gruppe')
, 'contact_cn' => 's,t=1,h='.we('responsible person','Verantwortliche Person')
, 'contact2_cn' => 's,t=1,h='.we('deputy','Vertretung')
) );

$rows = sql_query( 'rooms', array( 'filters' => $f['_filters'] ) );
if( ! $rows ) {
  open_div( '', we( '(no rooms found)', "(keine R{$aUML}ume gefunden)") );
  return;
}
$count = count( $rows );
$list_options['limits'] = false;

open_list( $list_options );
  open_list_row('header');
    open_list_cell( 'roomnumber' );
    open_list_cell( 'groups_id' );
    open_list_cell( 'contact' );
    open_list_cell( 'contact2' );
  foreach( $rows as $r ) {
    open_list_row();
      open_list_cell( 'roomnumber', $r['roomnumber'] );
      open_list_cell( 'groups_id', alink_group_view( $r['groups_id'], 'fullname=1' ) );
      open_list_cell( 'contact', alink_person_view( $r['contact_people_id'], 'office=1' ) );
      open_list_cell( 'contact2', alink_person_view( $r['contact2_people_id'], 'office=0,default='.we(' - ',' - ') ) );
  }
close_list();



?>
