<?php

init_var( 'g', 'global,type=U6,sources=http self,set_scopes=self url' );

if( ! $group = sql_one_group( "groups_id=$g,flags&=".GROUPS_FLAG_LIST, 0 ) ) {
  open_div( 'warn', 'query failed - no such group' );
  return;
}

if( $group['jpegphoto'] ) {
  open_span( 'floatright', html_tag( 'img', array( 'style' => 'max-width:180px;max-height:180px;', 'src' => ( 'data:image/jpeg;base64,' . $group['jpegphoto'] ) ), NULL ) );
}
echo html_tag( 'h1', '', we('Group: ','Gruppe/Bereich: ') . $group['cn_we'] );

open_table('css,td:smallskips;qquads');
  open_tr();
    open_td( 'top', we('Head of group:','Leiter der Gruppe:' ) );
    open_td( 'top', html_alink_person( $group['head_people_id'], 'office' ) );

  open_tr();
    open_td( '', we('Secretary:','Sekretariat:' ) );
    open_td( '', html_alink_person( $group['secretary_people_id'], 'office' ) );

  if( $group['url_we'] ) {
    open_tr();
      open_td( '', we('Web page:','Internetseite:') );
      open_td( '', html_alink( $group['url_we'], array( 'text' => $group['url_we'] ) ) );
  }
close_table();

if( $group['note_we'] ) {
  open_div( 'medskips qquads', $group['note_we'] );
}


echo html_tag( 'h2', '', we('group members:','Gruppenmitglieder:') );

  peoplelist_view( "groups_id=$g", 'columns=group=t=off' );


$themen = sql_positions( "groups_id=$g" );
if( $themen ) {
  echo html_tag( 'h2', '', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );

  positionslist_view( "groups_id=$g", array( 'list_options' => 'columns=group=t=off', 'rows' => $themen ) );
}


?>
