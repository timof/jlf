<?php

init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $people_id ) {
  open_div( 'warn', we('no person selected','keine Person gewaehlt') );
  return;
}

$person = sql_person( $people_id );
$aff_rows = sql_affiliations( "people_id=$people_id", 'affiliations.priority' );
$naff = count( $aff_rows );

open_fieldset( 'small_form old', we('Person','Person') );

  open_table('small_form hfill');
    open_tr();
      open_td( 'colspan=2,bold', $person['cn'] );
    if( $f['jpegphoto']['value'] ) {
      open_tr();
        open_td( 'colspan=2' );
          echo html_tag( 'img'
          , array( 'height' => '100' , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value'] )
          , NULL
          );
    }

    for( $j = 0; $j < $naff; $j++ ) {
      if( $naff > 1 ) {
        open_tr('medskip');
          open_th( 'colspan=2' );
            printf( 'Kontakt %d:', $j+1 );
      }
      if( $faff[ $j ]['groups_id'] ) {
        open_tr();
          open_td( '', we('Group:','Gruppe:') );
          open_td( '', html_alink_group( $faff[ $j ]['groups_id'] ) );
            echo selector_groups( $faff[ $j ]['groups_id'] );
      }
      if( $faff[ $j ]['roomnumber'] ) {
        open_tr();
          open_td( '', we('Room:','Raum:') );
          open_td( '', $faff[ $j ]['roomnumber'] );
      }
      if( $faff[ $j ]['telephonenumber'] ) {
        open_tr();
          open_td( '', we('Phone:','Telefon:') );
          open_td( '', $faff[ $j ]['telephonenumber'] );
      }
      if( $faff[ $j ]['facsimiletelephonenumber'] ) {
        open_tr();
          open_td( '', 'Fax:' );
          open_td( '', $faff[ $j ]['facsimiletelephonenumber'] );
      }
      if( $faff[ $j ]['note'] ) {
        open_tr();
          open_td( 'colspan=2', $faff[ $j ]['note'] );
      }
    }

  close_table();
close_fieldset();


?>
