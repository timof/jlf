<?php

sql_transaction_boundary('*');

init_var( 'events_id', 'global,type=U6,sources=http self,set_scopes=self url' );

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
