<?php // /pi/windows/groupslist.php

// sql_transaction_boundary('groups,people,head=people,secretary=people,affiliations');
sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Groups','Gruppen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
  'flag_institute' => 'type=B,auto=1,default=2'
, 'flag_research' => 'type=B,auto=1,default=2'
, 'flag_publish' => 'type=B,auto=1,default=2'
) );

// if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
  open_div('menubox');
    open_table('css filters');
      open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
        open_tr();
          open_td( '', radiolist_element( $f['flag_institute'], 'choices='.we(':groups at institute:external groups:both',':Gruppen am Institut:externe Gruppen:alle' ) ) );
  
        open_tr();
          open_td( '', radiolist_element( $f['flag_research'], 'choices='.we(':research groups:other groups:both', ':Forschungsgruppen:sonstige:alle' ) ) );
    
        open_tr();
          open_td( '', radiolist_element( $f['flag_publish'], 'choices='.we(':listed on public pages:not listed:both', ":auf {$oUML}ffentliche Webseiten gelistete Gruppen:nicht gelistete:alle" ) ) );
    close_table();
    if( have_priv( 'groups', 'create' ) ) {
      open_table('css actions' );
        open_caption( '', we('Actions','Aktionen') );
        open_tr( '', inlink( 'group_edit', 'class=bigbutton,text='.we('Create new Group','Neue Gruppe anlegen') ) );
      close_table();
    }
  close_div();
// } else {
//    $f['flags']['value'] = GROUPS_FLAG_INSTITUTE | GROUPS_FLAG_ACTIVE;
// }

groupslist_view( $f['_filters'], 'orderby=status' );

?>
