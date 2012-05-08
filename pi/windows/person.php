<?php

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'people_id', 'global,type=u,sources=self http,set_scopes=self' );

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
  , 'tables' => 'people'
  , 'failsafe' => 0   // means: possibly return with NULL value (flagged as error)
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $people_id ) {
    $person = sql_person( $people_id );
    $opts['rows'] = array( 'people' => $person );
    $aff_rows = sql_affiliations( "people_id=$people_id", 'affiliations.priority' );
    $naff_old = max( count( $aff_rows ), 1 );
    $auth_methods_array = explode( ',', $person['authentication_methods'] );
    $person['auth_method_simple'] = ( in_array( 'simple', $auth_methods_array ) ? 1 : 0 );
    $person['auth_method_ssl'] = ( in_array( 'ssl', $auth_methods_array ) ? 1 : 0 );
    if( ( $edit_account = have_priv( 'person', 'account', $people_id ) ) ) {
      $edit_pw = 1;
    } else {
      $edit_pw = $person['auth_method_simple'];
    }
  } else {
    $aff_rows = array();
    $naff_old = 1;
    $edit_account = $edit_pw = 0;
  }

  $fields = array(
      'title' => 'size=10'
    , 'gn' => 'size=40'
    , 'sn' => 'size=40'
    , 'jpegphoto' => 'set_scopes='
  );
  if( $edit_account ) {
    $fields['auth_method_simple'] = 'type=b';
    $fields['auth_method_ssl'] = 'type=b';
    $fields['uid'] = 'size=20';
  }
  $f = init_fields( array(
      'title' => 'size=10'
    , 'gn' => 'size=40'
    , 'sn' => 'size=40'
    , 'jpegphoto' => 'set_scopes='
    )
  , $opts
  );

  $problems = & $f['_problems'];
  $changes = & $f['_changes'];

  if( $edit_account ) {
    $auth_methods_array = array();
    if( $f['auth_method_simple']['value'] ) {
      $auth_methods_array[] = 'simple';
      if( ! $person['passwd_hash_function'] ) {
        $problems['passwd'] = $problems['passwd2'] = 'need password';
        $f['passwd']['class'] = $f['passwd2']['class'] = 'problem';
      }
    }
    if( $f['auth_method_ssl']['value'] )
      $auth_methods_array[] = 'ssl';
    if( $flag_problems ) {
      if( $auth_methods_array ) {
        if( ! $f['uid']['value'] ) {
          $f['uid']['class'] = 'problem';
          $f['uid']['problem'] = 'need uid';
          $f['_problems']['uid'] = 'need uid';
        }
      }
    }
  }

  $opts['tables'] = 'affiliations';
  if( $reinit == 'reset' ) {
    init_var( 'naff', "global,type=U,sources=keep,default=1,set_scopes=self,old=$naff_old" );
  } else {
    init_var( 'naff', "global,type=U,sources=self keep,default=1,set_scopes=self,old=$naff_old" );
  }
  for( $j = 0; $j < $naff; $j++ ) {
    $opts['rows'] = array( 'affiliations' => adefault( $aff_rows, $j, array() ) );
    $opts['prefix'] = "aff{$j}_";
    $faff[ $j ] = init_fields( array(
        'priority' => "sources=default,default=$j"
      , 'roomnumber' => 'size=40'
      , 'groups_id' => array( 'more_choices' => array( 0 => ' (keine) ' ) )
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

  handle_action( array( 'reset', 'save', 'update', 'init', 'template', 'naffPlus', 'naffDelete', 'deletePhoto' ) );
  switch( $action ) {
    case 'template':
      $people_id = 0;
      $edit_pw = $edit_account = 0;
      break;

    case 'save':
      // debug( $f, 'f' );
      if( $edit_pw ) {
        $pw = init_var( 'passwd', 'type=h32,default=,scopes=http' );
        $pw2 = init_var( 'passwd2', 'type=h32,default=,scopes=http' );
        if( $pw !== $pw2 ) {
          $problems['passwd'] = $problems['passwd2'] = "passwords don't match";
        } else {
          auth_set_password( $people_id, $pw );
        }
      }
      if( ! $problems ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $r['value'];
        }
        if( $people_id ) {
          if( $edit_account ) {
            unset( $values['auth_method_simple'] );
            unset( $values['auth_method_ssl'] );
            $values['authemtication_methods'] = implode( ',', $auth_methods_array );
          }
          sql_update( 'people', $people_id, $values );
          sql_delete_affiliations( "people_id=$people_id", NULL );
        } else {
          $people_id = sql_insert( 'people', $values );
        }
        for( $j = 0; $j < $naff; $j++ ) {
          $values = array();
          foreach( $faff[ $j ] as $fieldname => $r ) {
            if( $fieldname[ 0 ] !== '_' )
              $values[ $fieldname ] = $r['value'];
          }
          $values['people_id'] = $people_id;
          // debug( $values, "values $j" );
          sql_insert( 'affiliations', $values );
        }
        reinit('reset');

      } else {
        // debug( $problems, 'problems' );
      }

      break;

    case 'naffPlus':
      $naff++;
      reinit('self');
      break;

    case 'naffDelete':
      // debug( $GLOBALS['jlf_persistent_vars']['self'], 'self before' );
      while( $message < $naff - 1 ) {
        mv_persistent_vars( 'self', '/^aff'.($message+1).'_/', "aff{$message}_" );
        $message++;
      }
      // debug( $GLOBALS['jlf_persistent_vars']['self'], 'self after' );
      $naff--;
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

if( $people_id ) {
  open_fieldset( 'small_form old', we('permanent data for person','Stammdaten Person') );
} else {
  open_fieldset( 'small_form new', we('new person','neue Person') );
}
  open_table('small_form hfill');
    open_tr();
      open_th( 'colspan=2', 'Person:' );
    open_tr();
      open_td( array( 'label' => $f['title'] ), we('Title:','Titel:') );
      open_td( '', string_element( $f['title'] ) );
    open_tr();
      open_td( array( 'label' => $f['gn'] ), we('First name(s):','Vorname(n):') );
      open_td( '', string_element( $f['gn'] ) );
    open_tr();
      open_td( array( 'label' => $f['sn'] ), we('Last name:','Nachname:') );
      open_td( '', string_element( $f['sn'] ) );
if( $people_id ) {
    if( $f['jpegphoto']['value'] ) {
      open_tr();
        open_td( '', we('existing photo:','vorhandenes Foto:' ) );
        open_td( 'rowspan=2', html_tag( 'img', array(
            'height' => '100'
          , 'src' => 'data:image/jpeg;base64,' . $f['jpegphoto']['value']
          ), NULL
        ) );
      open_tr();
        open_td( 'right', inlink( '', 'action=deletePhoto,class=drop,title=Foto loeschen' ) );
    }
    open_tr();
      open_td( array( 'label' => $f['jpegphoto'] ), we('upload photo:','Foto hochladen:') );
      open_td( '', file_element( $f['jpegphoto'] ) . ' (jpeg, max. 200kB)' );
  if( $edit_account ) {
    open_tr();
      open_td( array( 'label' => $f['uid'] ), we('user id:','Benutzerkennung:') );
      open_td( '', string_element( $f['uid'] ) );
    open_tr();
      open_td( '', we('auth methods:','Verfahren:') );
      open_tr();
  
  }
}

    for( $j = 0; $j < $naff; $j++ ) {
      open_tr('medskip');
        open_th( 'colspan=2' );
          if( $naff > 1 ) 
            echo inlink( 'self', "class=href drop,title=Kontakt loeschen,action=naffDelete,message=$j" );
          printf( 'Kontakt %d:', $j+1 );
      open_tr();
        open_td( array( 'label' => $faff[ $j ]['roomnumber'] ), we('Group:','Gruppe:') );
        open_td();
          echo selector_groups( $faff[ $j ]['groups_id'] );
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
        open_td();
          echo textarea_element( $faff[ $j ]['note'] );
    }
    open_tr( 'medskip' );
      open_td( 'colspan=2', inlink( 'self', 'class=button plus,text=Kontakt hinzufuegen,action=naffPlus' ) );

    open_tr( 'bigskip' );
      open_td( 'right,colspan=2' );
        if( $people_id && ! $changes )
          template_button();
        reset_button( $changes ? '' : 'display=none' );
        submission_button();
  close_table();
close_fieldset();


?>
