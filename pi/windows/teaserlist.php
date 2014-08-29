<?php // /pi/windows/teaserlist.php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', 'Teaser' );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
  'SEARCH' => 'size=40,auto=1,relation=~='
) , '' );

open_div('menubox');
  open_table('css filters');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
//     open_tr();
//       open_th( '', we('Group:','Gruppe:') );
//       open_td( '', filter_group( $f['groups_id'] ) );
//     open_tr();
//       open_th( 'oneline', we('Programme / Degree:','Studiengang / Abschluss:' ) );
//       open_td( '', filter_programme( $f['programme_flags'] ) );
    open_tr();
      open_th( '', we('search:','Suche:') );
      open_td( '', '/'.string_element( $f['SEARCH'] ).'/ ' . filter_reset_button( $f['SEARCH'] ) );
  close_table();
  if( have_priv( 'teaser', 'create' ) ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      open_tr( '', inlink( 'teaser_edit', 'class=big button,text=New Teaser' ) );
    close_table();
  }
close_div();

teaserlist_view( $f['_filters'], '' );

?>
