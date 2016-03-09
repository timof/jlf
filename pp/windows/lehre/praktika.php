 <?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Lab Courses:','Praktika am Institut:') );

foreach( array( 'gp', 'fp' ) as $acronym ) {
  if( ( $group = sql_one_group( "acronym=$acronym,flag_publish", 0 ) ) ) {
    $groups_id = $group['groups_id'];

    echo group_view( $group );
    echo html_tag( 'h3', '', we('Members:','Mitglieder:') );

    peoplelist_view( "groups_id=$groups_id", 'columns=groups=t=0,select=1,insert=1' );
  }
}



?>
