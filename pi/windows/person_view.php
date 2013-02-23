<?php

init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $people_id ) {
  open_div( 'warn', we('no person selected','keine Person gewaehlt') );
  return;
}

$person = sql_person( $people_id );
$aff_rows = sql_affiliations( "people_id=$people_id", 'orderby=affiliations.priority' );
$naff = count( $aff_rows );

open_fieldset( 'small_form old', we('Person','Person') );
  open_table('small_form hfill');
    open_tr();
      open_td( 'colspan=2,bold', $person['cn'] );

    if( ! ( $person['flag_institute'] ) ) {
      open_tr();
        open_td( 'colspan=2,bold', 'nicht auf Institutsseite gelistet' );
    }
    if( $person['flag_virtual'] ) {
      open_tr();
        open_td( 'colspan=2,bold', 'virtueller account - keine reale Person' );
    }
    if( $person['flag_deleted'] ) {
      open_tr();
        open_td( 'colspan=2,bold', 'als geloescht markiert' );
    }
    if( $person['jpegphoto'] ) {
      open_tr();
        open_td( 'colspan=2' );
          echo html_tag( 'img'
          , array( 'height' => '100' , 'src' => ( 'data:image/jpeg;base64,' . $person['jpegphoto'] ) )
          , NULL
          );
    }

    for( $j = 0; $j < $naff; $j++ ) {
      if( $naff > 1 ) {
        open_tr('medskip');
          open_th( 'colspan=2' );
            printf( 'Kontakt %d:', $j+1 );
      }
      if( $aff_rows[ $j ]['groups_id'] ) {
        open_tr();
          open_td( '', we('Group:','Gruppe:') );
          open_td( '', html_alink_group( $aff_rows[ $j ]['groups_id'] ) );
      }
      if( $aff_rows[ $j ]['roomnumber'] ) {
        open_tr();
          open_td( '', we('Room:','Raum:') );
          open_td( '', $aff_rows[ $j ]['roomnumber'] );
      }
      if( $aff_rows[ $j ]['telephonenumber'] ) {
        open_tr();
          open_td( '', we('Phone:','Telefon:') );
          open_td( '', $aff_rows[ $j ]['telephonenumber'] );
      }
      if( $aff_rows[ $j ]['facsimiletelephonenumber'] ) {
        open_tr();
          open_td( '', 'Fax:' );
          open_td( '', $aff_rows[ $j ]['facsimiletelephonenumber'] );
      }
      if( $aff_rows[ $j ]['note'] ) {
        open_tr();
          open_td( 'colspan=2', $aff_rows[ $j ]['note'] );
      }
    }

    open_tr();
      open_td( 'colspan=2', inlink( 'person_edit', array(
        'class' => 'edit', 'text' => we('edit','bearbeiten' )
      , 'people_id' => $people_id
      , 'inactive' => priv_problems( 'person', 'edit', $people_id )
      ) ) );

  close_table();
close_fieldset();


?>
