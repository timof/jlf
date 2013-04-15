<?php

init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $people_id ) {
  open_div( 'warn', we('no person selected','keine Person gewaehlt') );
  return;
}

$person = sql_person( $people_id );
$aff_rows = sql_affiliations( "people_id=$people_id", 'orderby=affiliations.priority' );
$naff = count( $aff_rows );

open_fieldset( 'old', we('Person','Person') );
  open_table('css=1');
    open_caption('', $person['cn'] );

    if( ! ( $person['flag_institute'] ) ) {
      open_tr();
        open_td( 'bold', 'nicht auf Institutsseite gelistet' );
    }
    if( $person['flag_virtual'] ) {
      open_tr();
        open_td( 'bold', 'virtueller account - keine reale Person' );
    }
    if( $person['flag_deleted'] ) {
      open_tr();
        open_td( 'bold', 'als geloescht markiert' );
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
        open_tr('bigskip');
          open_th( 'colspan=2' );
            printf( 'Kontakt %d:', $j+1 );
      }
      $fa = & $aff_rows[ $j ];
      if( $fa['groups_id'] ) {
        open_tr();
          open_td( '', we('Group:','Gruppe:') );
          open_td( '', html_alink_group( $fa['groups_id'] ) );
      }
      if( $fa['roomnumber'] ) {
        open_tr();
          open_td( '', we('Room:','Raum:') );
          open_td( '', $fa['roomnumber'] );
      }
      if( $fa['telephonenumber'] ) {
        open_tr();
          open_td( '', we('Phone:','Telefon:') );
          open_td( '', $fa['telephonenumber'] );
      }
      if( $fa['facsimiletelephonenumber'] ) {
        open_tr();
          open_td( '', 'Fax:' );
          open_td( '', $fa['facsimiletelephonenumber'] );
      }
      if( $fa['note'] ) {
        open_tr();
          open_td( 'colspan=2', $fa['note'] );
      }
      open_tr( 'smallskips solidtop' );
        open_td('right', we('position:','Stelle:') );
        open_td( 'oneline' );
          open_span( 'quads', adefault( $choices_typeofposition, $fa['typeofposition'], 'n/a' ) );

      if( have_priv( 'person', 'edit', $person ) ) {
            open_tr();
              open_td('right', we('teaching obligation:','Lehrverpflichtung:') );
        if( $fa['teaching_obligation'] ) {
              open_td( 'oneline', $fa['teaching_obligation'] . hskip('2em') . we('reduction: ','Reduktion: ') . $fa['teaching_reduction'] );
          if( $fa['teaching_reduction']['value'] ) {
            open_tr();
                open_td();
                open_td('oneline', we('reason: ','Grund: ') . $fa['teaching_reduction_reason'] );
          }
        } else {
              open_td('', we('none','keine') );
        }
      }
    }

    open_tr();
      open_td( 'colspan=2', inlink( 'person_edit', array(
        'class' => 'button edit', 'text' => we('edit','bearbeiten' )
      , 'people_id' => $people_id
      , 'inactive' => priv_problems( 'person', 'edit', $people_id )
      ) ) );

  close_table();
close_fieldset();


?>
