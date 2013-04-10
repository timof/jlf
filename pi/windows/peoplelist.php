<?php


init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$fields = array(
  'groups_id' => 'u' // not 'U'!
, 'REGEX' => 'size=30,auto=1'
, 'flag_institute' => 'B,initval=1,auto=1'
, 'flag_virtual' => 'B,initval=2,auto=1'
, 'flag_deleted' => 'B,initval=2,auto=1'
);
if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
  $fields['typeofposition'] = 'allow_null=,default=';
  $fields['teaching_obligation'] = 'u1,min=0,max=8,allow_null=,default=,default_filter=8,normalize=k\\d';
  $fields['teaching_reduction'] = 'u1,min=0,max=8,allow_null=,default=,normalize=k\\d';
}

$f = init_fields( $fields, array( 'tables' => array( 'people', 'affiliations' ) ) );

if( ! have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
  $f['flag_virtual']['value'] = 2;
  $f['flag_deleted']['value'] = 2;
}

echo html_tag( 'h1', '', we('People','Personen') );

open_div('menu');

  open_table('css=1');
    open_caption( 'center th', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Group:','Gruppe:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('institute:','Institut:') );
      open_td( 'oneline', radiolist_element( $f['flag_institute'], 'choices='.we(':non-members:members:all',':nicht-Mitglieder:Mitglieder:alle' ) ) );
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    open_tr();
      open_th( '', we('type:','Art:') );
      open_td( 'oneline', radiolist_element( $f['flag_virtual'], 'choices='.we(':real:virtual:all',':real:virtuell:alle' ) ) );
    open_tr();
      open_th( '', we('status:','Status:') );
      open_td( 'oneline', radiolist_element( $f['flag_deleted'], 'choices='.we(':not deleted:deleted:all',':nicht-gelöscht:gelöscht:alle') ) );
  }
  if( have_minimum_person_priv( PERSON_PRIV_COORDINATOR ) ) {
    open_tr();
      open_th( '', we('position:','Stelle:') );
      open_td( '', filter_typeofposition( $f['typeofposition'] ) );
    open_tr();
      open_th( '', we('teaching obligation:','Lehrverpflichtung:') );
      open_td( 'oneline'
      , filter_int( $f['teaching_obligation'] )
        . hskip('2ex')
        . we('reduction:','Reduktion:')
        . filter_int( $f['teaching_reduction'] )
      );
  }
    open_tr();
      open_th( '', we('search:','suche:') );
      open_td( '', filter_reset_button( $f['REGEX'] ) . '/'.string_element( $f['REGEX'] ).'/ ' );
  close_table();

  if( have_priv( 'person', 'create' ) ) {
    open_div( 'center th', we('Actions','Aktionen') );
    open_div( 'center', inlink( 'person_edit', 'class=bigbutton,text='.we('New Person','Neue Person') ) );
  }

close_div();

peoplelist_view( $f['_filters'], "regex_filter=1,allow_download=1,format=$global_format" );

?>
