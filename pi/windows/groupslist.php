<?php

echo html_tag( 'h1', '', we('Groups','Gruppen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array( 'flags' => 'type=u,auto=1,default='.( GROUPS_FLAG_INSTITUTE | GROUPS_FLAG_ACTIVE ) ) );

$filters = array();
if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
  open_div('menubox');
    open_table('css filters');
      open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
        open_tr();
          $f['flags']['text'] = we('only institute members','nur Gruppen am Institut');
          $f['flags']['mask'] = GROUPS_FLAG_INSTITUTE;
          open_td( '', checkbox_element( $f['flags'] ) );
  
        open_tr();
          $f['flags']['text'] = we('only active groups','nur aktive Gruppen');
          $f['flags']['mask'] = GROUPS_FLAG_ACTIVE;
          open_td( '', checkbox_element( $f['flags'] ) );
    
        open_tr();
          $f['flags']['text'] = we('only groups listed on public site','nur auf öffentlicher Seite gelistete Gruppen');
          $f['flags']['mask'] = GROUPS_FLAG_LIST;
          open_td( '', checkbox_element( $f['flags'] ) );
    close_table();
    if( have_priv( 'groups', 'create' ) ) {
      open_table('css actions' );
        open_caption( '', we('Actions','Aktionen') );
        open_tr( '', inlink( 'group_edit', 'class=bigbutton,text='.we('Create new Group','Neue Gruppe anlegen') ) );
      close_table();
    }
  close_div();
} else {
  $f['flags']['value'] = GROUPS_FLAG_INSTITUTE | GROUPS_FLAG_ACTIVE;
}

handle_action( array( 'update', 'deleteGroup' ) );
switch( $action ) {
  case 'deleteGroup':
    need( $message > 0, 'keine Gruppe ausgewaehlt' );
    sql_delete_groups( $message );
    break;
}

if( $f['flags']['value'] & GROUPS_FLAG_INSTITUTE ) {
  $filters[] = array( 'INSTITUTE' );
}
if( $f['flags']['value'] & GROUPS_FLAG_ACTIVE ) {
  $filters[] = array( 'ACTIVE' );
}
if( $f['flags']['value'] & GROUPS_FLAG_LIST ) {
  $filters[] = array( 'LIST' );
}

groupslist_view( $filters, 'orderby=status' );

?>
