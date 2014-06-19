<?php

sql_transaction_boundary('*');

init_var( 'events_id', 'global,type=U6,sources=http self,set_scopes=self url' );

switch( $events_id ) {
  case '19':
    echo html_tag( 'h1', '', 'Institutstag am 2. Juli 2014' ); 

    open_table('td;th:qpads;smallpads th:solidbottom' );
      open_tr();
        open_th( '', 'Zeit' );
        open_th( '', 'Ort' );
        open_th( '', 'Was' );
      open_tr();
        open_td( '', '11.00' );
        open_td( '', '0.108' );
        open_td( '', 'Vorstellung der Bachelor- und Masterarbeitsthemen' );
      open_tr();
        open_td( '', '13.00' );
        open_td( '', '0.104' );
        open_td( '', "Institutsrat mit Wahl des gesch{$aUML}ftsf{$uUML}hrenden Leiters" );
      open_tr();
        open_td( '', '15.00' );
        open_td( '', 'Foyer' );
        open_td( '', 'Posterausstellung Fortgeschrittenenpraktikum' );
      open_tr();
        open_td( '', '14.00' );
        open_td( '', '0.108' );
        open_td( '', 'Vortrag "Neutrinos" von Walter Winter, Desy' );
      open_tr();
        open_td( '', '15.00' );
        open_td( '', 'Haus 28' );
        open_td( '', "Tag der offenen T{$uUML}r in den Arbeitsgruppen" );
      open_tr();
        open_td( '', '17.00' );
        open_td( '', 'Innenhof' );
        open_td( '', 'Grillfest' );
    close_table();
    return;
}


if( ! ( $event = sql_one_event( "events_id=$events_id,flag_publish", 0 ) ) ) {
  open_div( 'warn', 'query failed - no such event' );
  return;
}

if( $deliverable ) switch( $deliverable ) {

  case 'event':
     $event = array(
//       'dn' => "events_id=$events_id,ou=events,ou=physik,o=uni-potsdam,c=de"
         'cn' => $event['cn']
//     , 'programme_cn' => $position['programme_cn']
//     , 'groups_cn' => $position['groups_cn']
//     , 'people_cn' => $position['people_cn']
     , 'url' => $event['url']
     , 'note' => $event['note']
     );
     switch( $global_format ) {
//       case 'pdf':
//         begin_deliverable( 'position', 'pdf'
//         , tex2pdf( 'position.tex', array( 'loadfile', 'row' => $position ) )
//         );
//         break;
       case 'ldif':
         begin_deliverable( 'event', 'ldif' , ldif_encode( $event ) );
         break;
       default:
         error( "unsupported format: [$global_format]" );
     }
    return;

  default:
    error("no such deliverable: $deliverable");
}

echo event_view( $event, 'format=detail' );

?>
