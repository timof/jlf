<?php // /code/session.php

$f = init_var( 'sessions_id', 'global,type=U,sources=http persistent,set_scopes=self' );

need_priv( '*','*' );

sql_transaction_boundary('sessions,people,persistentvars');

$r = sql_one_session( $sessions_id );

open_fieldset( 'small_form', 'session '.any_link( 'sessions', $sessions_id ) );

  open_table( 'hfill td:qquadr;smallskipb,colgroup=10% 90%' );
    open_tr();
      open_td( '', 'id:' );
      open_td( 'bold', $r['sessions_id'] );
    open_tr();
      open_td( '', 'person:' );
      open_td( 'bold', entry_link( 'people', $r['login_people_id'], array( 'text' => $r['cn'] ) ) ); 
    open_tr();
      open_td( '', 'authentication method:' );
      open_td( 'bold', $r['login_authentication_method'] );
    open_tr();
      open_td( '', 'ctime:' );
      open_td( 'bold', $r['ctime'] );
    open_tr();
      open_td( '', 'application:' );
      open_td( 'bold', $r['application'] );
    open_tr();
      open_td( '', 'login from:' );
      open_td( 'bold', "{$r['login_remote_ip']}:{$r['login_remote_port']}" );
    open_tr();
      open_td( '', 'last access from:' );
      open_td( 'bold', "{$r['latest_remote_ip']}:{$r['latest_remote_port']}" );
    open_tr();
      open_td( '', 'atime / valid:' );
      open_td( 'bold', "{$r['atime']} / {$r['valid']}" );
    open_tr();
      open_td( '', 'logbook:' );
      $n = sql_query( 'logbook', "sessions_id=$sessions_id,single_field=COUNT" );
      open_td( 'bold', inlink( 'logbook', "sessions_id=$sessions_id,text=$n entries" ) );
  close_table();

close_fieldset();

persistent_vars_view( "sessions_id=$sessions_id" );

?>
