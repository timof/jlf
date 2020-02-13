<?php

sql_transaction_boundary('*');

init_var( 'events_id', 'global,type=U6,sources=http self,set_scopes=self url' );

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('Institute / event details','Institut / Details zur Veranstaltung') );
  close_div();
close_div();

if( $events_id == 113) {
  require_once( 'pp/windows/institut/klimatag2019.php' );
  return;
}
if( $events_id == 121 ) {
  require_once( 'pp/windows/institut/klimatag.php' );
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

   case 'attachment': // for attached file
     begin_deliverable( 'attachment', 'pdf', base64_decode( $event['pdf'] ) );
     return;

   case 'photo':
     begin_deliverable( 'photo', 'jpg', base64_decode( $event['jpegphoto'] ) );
     return;

  default:
    error("no such deliverable: $deliverable");
}

if( $events_id == 79 ) {


      echo html_tag( "h1", '', 'Verleihung des Carl-Ramsauer-Preises 2016' );
      
      echo html_tag( 'img', 'class=floatright,width=400px,margin=1em,src=/pp/fotos/ramsauer/r29.jpg' );

      echo "Am 23. November wurde der Carl-Ramsauer-Preises der Physikalischen
           Gesellschaft zu Berlin für die besten Doktorarbeiten in
           Physik an der Humboldt-Universität, Freien Universität,
           Technischen Universität und Universität Potsdam verliehen. 
           Hier finden Sie einige Impressionen von der Veranstaltung 
           (Fotos: Daniela Höpfner).
           ";

     echo html_div( 'clear', '' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r01.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r02.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r03.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r04.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r05.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r06.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r07.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r08.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r09.jpg' );

     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r10.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r11.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r12.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r13.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r14.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r15.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r16.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r17.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r18.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r19.jpg' );

     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r20.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r21.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r22.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r23.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r24.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r25.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r26.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r27.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r28.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r29.jpg' );
     
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r30.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r31.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/ramsauer/r32.jpg' );

} else if( $events_id == 81 ) {


     echo html_tag( "h1", '', 'Absolventenverabschiedung 2016' );

     echo html_tag( 'img', 'class=floatright,width=400px,margin=1em,src=/pp/fotos/abs2016/abs2016e.jpg' );

     echo "Einige Impressionen von der Feier zur Verabschiedung der diesjährigen Absolventen am 09.12.2016 (Fotos: Achim Feldmeier)";

     echo html_div( 'clear', '' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/abs2016/abs2016a.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/abs2016/abs2016b.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/abs2016/abs2016c.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/abs2016/abs2016d.jpg' );
     echo html_tag( 'img', 'width=600px,medpads,src=/pp/fotos/abs2016/abs2016e.jpg' );

} else if( $events_id == 9999 ) {

  require_once( '/pp/windows/institut.klimatag.php' );
  
} else { 
  echo event_view( $event, 'format=detail' );
}

?>
