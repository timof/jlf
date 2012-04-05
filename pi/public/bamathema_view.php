<?php

init_var( 'bamathemen_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $bamathemen_id ) {
  open_div( 'warn', we('no topic selected','keine Thema gewaehlt') );
  return;
}

$bamathema = sql_one_bamathema( $bamathemen_id );

open_fieldset( 'small_form old', we( 'Data of topic', 'Daten Thema' ) );
  open_table('small_form hfill');
    open_tr( 'medskip' );
      open_td( 'colspan=2', $bamathema['cn'] );

    open_tr( 'medskip' );
      open_td( '', we('Degree:','Abschluss:') );
      open_td( 'oneline' );
        $a = $bamathema['abschluss'];
        foreach( $abschluss_text as $abschluss_id => $abschluss_cn ) {
          if( $a & $abschluss_id ) {
            open_span( 'quadr', $abschluss_cn );
          }
        }
    open_tr();
      open_td( 'colspan=2', $bamathema['beschreibung'] );

    if( ( $url = $bamathema['url'] ) ) {
      open_tr( 'medskip' );
        open_td( array( 'label' => $f['url'] ), we('Web page:','Webseite:') );
        open_td( '', html_alink( $url, array( 'text' => 'url' ) ) );
    }

    if( ( $pdf = $bamathema['pdf'] ) ) {
      open_tr( 'bigskip' );
        open_td( '', we('more information:', 'weitere Informationen:' ) );
        open_td( 'oneline', download_link( 'bamathemen_pdf', $bamathemen_id, 'class=file,text=download .pdf' ) );
    }

    open_tr( 'medskip' );
      open_td( '', we('Group:','Gruppe:') );
      open_td( '', html_alink_gruppe( $bamathema['groups_id'] ) );

    open_tr( 'medskip' );
      open_td( '', we('Advisor:','Ansprechpartner:') );
      open_td( '', html_alink_person( $bamathema['ansprechpartner_people_id'] ) );

    if( have_priv( 'bamathema', 'edit', $bamathemen_id ) ) {
      open_tr();
        open_td( 'colspan=2', inlink( 'bamathema_edit', array(
          'class' => 'edit', 'text' => we('edit...','bearbeiten...' )
        , 'bamathemen_id' => $bamathemen_id
        ) ) );
    }
  close_table();

close_fieldset();

?>
