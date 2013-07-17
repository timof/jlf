<?php

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


open_fieldset( 'small_form old', we('Room:','Raum:' ) );

  open_table('css=1,small_form hfill td:smallskips;qquads');
    open_tr( 'bigskips' );
      open_td( '', we('Room:','Raum:') );
      open_td( 'bold', $room['roomnumber'] );

    open_tr( 'medskip' );
      open_td( '', we('belongs to Group:','zugeordnert zu Gruppe:') );
      open_td( 'oneline', html_alink_group( $room['groups_id'] ) );

    open_tr( 'medskip' );
      open_td( '', we('responsible person:','verantwortliche Person:') );
      open_td( 'oneline', html_alink_person( $room['contact_people_id'] ) );

    open_tr();
      open_td( 'colspan=2', $room['note'] );


  close_table();

  open_div('right');
    echo download_button( 'ldif,pdf', 'room' );
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
