<?php

sql_transaction_boundary('*');

init_var( 'groups_id', 'global,type=U6,sources=http self,set_scopes=self url' );

if( ! ( $group = sql_one_group( "groups_id=$groups_id,flag_publish", 0 ) ) ) {
  open_div( 'warn', we('query failed - no such group','Abfrage fehlgeschlagen - Gruppe nicht gefunden') );
  return;
}

echo group_view( $group );


echo html_tag( 'h2', '', we('group members:','Gruppenmitglieder:') );

  peoplelist_view( "groups_id=$groups_id", 'columns=groups=t=0' );


if( sql_positions( "groups_id=$groups_id", 'single_field=COUNT' ) ) { // just count - would be tricky to get orderby right here!
  echo html_tag( 'h2', '', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );

  init_var( 'positions_id', 'global=1,set_scopes=self,sources=http persistent' );
  positionslist_view( "groups_id=$groups_id", array( 'columns' => 'groups=t=0', 'insert' => 1, 'select' => 'positions_id' ) );
}


?>
