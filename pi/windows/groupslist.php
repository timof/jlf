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
          open_td( '', radiolist_element( $f['flag_institute'], 'choices='.we(':external groups:groups at institute:both',':Gruppen am Institut:externe Gruppen:alle' ) ) );
  
        open_tr();
          open_td( '', radiolist_element( $f['flag_research'], 'choices='.we(':other groups:research groups:both', ':sonstige:Forschungsgruppen:alle' ) ) );
    
        open_tr();
          open_td( '', radiolist_element( $f['flag_publish'], 'choices='.we(':groups not listed on public pages:listed on public pages:both', ":auf {$oUML}ffentliche Webseiten nicht gelistete Gruppen:{$oUML}ffentlich gelistete Gruppen:alle" ) ) );
    close_table();
    if( have_priv( 'groups', 'create' ) ) {
      open_table('css actions' );
        open_caption( '', we('Actions','Aktionen') );
        open_tr( '', inlink( 'group_edit', 'class=big button,text='.we('Create new Group','Neue Gruppe anlegen') ) );
      close_table();
    }
  close_div();
// } else {
//    $f['flags']['value'] = GROUPS_FLAG_INSTITUTE | GROUPS_FLAG_ACTIVE;
// }

groupslist_view( $f['_filters'], 'listoptions=orderby=status' );

?>
