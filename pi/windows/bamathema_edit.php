<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'bamathemen_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self keep default';
      break;
    case 'self':
      $sources = 'self keep default';  // need keep here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'keep default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'bamathemen'
  , 'failsafe' => false   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $bamathemen_id ) {
    $bamathema = sql_one_bamathema( $bamathemen_id );
    $opts['rows'] = array( 'bamathemen' => $bamathema );
  }

  $f = init_fields( array(
      'cn' => 'size=80'
    , 'beschreibung' => 'lines=10,cols=80'
    , 'url' => 'size=60'
    , 'abschluss' => 'auto=1'
    , 'groups_id'
    , 'ansprechpartner_people_id'
    , 'pdf' => 'set_scopes='
    )
  , $opts
  );
  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'deletePdf' ) ); 
  switch( $action ) {
    case 'template':
      $bamathemen_id = 0;
      break;

    case 'save':
      if( ! $f['_problems'] ) {

        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            if( $fieldname['source'] !== 'keep' ) // no need to write existing blob
              $values[ $fieldname ] = $r['value'];
        }
        // debug( strlen( $values['pdf'] ), 'size of pdf' );
        // debug( $values, 'values' );
        if( $bamathemen_id ) {
          sql_update( 'bamathemen', $bamathemen_id, $values );
        } else {
          $bamathemen_id = sql_insert( 'bamathemen', $values );
        }
        reinit('reset');
      }
      break;

    case 'deletePdf':
      need( $bamathemen_id );
      sql_update( 'bamathemen', $bamathemen_id, array( 'pdf' => '' ) );
      reinit('self');
      break;

  }

} // while $reinit

// debug( $_POST, '_POST' );
// debug( $f['pdf']['source'], 'pdf source' );
// debug( strlen( $f['pdf']['value'] ), 'sizeof pdf value' );

if( $bamathemen_id ) {
  open_fieldset( 'small_form old', we( 'Data of topic', 'Daten Thema' ) );
} else {
  open_fieldset( 'small_form new', we( 'New topic', 'neues Thema' ) );
}
  open_table('small_form hfill');
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['cn'] ), we('Topic:','Thema:') );
      open_td( '', string_element( $f['cn'] ) );
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['abschluss'] ), we('Degree:','Abschluss:') );
      open_td( 'oneline' );
        $a = $f['abschluss'];
        foreach( $abschluss_text as $abschluss_id => $abschluss_cn ) {
          $a['mask'] = $abschluss_id;
          open_span( 'quadr', checkbox_element( $a ). "$abschluss_cn " );
        }
    open_tr();
      open_td( array( 'label' => $f['beschreibung'] ), we('Description:','Beschreibung:') );
      open_td( '', textarea_element( $f['beschreibung'] ) );
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['url'] ), we('Web page:','Webseite:') );
      open_td( '', string_element( $f['url'] ) );
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['groups_id'] ), we('Group:','Gruppe:') );
      open_td();
        selector_groups( $f['groups_id'] );
if( $f['groups_id']['value'] ) {
    open_tr( 'medskip' );
      open_td( array( 'label' => $f['ansprechpartner_people_id'] ), we('advisor:','Ansprechpartner:' ) );
      open_td();
        selector_people( $f['ansprechpartner_people_id'], array( 'filters' => array( 'groups_id' => $f['groups_id']['value'] ) ) );
}
if( $bamathemen_id ) {
    if( $f['pdf']['value'] ) {
      open_tr( 'bigskip' );
        open_td( '', we('available document:', 'vorhandene Datei:' ) );
        open_td('oneline');
          echo download_link( 'bamathemen_pdf', $bamathemen_id, 'class=file,text=download .pdf' );
          quad();
          echo inlink( '', 'action=deletePdf,class=drop,title=PDF loeschen' );

    }
    open_tr( 'bigskip' );
      open_td( array( 'label' => $f['pdf'] ), 'PDF upload:' );
      open_td( '', file_element( $f['pdf'] ) );
}

    open_tr( 'bigskip' );
      open_td( 'right,colspan=2' );
        if( $bamathemen_id && ! $f['_changes'] )
          template_button();
        reset_button( $f['_changes'] ? '' : 'display=none' );
        // submission_button( $f['_changes'] ? '' : 'display=none' );
        submission_button();
  close_table();

close_fieldset();

?>
