<?php

do {
  $reinit = false;
  init_var( 'tapes_id', 'global,pattern=u,sources=http persistent,default=0,set_scopes=self' );
  init_var( 'flag_problems', 'pattern=u,sources=persistent,default=0,global,set_scopes=self' );
  

  if( $tapes_id ) {
    $tape = sql_one_tape( $tapes_id );
    $oid_t = $tape['oid_t'] = oid_canonical2traditional( $tape['oid'] );
    $flag_modified = 1;
  } else { 
    $tape = array();
    $tape['oid_t'] = $oid_prefix;
    $flag_modified = 0;
  }

  $opts = array(
    'flag_problems' => & $flag_problems 
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'tapes'
  , 'rows' => array( 'tapes' => $tape )
  , 'failsafe' => false
  );
  if( $action === 'save' ) {
    $flag_problems = 1;
  }
  if( $action === 'reset' ) {
    $opts['reset'] = 1;
    $flag_problems = 0;
  }

  $f = init_fields( array(
      'cn' => 'W,default=,size=20'
    , 'type_tape'
    , 'oid_t' => 'pattern=Toid,cols=40'
    , 'good'
    , 'retired'
    , 'location' => 'size=20'
    )
  , $opts
  );

  if( $flag_problems ) {
    // check for additional problems which can prevent saving:
  }

  handle_action( array( 'update', 'save', 'reset', 'template' ) );

  switch( $action ) {

    case 'template':
      $tapes_id = 0;
      reinit();
      break;

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $f as $fieldname => $r ) {
          if( $fieldname[ 0 ] !== '_' )
            $values[ $fieldname ] = $r['value'];
        }
        $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
        unset( $values['oid_t'] );
        if( $tapes_id ) {
          sql_update( 'tapes', $tapes_id, $values );
        } else {
          $tapes_id = sql_insert( 'tapes', $values );
        }
        reinit();
      }
      break;
  }

} while( $reinit );

if( $tapes_id ) {
  open_fieldset( 'small_form old', "edit tape [$tapes_id]" );
} else {
  open_fieldset( 'small_form new', 'new tape' );
}
  open_table( 'hfill' );
  open_table( 'hfill,colgroup=20% 30% 50%' );
    open_tr();
      open_td();
        open_label( $f['cn'], 'cn:' );
      open_td( '', string_element( $f['cn'] ) );
      open_td( 'qquad' );
        open_label( $f['type_tape'], 'type:' );
          selector_type_tape( $f['type_tape'] );

    open_tr();
      open_td();
        open_label( $f['location'], 'location: ' );
      open_td( 'colspan=2', string_element( $f['location'] ) );

    open_tr();
      open_td();
        open_label( $f['oid_t'], 'oid: ' );
      open_td( 'colspan=2', string_element( $f['oid_t'] ) );

    open_tr();
      open_td();
        open_label( $f['good'], 'good: ' );
      open_td( '', checkbox_element( $f['good'] ) );

      open_td( 'qquad' );
        open_label( $f['retired'], 'retired: ' );
        echo checkbox_element( $f['retired'] );

    open_tr();
      open_td( 'right,colspan=3' );
        if( $tapes_id && ! $changes )
          template_button();
        submission_button();
  close_table();
close_fieldset();

if( $tapes_id ) {
  open_fieldset( 'small_form', 'chunks', 'on' );
    tapechunkslist_view( array( 'tapes_id' => $tapes_id ), false );
  close_fieldset();
}

?>
