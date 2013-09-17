<?php // /code/profileentry.php

$f = init_var( 'profile_id', 'global,type=U,sources=http persistent,set_scopes=self' );

need_priv( '*','*' );

sql_transaction_boundary('profile');
  $r = sql_query( 'profile', array( 'filters' => $profile_id, 'single_row' => 1 ) );
sql_transaction_boundary();

$v = any_link( 'profile', $profile_id );
open_fieldset( 'small_form', "profile entry $v" );
  open_table( 'hfill td:qquadr;smallskipb,colgroup=10% 90%' );
    open_tr();
      open_td( '', 'id:' );
      open_td( 'bold', $f['value'] );
    open_tr();
      open_td( '', 'utc:' );
      open_td( 'bold', $r['utc'] );
    open_tr();
      open_td( '', 'script:' );
      open_td( 'bold', $r['script'] );
    open_tr();
      open_td( '', 'rows_returned:' );
      open_td( 'bold', $r['rows_returned'] );
    open_tr();
      open_td( '', 'wallclock_seconds:' );
      open_td( 'bold', $r['wallclock_seconds'] );

    open_tr();
      open_td( 'solidtop,colspan=2', 'sql:' );
    open_tr();
      open_td( 'qquadl bold,colspan=2', $r['sql'] );

    $s = json_decode( $r['stack'], 1 );
    $s = jlf_var_export_html( $s );
    open_tr();
      open_td( 'solidtop,colspan=2', 'stack:' );
    open_tr();
      open_td( 'qquadl bold,colspan=2' );
        open_pre( '', $s );
  close_table();

close_fieldset();


?>
