 <?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    open_tag( 'img', array( 'src' => '/pp/fotos/lehre.jpg', 'alt' => 'Vorlesung im großsen Hörsaal' ), NULL );
    open_div( 'rights', we('Image:','Bild:') . ' Karla Fritze' );
    echo html_tag( 'h1', '', we('Studies / Labcourses','Lehre am Institut / Praktika') );
  close_div();
close_div();


$group = sql_one_group( array( 'status' => GROUPS_STATUS_LABCOURSE, 'acronym' => 'gp', 'flag_publish' ), 0 );
if( $group ) {
  $groups_id = $group['groups_id'];
  open_ccbox( 'group', we('Basic Lab Course','Grundpraktikum') );
    open_div( 'illu', photo_view( '/pp/fotos/gp.jpg', 'Karla Fritze', 'class=teaser,format=url' ) );
    
    echo groupcontact_view( $group );

    echo html_tag( 'h3', '', we('Members:','Mitglieder:') );
    peoplelist_view( "groups_id=$groups_id", 'columns=groups=t=0,select=1,insert=1' );

    open_div('clear','');
  close_ccbox();
}

$group = sql_one_group( array( 'status' => GROUPS_STATUS_LABCOURSE, 'acronym' => 'fp', 'flag_publish' ), 0 );
if( $group ) {
  $groups_id = $group['groups_id'];
  open_ccbox( 'group', we('Advanced Lab Course','Fortgeschrittenenpraktikum') );
    open_div( 'illu', photo_view( '/pp/fotos/master.jpg', 'Karla Fritze', 'class=teaser,format=url' ) );

    echo groupcontact_view( $group );

    echo html_tag( 'h3', '', we('Members:','Mitglieder:') );
    peoplelist_view( "groups_id=$groups_id", 'columns=groups=t=0,select=1,insert=1' );

    open_div('clear','');
  close_ccbox();
}

?>
