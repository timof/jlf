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
    , 'jpegphoto' => $event['jpegphoto']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'event', 'pdf'
        , tex2pdf( 'event.tex', array( 'loadfile', 'row' => $event ) )
        );
        break;
      case 'ldif':
        unset( $event['jpegphoto'] );
        begin_deliverable( 'event', 'ldif'
        , ldif_encode( $event )
        );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;

  case 'attachment': // for attached file
    begin_deliverable( 'attachment', 'pdf' , base64_decode( $event['pdf'] ) );
    return;

 case 'photo':
   begin_deliverable( 'photo', 'jpg', base64_decode( $event['jpegphoto'] ) );
   return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'events', $events_id ) ) : '' );
open_fieldset( 'qquads old', we( 'event', 'Veranstaltung' ) . $v );

  open_fieldset( '', we('attributes','Attribute') );
    open_ul();
      open_li( '', $event['flag_publish'] ? we('published',"ver{$oUML}ffentlicht") : we('not published',"nicht ver{$oUML}ffentlicht") );
      open_li( '', $event['flag_ticker'] ? we('show in ticker',"im Ticker anzeigen") : we('not shown in ticker',"nicht im Ticker anzeigen") );
      open_li( '', $event['flag_detailview'] ? we('has detail view',"mit Detailanzeige") : we('no detail view',"keine Detailanzeige") );
    close_ul();
  close_fieldset();

  open_fieldset( '', we('ticker view','Tickeranzeige'), "+++$NBSP$NBSP".event_view( $event, 'format=ticker' )."$NBSP$NBSP+++" );

  open_fieldset( '', we('table view','Tabellenanzeige') );
    open_table('events');
      for( $n = 1; $n <= 3; $n++ ) {
        echo event_view( $event, 'format=table' );
      }
    close_table();
  close_fieldset();

 $t = we('detail view','Detailanzeige');
  if( ! $event['flag_detailview'] ) {
    $t .= we(' (is disabled)',' (ist ausgeschaltet)');
  }
  open_fieldset( '', $t, event_view( $event, 'format=detail' ) );

  open_div( 'right bigskips' );
    // echo download_button( 'event', 'ldif,pdf', "events_id=$events_id" );
    if( have_priv( 'events', 'edit', $events_id ) ) {
      echo inlink( 'event_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'events_id' => $events_id
      , 'inactive' => priv_problems( 'events', 'edit', $events_id )
      ) );
    }
  close_div();

close_fieldset();

?>
