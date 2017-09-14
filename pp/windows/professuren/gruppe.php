<?php

sql_transaction_boundary('*');

init_var( 'groups_id', 'global,type=U6,sources=http self,set_scopes=self url' );

if( ! ( $group = sql_one_group( "groups_id=$groups_id,flag_publish", 0 ) ) ) {
  open_div( 'warn', we('query failed - no such group','Abfrage fehlgeschlagen - Gruppe nicht gefunden') );
  return;
}

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('Institute / Group','Institut / Arbeitsgruppe') );
  close_div();
close_div();

open_ccbox('group', array( 'title' => $group['cn'], 'subtitle' => $group['h2'] ) );

  $s = $group['note'];

  if( $group['jpegphoto'] ) {
    $p = sql_person( $group['jpegphotorights_people_id'], 0 );
    if( $p ) {
      $img = html_img( $group['jpegphoto'], '', credits( $p['cn_notitle'] ), 'format=jpeg' );
      $s = html_div( 'illu', $img ) . $s;
    }
  }

  echo groupcontact_view( $group ) . html_div( 'textaroundphoto medskips', $s );

  peoplelist_view( "groups_id=$groups_id", array( 'columns' => 'groups=t=0', 'select' => 1, 'insert' => 1, 'heading' => we('group members:','Gruppenmitglieder:') ) );


//   if( sql_positions( "groups_id=$groups_id", 'single_field=COUNT' ) ) { // just count - would be tricky to get orderby right here!
//     echo html_tag( 'h2', '', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );
//   
//     init_var( 'positions_id', 'global=1,set_scopes=self,sources=http persistent' );
//     positionslist_view( "groups_id=$groups_id", array( 'columns' => 'groups=t=0', 'insert' => 1, 'select' => 'positions_id' ) );
//   }


close_ccbox();

?>
