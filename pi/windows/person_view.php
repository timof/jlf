<?php

$aff = false;

init_var( 'affiliations_id', 'global,type=u,sources=http,set_scopes=' );
if( $affiliations_id ) {
  $aff = sql_affiliations( $affiliations_id, array( 'single_row' => true, 'default' => 'null' ) );
}
if( $aff ) {
  init_var( 'people_id', 'global,type=u,sources=initval,set_scopes=self,initval='.$aff['people_id'] );
} else {
  init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );
}
if( ! $people_id ) {
  open_div( 'warn', we('no person selected','keine Person gewaehlt') );
  return;
}

$person = sql_person( $people_id );
$aff_rows = sql_affiliations( "people_id=$people_id", 'orderby=affiliations.priority' );
$naff = count( $aff_rows );

open_fieldset( 'qquads old', we('Person','Person') );

  open_div('bold medskips', $person['cn'] );

  if( ! ( $person['flag_institute'] ) ) {
    open_div( 'smallskips bold', we('not listed on public institute page','nicht auf öffentlicher Institutsseite gelistet' ) );
  }
  if( $person['flag_virtual'] ) {
    open_div( 'smallskips bold', we('virtual account - not a real person','virtueller account - keine reale Person' ) );
  }
  if( $person['flag_deleted'] ) {
    open_div( 'smallskips bold', we('marked as deleted','als gelöscht markiert' ) );
  }

  if( $person['jpegphoto'] ) {
    open_div( 'smallskips center', photo_view( $person['jpegphoto'], $person['jpegphotorights_people_id'] ) );
  }

  for( $j = 0; $j < $naff; $j++ ) {
    if( $naff > 1 ) {
      open_fieldset('table td:smallskips;quads', we('Contact ','Kontakt '). ($j+1) );
    } else {
      open_fieldset('table notop td:smallskips;quads' );
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
        open_tr('smallskipb');
          open_td();
          open_td( 'colspan=2', $fa['note'] );
      }
      open_tr( 'td:solidtop' );
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
    close_fieldset();
  }

  if( $logged_in ) {
    open_div('right', inlink( 'person_edit', array(
      'class' => 'button edit', 'text' => we('edit','bearbeiten' )
    , 'people_id' => $people_id
    , 'inactive' => priv_problems( 'person', 'edit', $people_id )
    ) ) );
  }

  if( $people_id && have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    echo inlink( 'references', "referent=people,referent_id=$people_id,text=references" );
  }

close_fieldset();

?>
