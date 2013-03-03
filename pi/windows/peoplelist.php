<?php

echo html_tag( 'h1', '', we('People','Personen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'groups_id'
  , 'REGEX' => 'size=40,auto=1'
  , 'flag_institute' => 'B,initval=1,auto=1'
  , 'flag_virtual' => 'B,initval=2,auto=1'
  , 'flag_deleted' => 'B,initval=2,auto=1'
  )
, '' );

if( ! have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
  $f['flag_virtual']['value'] = 2;
  $f['flag_deleted']['value'] = 2;
}

if( $global_format === 'html' ) {
  open_table('menu');
    open_tr();
      open_th( 'center,colspan=2', html_span( 'floatright', filter_reset_button( $f ) ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('institute:','Institut:') );
      open_td( 'oneline', radiolist_element( $f['flag_institute'], 'choices='.we(':non-members:members:all',':nicht-Mitglieder:Mitglieder:alle' ) ) );
if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    open_tr();
      open_th( '', 'type:','Art:' );
      open_td( 'oneline', radiolist_element( $f['flag_virtual'], 'choices='.we(':real:virtual:all',':real:virtuell:alle' ) ) );
    open_tr();
      open_th( '', 'status:','Status:' );
      open_td( 'oneline', radiolist_element( $f['flag_deleted'], 'choices='.we(':not deleted:deleted:all',':nicht-gelöscht:gelöscht:alle') ) );
}
    open_tr();
      open_th( '', we('search:','suche:') );
      open_td( '', '/'.string_element( $f['REGEX'] ).'/ ' . filter_reset_button( $f['REGEX'] ) );
    if( have_priv( 'person', 'create' ) ) {
      open_tr();
        open_th( 'center,colspan=2', we('Actions','Aktionen') );
      open_tr();
        open_td( 'center,colspan=2', inlink( 'person_edit', 'class=bigbutton,text='.we('New Person','Neue Person') ) );
    }
  close_table();
  
  bigskip();
}

peoplelist_view( $f['_filters'], "download=downloadPeoplelist,format=$global_format" );

?>
