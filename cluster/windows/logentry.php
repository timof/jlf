<?php

init_global_var( 'logbook_id', 'U', 'http,persistent', NULL, 'self' );
$l = sql_logentry( $logbook_id );

open_fieldset( 'small_form', 'logbook entry' );
  open_table();
    open_tr();
      open_th( '', 'nr:' );
      open_td();
      selector_int( $logbook_id, 'logbook_id', 1, sql_logbook_max_logbook_id() );
    open_tr();
      open_td( '', 'timestamp:' );
      open_td( '', $l['timestamp'] );
    open_tr();
      open_td( '', 'session:' );
      open_td( '', $l['sessions_id'] );
    open_tr();
      open_td( 'small', 'parent:' );
      open_td( 'small', "{$l['parent_thread']} / {$l['parent_window']} / {$l['parent_script']}" );
    open_tr();
      open_td( '', 'self:' );
      open_td( '', "{$l['thread']} / {$l['window']} / {$l['script']}" );
    open_tr();
      open_td( 'solidtop', 'event:' );
      open_td( 'solidtop', $l['event'] );
    open_tr();
      open_td( '', 'note:' );
      open_td( '', $l['note'] );

if( $l['stack'] ) {
    open_tr();
      open_th( 'solidtop,colspan=2', 'Stack:' );
    open_tr();
      open_td( 'colspan=2' );
      open_pre( '', $l['stack'] );
}

  close_table();
close_fieldset();


?>
