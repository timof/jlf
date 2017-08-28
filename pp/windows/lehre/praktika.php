 <?php

sql_transaction_boundary('*');

open_span( 'qquadl bigpadb banner', photo_view( '/pp/fotos/praktikum.jpg', 'Karla Fritze', 'format=url' ) );

echo html_tag( 'h1', '', we('Lab Courses','Praktika am Institut') );

$groups = sql_groups( array( 'status' => GROUPS_STATUS_LABCOURSE, 'flag_publish' ), "orderby=acronym DESC" );
foreach( $groups as $group ) {
  $groups_id = $group['groups_id'];

  open_div( 'medpads' );
    echo group_view( $group, 'hlevel=2' );
    echo html_tag( 'h3', 'noskipt nopadt', we('Members:','Mitglieder:') );

    peoplelist_view( "groups_id=$groups_id", 'columns=groups=t=0,select=1,insert=1' );
  close_div();
}


?>
