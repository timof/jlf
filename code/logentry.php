<?php // /code/logentry.php

sql_transaction_boundary('logbook,sessions');
$f = init_var( 'logbook_id', 'global,type=U,sources=http persistent,set_scopes=self,min=1,max='.sql_logbook_max_logbook_id() );

need_priv( 'logbook', 'list', $logbook_id );

$l = sql_logentry( $logbook_id );

$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'logbook', $logbook_id ) ) : '' );
open_fieldset( 'small_form', "logbook entry $v" );
  open_table( 'hfill td:qquadr;smallsbipb,colgroup=15% 85%' );
    open_tr();
      open_td( '', 'id:' );
      open_td( 'bold', selector_int( $f ) );
    open_tr();
      open_td( '', 'utc:' );
      open_td( 'bold', $l['utc'] );
    open_tr();
      open_td( '', 'session:' );
      open_td( 'bold', $l['sessions_id'] );
    open_tr();
      open_td( 'small', 'parent:' );
      open_td( 'small bold', "{$l['parent_thread']} / {$l['parent_window']} / {$l['parent_script']}" );
    open_tr();
      open_td( '', 'self:' );
      open_td( 'bold', "{$l['thread']} / {$l['window']} / {$l['script']}" );
    open_tr();
      open_td( 'solidtop', 'tags:' );
      open_td( 'solidtop bold kbd', $l['tags'] );
    open_tr();
      open_td( '', 'flags:' );
      open_td( 'kbd bold' );
        for( $i = 1; isset( $log_flag_text[ $i ] ); $i <<= 1 ) {
          if( $l['flags'] & $i )
            open_span( 'qquadr', $log_flag_text[ $i ] );
        }
    open_tr();
      open_td( '', 'links:' );
      open_td( 'kbd bold', inlinks_view( $l['links'] ) );
    open_tr();
      open_td( '', 'note:' );
      open_td( 'kbd bold', $l['note'] );

if( ( $stack = $l['stack'] ) ) {
    if( ( $s = json_decode( $stack, 1 ) ) ) { // compatibility kludge: may already be html
      $stack = jlf_var_export_html( $s );
    }
    open_tr();
      open_td( 'solidtop bold,colspan=2', 'stack:' );
    open_tr();
      open_td( 'qquadl bold,colspan=2' );
      open_pre( '', $stack );
}

  close_table();

close_fieldset();


?>
