<?php // /pi/windows/room_view.php

sql_transaction_boundary('*');

init_var( 'rooms_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $rooms_id ) {
  open_div( 'warn', we('no room selected','kein Raum gewählt') );
  return;
}

$room = sql_one_room( $rooms_id );

if( $deliverable ) switch( $deliverable ) {

  case 'room':
    $room = array(
      'roomnumber' => $room['roomnumber']
    , 'groups_cn' => $room['groups_cn']
    , 'contact_cn' => $room['contact_cn']
    , 'contact2_cn' => $room['contact2_cn']
    , 'note' => $room['note']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'room', 'pdf' , tex2pdf( 'room.tex', array( 'loadfile', 'row' => $room ) ) );
        break;
      case 'ldif':
        begin_deliverable( 'room', 'ldif' , ldif_encode( $room ) );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'rooms', $rooms_id ) ) : '' );
open_fieldset( 'small_form old', we('Room:','Raum:' ) . $v );

  open_table('css small_form hfill td:smallskips;qquads');
    open_tr( 'bigskips' );
      open_td( '', we('Room:','Raum:') );
      open_td( 'bold', $room['roomnumber'] );

    open_tr( 'medskip' );
      open_td( '', we('belongs to Group:','zugeordnert zu Gruppe:') );
      open_td( 'oneline', alink_group_view( $room['groups_id'] ) );

    open_tr( 'medskip' );
      open_td( '', we('responsible person:','verantwortliche Person:') );
      open_td( 'oneline', alink_person_view( $room['contact_people_id'], 'office' ) );

    open_tr( 'medskip' );
      open_td( '', we('deputy:','Stellvertretung:') );
      open_td( 'oneline', alink_person_view( $room['contact2_people_id'], 'office' ) );

    open_tr();
      open_td( 'colspan=2', $room['note'] );


  close_table();

  open_div('right');
    echo download_button( 'room', 'ldif,pdf', "rooms_id=$rooms_id" );
    if( $logged_in ) {
      echo inlink( 'room_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'rooms_id' => $rooms_id
      , 'inactive' => priv_problems( 'rooms', 'edit', $rooms_id )
      ) );
    }
  close_div();
  
close_fieldset();

?>
