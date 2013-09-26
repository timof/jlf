<?php

sql_transaction_boundary('*');

init_var( 'groups_id', 'global,type=U6,sources=http self,set_scopes=self url' );

if( ! $group = sql_one_group( "groups_id=$groups_id,flags&=".GROUPS_FLAG_LIST, 0 ) ) {
  open_div( 'warn', 'query failed - no such group' );
  return;
}

echo group_view( $group );

// if( $group['jpegphoto'] ) {
//   open_span( 'floatright', html_tag( 'img', array( 'style' => 'max-width:180px;max-height:180px;', 'src' => ( 'data:image/jpeg;base64,' . $group['jpegphoto'] ) ), NULL ) );
// }
// echo html_tag( 'h1', '', we('Group: ','Gruppe/Bereich: ') . $group['cn'] );
// 
// open_table('css,td:smallskips;qquads');
//   open_tr();
//     open_td( 'top', we('Head of group:','Leiter der Gruppe:' ) );
//     open_td( 'top', alink_person_view( $group['head_people_id'], 'office' ) );
// 
//   open_tr();
//     open_td( '', we('Secretary:','Sekretariat:' ) );
//     open_td( '', alink_person_view( $group['secretary_people_id'], 'office' ) );
// 
//   if( $group['url'] ) {
//     open_tr();
//       open_td( '', we('Web page:','Internetseite:') );
//       open_td( '', html_alink( $group['url'], array( 'text' => $group['url'] ) ) );
//   }
// close_table();
// 
// if( $group['note'] ) {
//   open_div( 'medskips qquads', $group['note'] );
// }


echo html_tag( 'h2', '', we('group members:','Gruppenmitglieder:') );

  peoplelist_view( "groups_id=$groups_id", 'columns=group=t=off' );


$themen = sql_positions( "groups_id=$groups_id" );
if( $themen ) {
  echo html_tag( 'h2', '', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );

  positionslist_view( "groups_id=$groups_id", array( 'list_options' => 'columns=group=t=off', 'rows' => $themen ) );
}


?>
