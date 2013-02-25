<?php

init_var( 'positions_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $positions_id ) {
  open_div( 'warn', we('no topic selected','keine Thema gewaehlt') );
  return;
}

$position = sql_one_position( $positions_id );

open_fieldset( 'small_form old' ); // , we( 'Data of topic', 'Daten Thema' ) );
  open_table('small_form hfill');
    open_tr( 'bigskips' );
      open_td( 'colspan=2,center bold larger', $position['cn'] );

    open_tr( 'medskip' );
      open_td( '', we('Degree:','Abschluss:') );
      open_td( 'oneline' );
        $a = $position['degree'];
        foreach( $degree_text as $degree_id => $degree_cn ) {
          if( $a & $degree_id ) {
            open_span( 'quadr', $degree_cn );
          }
        }
    open_tr();
      open_td( 'colspan=2', $position['note'] );

    if( ( $url = $position['url'] ) ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $position['url'] ), we('Web page:','Webseite:') );
        open_td( '', html_alink( $position['url'], array( 'text' => $position['url'] ) ) );
    }

    if( ( $pdf = $position['pdf'] ) ) {
      open_tr( 'bigskip' );
        open_td( '', we('more information:', 'weitere Informationen:' ) );
        // open_td( 'oneline', download_link( 'positions_pdf', $positions_id, 'class=file,text=download .pdf' ) );
        open_td( 'oneline', inlink( 'download', "item=positions_id,id=$positions_id,class=file,text=download .pdf" ) );
    }

    open_tr( 'medskip' );
      open_td( '', we('Group:','Gruppe:') );
      open_td( '', html_alink_group( $position['groups_id'] ) );

    open_tr( 'medskip' );
      open_td( '', we('Contact:','Ansprechpartner:') );
      open_td( '', html_alink_person( $position['contact_people_id'] ) );

    open_tr();
      open_td( 'right,colspan=2', inlink( 'position_edit', array(
        'class' => 'edit', 'text' => we('edit...','bearbeiten...' )
      , 'positions_id' => $positions_id
      , 'inactive' => priv_problems( 'positions', 'edit', $positions_id )
      ) ) );

  close_table();

close_fieldset();

?>
