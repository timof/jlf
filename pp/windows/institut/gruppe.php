<?php

init_var( 'g', 'global,type=U,max=999999,set_scopes=url' );

if( ! $group = sql_one_group( "groups_id=$g,flags&=".GROUPS_FLAG_LIST, 0 ) ) {
  open_div( 'warn', 'query failed - no such group' );
  return;
}

open_table('css=1,td:smallskips;qquads');
    // open_tr( 'medskip' );
    //   open_td( '', we('Short Name:','Kurzname:') );
    //   open_td( '', $group['acronym'] );

  open_tr();
    open_td( 'larger', we('Group:','Gruppe/Bereich:') );
    open_td( 'bold larger', $group['cn_we'] );

  open_tr();
    open_td( '', we('Head of group:','Leiter der Gruppe:' ) );
    open_td( '', html_alink_person( $group['head_people_id'] ), 'office' );

  open_tr();
    open_td( '', we('Secretary:','Sekretariat:' ) );
    open_td( '', html_alink_person( $group['secretary_people_id'] ), 'office' );

  if( $group['url_we'] ) {
    open_tr();
      open_td( '', we('web page:','Internetseite:') );
      open_td( '', html_alink( $group['url_we'], array( 'text' => $group['url_we'] ) ) );
  }

  if( $group['note_we'] ) {
    open_tr();
      open_td( 'colspan=2,padding:0ex 2em 0ex 2em', $group['note_we'] );
  }

close_table();

echo html_tag( 'h4', 'bigskip', we('group members:','Gruppenmitglieder:') );

  peoplelist_view( "groups_id=$g", 'columns=group=t=off' );


echo html_tag( 'h4', 'bigskip', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );

  positionslist_view( "groups_id=$g", 'columns=group=t=off' );


?>
