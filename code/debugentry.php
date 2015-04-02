<?php // /code/debugentry.php

$f = init_var( 'debug_id', 'global,type=U,sources=http persistent,set_scopes=self' );

need_priv( 'debug', 'list', $debug_id );

sql_transaction_boundary('debug');
$l = sql_debugentry( $debug_id );

$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'debug', $debug_id ) ) : '' );
open_fieldset( 'small_form', "debug entry $v" );
  open_table( 'hfill td:qquadr;smallskipb,colgroup=15% 85%' );
    open_tr();
      open_td( '', 'id:' );
      open_td( 'bold', $debug_id );
    open_tr();
      open_td( '', 'utc:' );
      open_td( 'bold', $l['utc'] );
    open_tr();
      open_td( '', 'script:' );
      open_td( 'bold', $l['script'] );
    open_tr();
      open_td( '', 'facility:' );
      open_td( 'bold', $l['facility'] );
    open_tr();
      open_td( '', 'object:' );
      open_td( 'bold', $l['object'] );
    open_tr();
      open_td( '', 'comment:' );
      open_td( 'bold', $l['comment'] );

    open_tr();
      open_td( 'solidtop bold,colspan=2', 'value:' );
    open_tr();
      open_td( 'qquadl bold,colspan=2' );
      open_pre( '', jlf_var_export_html( json_decode( $l['value'], 1 ) ) );

    open_tr();
      open_td( 'solidtop bold,colspan=2', 'stack:' );
    open_tr();
      open_td( 'qquadl bold,colspan=2' );
      open_pre( '', $l['stack'] ? jlf_var_export_html( json_decode( $l['stack'], 1 ) ) : '-' );

  close_table();

close_fieldset();


?>
