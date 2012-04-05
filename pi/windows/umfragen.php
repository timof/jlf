<?php

echo html_tag( 'h1', '', we('Topics for Bachelor/Master/... Theses','Themen fuer Bachelor/Master/...-Arbeiten' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'groups_id', 'abschluss_id' ) , '' );

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td();
      echo filter_group( $f['groups_id'] );
  open_tr();
    open_th( '', we('Degree:','Abschluss:' ) );
    open_td();
      echo filter_abschluss( $f['abschluss_id'] );
  open_tr();
    open_th( 'center,colspan=1', we('Actions','Aktionen') );
    open_td( 'center,colspan=1', inlink( 'bamathema', 'class=bigbutton,text='.we('New Topic','Neues Thema' ) ) );
close_table();

bigskip();


handle_action( array( 'update', 'deleteBamathema' ) );
switch( $action ) {
  case 'deleteBamathema':
    need( $message > 0, 'kein Thema ausgewaehlt' );
    sql_delete_bamathemen( $message );
    break;
}

medskip();

bamathemenlist_view( $f['_filters'], '' );

?>
