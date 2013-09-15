<?php // /pi/windows/survey_edit.php

need( false );

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'surveys_id', 'global,type=u,sources=self http,set_scopes=self' );

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':
      $sources = 'http self initval default';
      break;
    case 'self':
      $sources = 'self initval default';  // need 'initval' here for big blobs!
      break;
    case 'reset':
      $flag_problems = 0;
      $sources = 'initval default';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'surveys,init' );
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => 1
  , 'tables' => 'surveys'
  , 'failsafe' => 0   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $surveys_id ) {
    $survey = sql_person( $surveys_id );
    $opts['rows'] = array( 'surveys' => $survey );
    $sf_rows = sql_surveyfields( "surveys_id=$surveys_id", 'surveyfields.priority' );
    $nsf_old = max( count( $sf_rows ), 1 );
  } else {
    $sf_rows = array();
    $nsf_old = 1;
  }

  $f = init_fields( array(
      'bla' => 'type=u,min=17,max=233,size=2'
    )
  , $opts
  );
  $problems = $f['_problems'];
  $changes = $f['_changes'];

  $opts['tables'] = 'affiliations';
  if( $reinit == 'reset' ) {
    init_var( 'nsf', "global,type=U,sources=initval,default=1,set_scopes=self,initval=$nsf_old" );
  } else {
    init_var( 'nsf', "global,type=U,sources=self initval,default=1,set_scopes=self,initval=$nsf_old" );
  }
  for( $j = 0; $j < $nsf; $j++ ) {
    $opts['rows'] = array( 'surveyfields' => adefault( $sf_rows, $j, array() ) );
    $opts['prefix'] = "aff{$j}_";
    $faff[ $j ] = init_fields( array(
        'priority' => "sources=default,default=$j"
      , 'roomnumber' => 'size=40'
      , 'groups_id' => 'type=U'
      , 'street' => 'size=40'
      , 'street2' => 'size=40'
      , 'city' => 'size=40'
      , 'telephonenumber' => 'size=40'
      , 'facsimiletelephonenumber' => 'size=40'
      , 'mail' => 'size=40'
      , 'note' => 'lines=4,cols=60'
      )
    , $opts
    );
    if( $faff[ $j ]['roomnumber']['value'] ) {
      $faff[ $j ]['roomnumber']['value'] =
        preg_replace( '/^(\d[.]\d+)$/', '2.28.$1', $faff[ $j ]['roomnumber']['value'] );
    }
    if( $faff[ $j ]['telephonenumber']['value'] ) {
      $faff[ $j ]['telephonenumber']['value'] =
        preg_replace( '/^(\d{4})$/', '+49 331 977 $1', $faff[ $j ]['telephonenumber']['value'] );
    }
    if( $faff[ $j ]['facsimiletelephonenumber']['value'] ) {
      $faff[ $j ]['facsimiletelephonenumber']['value'] =
        preg_replace( '/^(\d{4})$/', '+49 331 977 $1', $faff[ $j ]['facsimiletelephonenumber']['value'] );
    }
    $problems = array_merge( $problems, $faff[ $j ]['_problems'] );
    $changes = array_merge( $changes, $faff[ $j ]['_changes'] );
  }

  $reinit = false;

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'nsfPlus', 'nsfDelete', 'deletePhoto' ) );
  switch( $action ) {
    case 'template':
      $surveys_id = 0;
      break;

    case 'save':
      // debug( $f, 'f' );
      break;
      
      if( ! $problems ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $r['value'];
        }
        if( $surveys_id ) {
          sql_update( 'surveys', $surveys_id, $values );
          sql_delete_surveyfields( "surveys_id=$surveys_id", NULL );
        } else {
          $surveys_id = sql_insert( 'surveys', $values );
        }
        for( $j = 0; $j < $nsf; $j++ ) {
          $values = array();
          foreach( $faff[ $j ] as $fieldname => $r ) {
            if( $fieldname[ 0 ] !== '_' )
              $values[ $fieldname ] = $r['value'];
          }
          $values['surveys_id'] = $surveys_id;
          // debug( $values, "values $j" );
          sql_insert( 'surveyfields', $values );
        }
        reinit('reset');

      } else {
        // debug( $problems, 'problems' );
      }

      break;

    case 'nsfPlus':
      $nsf++;
      reinit('self');
      break;

    case 'nsfDelete':
      // debug( $GLOBALS['jlf_persistent_vars']['self'], 'self before' );
      while( $message < $nsf - 1 ) {
        mv_persistent_vars( 'self', '/^sf'.($message+1).'_/', "sf{$message}_" );
        $message++;
      }
      // debug( $GLOBALS['jlf_persistent_vars']['self'], 'self after' );
      $nsf--;
      reinit('self');
      break;

    case 'deletePhoto':
      need( $people_id );
      sql_update( 'people', $people_id, array( 'jpegphoto' => '' ) );
      reinit('self');
      break;

  }
}

// debug( $_FILES, 'FILES' );

// debug( $f['jpegphoto']['source'], 'jpegphoto: from source: ' + strlen( $f['jpegphoto']['value'] ) + ' bytes' );

if( $surveys_id ) {
  open_fieldset( 'small_form old', we('Survey','Umfrage') );
} else {
  open_fieldset( 'small_form new', we('New Survey','Neue Umfrage') );
}
  open_table('small_form hfill');
    open_tr();
      open_td( array( 'label' => $f['bla'] ), we('bla:','bla:') );
      open_td( '', selector_int( $f['bla'] ) );

    /*
    for( $j = 0; $j < $nsf; $j++ ) {
      open_tr('medskip');
        open_th( 'colspan=2' );
          if( $nsf > 1 ) 
            echo inlink( 'self', "class=href drop,title=Kontakt loeschen,action=nsfDelete,message=$j" );
          printf( 'Kontakt %d:', $j+1 );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['roomnumber'] ), we('Group:','Gruppe:') );
        open_td( '', selector_groups( $faff[ $j ]['groups_id'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['roomnumber'] ), we('Room:','Raum:') );
        open_td( '', string_element( $faff[ $j ]['roomnumber'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['street'] ), we('Street:','Strasse:') );
        open_td( '', string_element( $faff[ $j ]['street'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['street2'] ), ' ' );
        open_td( '', string_element( $faff[ $j ]['street2'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['city'] ), we('City:','Stadt:') );
        open_td( '', string_element( $faff[ $j ]['city'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['telephonenumber'] ), we('Phone:','Telefon:') );
        open_td( '', string_element( $faff[ $j ]['telephonenumber'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['facsimiletelephonenumber'] ), 'Fax:' );
        open_td( '', string_element( $faff[ $j ]['facsimiletelephonenumber'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['mail'] ), 'email:' );
        open_td( '', string_element( $faff[ $j ]['mail'] ) );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['note'] ), we('Note:','Notiz:') );
        open_td( '', textarea_element( $faff[ $j ]['note'] ) );
    }
    open_tr( 'medskip' );
      open_td( 'colspan=2', inlink( 'self', 'class=button plus,text=Kontakt hinzufuegen,action=nsfPlus' ) );

*/
    open_tr( 'bigskip' );
      open_td( 'right,colspan=2' );
        if( $surveys_id ) {
          echo template_button_view();
        }
        echo reset_button_view( $changes ? '' : 'display=none' );
        echo save_button_view();
  close_table();
close_fieldset();


?>
