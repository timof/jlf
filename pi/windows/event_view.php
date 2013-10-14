<?php // /pi/windows/event_view.php

sql_transaction_boundary('*');

init_var( 'events_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $events_id ) {
  open_div( 'warn', we('no event selected','keine Veranstaltung gewaehlt') );
  return;
}

$event = sql_one_event( $events_id );

if( $deliverable ) switch( $deliverable ) {

  case 'event':
    $event = array(
      'cn_en' => $event['cn_en']
    , 'cn_de' => $event['cn_de']
    , 'note_en' => $event['note_en']
    , 'note_de' => $event['note_de']
    , 'date' => $event['date']
    , 'time' => $event['time']
    , 'location' => $event['location']
    , 'url' => $event['url']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'event', 'pdf'
        , tex2pdf( 'event.tex', array( 'loadfile', 'row' => $event ) )
        );
        break;
      case 'ldif':
        begin_deliverable( 'event', 'ldif'
        , ldif_encode( $event )
        );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'events', $events_id ) ) : '' );
open_fieldset( 'qquads old', we( 'event', 'Veranstaltung' ) . $v );

  echo html_tag('h2', '', $event['cn'] );
  echo html_span( '', $event['note'] );

  open_table('css');
    open_tr();
      open_th( '', we('date:','Datum:' ) );
      open_td( '', $event['date'] );
    if( $event['time'] ) {
      open_tr();
        open_th( '', we('time:','Zeit:' ) );
        open_td( '', $event['time'] );
    }
    if( $event['location'] ) {
      open_tr();
        open_th( '', we('location:','Ort:' ) );
        open_td( '', $event['location'] );
    }
    if( $event['groups_id'] || $event['people_id'] ) {
      open_tr();
        open_th( '', we('contact:','Kontakt/Veranstalter:' ) );
        open_td();
          if( $event['groups_id'] ) {
            open_div( 'oneline', alink_group_view( $event['groups_id'] ) );
          }
          if( $event['people_id'] ) {
            open_div( 'oneline', alink_person_view( $event['people_id'] ) );
          }
    }

  close_table();
    
  open_div( 'right bigskips' );
    // echo download_button( 'event', 'ldif,pdf', "events_id=$events_id" );
    if( have_priv( 'events', 'edit', $events_id ) ) {
      echo inlink( 'events_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'events_id' => $events_id
      , 'inactive' => priv_problems( 'events', 'edit', $events_id )
      ) );
    }
  close_div();

close_fieldset();

?>
