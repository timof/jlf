<?php

echo html_tag( 'h1', '', we('People','Personen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'groups_id'
  , 'REGEX' => 'size=20,auto=1'
  )
, '' );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td();
      echo filter_group( $f['groups_id'] );
  open_tr();
    open_th( '', we('search:','suche:') );
    open_td( '', string_element( $f['REGEX'] ) );
  if( have_priv( 'person', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=2', we('Actions','Aktionen') );
    open_tr();
      open_td( 'center,colspan=2', inlink( 'person_edit', 'class=bigbutton,text='.we('New Person','Neue Person') ) );
  }
close_table();

bigskip();


handle_action( array( 'update', 'deletePerson' ) );
switch( $action ) {
  case 'deletePerson':
    need( $message > 0, we('no person selected','keine person ausgewaehlt') );
    need_priv( 'person', 'delete', $message );
    sql_delete_people( $message );
    break;
}

medskip();

peoplelist_view( $f['_filters'], '' );

?>
