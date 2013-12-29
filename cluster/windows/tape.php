<?php

sql_transaction_boundary('*');

do {
  $problems = array();
  $reinit = false;
  init_var( 'tapes_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
  init_var( 'flag_problems', 'type=u,sources=persistent,default=0,global,set_scopes=self' );
  

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
      'cn' => 'size=40'
    , 'type_tape'
    , 'oid_t' => 'type=Toid,size=40'
    , 'good' => 'auto=1'
    , 'retired' => 'auto=1'
    , 'location' => array( 'type' => 'H', 'size' => '20', 'uid_choices' => uid_choices_locations( 'tapes' ) )
    , 'tapewritten_first' => 't,size=16'
    , 'tapewritten_last' => 't'
    , 'tapewritten_count' => 'u,size=2'
    , 'tapechecked_last' => 't'
    )
  , $opts
  );

  if( $flag_problems ) {
    // check for additional problems which can prevent saving:
  }

  handle_actions( array( 'save', 'reset', 'template', 'deleteTape' ) );

  if( $action ) switch( $action ) {

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
        if( ! ( $problems = sql_save_tape( $tapes_id, $values, 'check' ) ) ) {
          $tapes_id = sql_save_tape( $tapes_id, $values );
          need( isnumber( $tapes_id ) && ( $tapes_id > 0 ) );
          reinit('reset');
        }
      }
      break;
  }

} while( $reinit );

if( $tapes_id ) {
  open_fieldset( 'old', "edit tape " . any_link( 'tapes', $tapes_id ) );
} else {
  open_fieldset( 'new', 'new tape' );
}
  flush_all_messages();

  open_table('css=1,td:smallskips;quads');

    open_tr();
      open_td( '', label_element( $f['cn'], '', 'cn:' ) );
      open_td( '', string_element( $f['cn'] ) );
      open_td( 'qquad', label_element( $f['type_tape'], '', 'type: ' . selector_type_tape( $f['type_tape'] ) ) );

    open_tr();
      open_td( '', label_element( $f['location'], '', 'location:' ) );
      open_td( 'colspan=2', string_element( $f['location'] ) );

    open_tr();
      open_td( '', label_element( $f['oid_t'], '', 'oid: ' ) );
      open_td( 'colspan=2', string_element( $f['oid_t'] ) );

    open_tr();
      open_td( '', label_element( $f['tapewritten_last'], '', 'writes: ' ) );
      open_td( 'oneline,colspan=2' );
        open_span( 'qquadr', 'count: ' . int_element( $f['tapewritten_count'] ) );
        open_span( 'qquadr', 'first: ' . string_element( $f['tapewritten_first'] ) );
        open_span( 'qquadr', 'last: ' . string_element( $f['tapewritten_last'] ) );

    open_tr();
      open_td( '', label_element( $f['good'], '', 'checks: ' ) );
      open_td( 'oneline,colspan=2' );
        open_span( 'qquadr', 'last check: ' . string_element( $f['tapechecked_last'] ) );
        open_span( 'qquadr', 'good: ' . checkbox_element( $f['good'] ) );
        open_span( 'qquadr', 'retired: ' . checkbox_element( $f['retired'] ) );

  close_table();

  open_div('right');
    if( $tapes_id ) {
      echo template_button_view();
      echo inlink( 'self', array(
        'class' => 'drop button'
      , 'action' => 'deleteTape'
      , 'text' => 'delete'
      , 'confirm' => 'really delete?'
      , 'inactive' => sql_delete_tapes( $tapes_id, 'check' )
      ) );
    }
    echo save_button_view();
  close_div();

  if( $f['type_tape']['value'] ) {
    open_div( 'medskips comment', 'next unused oid: ' . sql_get_unused_oid( 'tapes', $f['type_tape']['value'] ) );
  }

close_fieldset();

if( $tapes_id ) {
  open_fieldset( '', 'content:' );
    tapechunkslist_view( array( 'tapes_id' => $tapes_id ), false );
  close_fieldset();
}

if( $action === 'deleteTape' ) {
  need( $tapes_id );
  if( sql_delete_tapes( $tapes_id ) ) {
    js_on_exit( "flash_close_message($H_SQ tape deleted $H_SQ );" );
    js_on_exit( "if(opener) opener.submit_form( {$H_SQ}update_form{$H_SQ} ); " );
  }
}

?>
