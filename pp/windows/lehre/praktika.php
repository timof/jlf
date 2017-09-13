 <?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('lehre');
    echo html_tag( 'h1', '', we('Studies / Labcourses','Lehre am Institut / Praktika') );
  close_div();
close_div();


$group = sql_one_group( array( 'status' => GROUPS_STATUS_LABCOURSE, 'acronym' => 'gp', 'flag_publish' ), 0 );
if( $group ) {
  $groups_id = $group['groups_id'];
  open_ccbox( 'group', we('Basic Lab Course','Grundpraktikum') );
    open_div( 'illu', image('gp') );

    echo html_div( 'medskips', $group['note'] );
    echo groupcontact_view( $group );

    peoplelist_view( "groups_id=$groups_id"
    , array( 
        'columns' => 'groups=t=0'
      , 'select' => 1
      , 'insert' => 1
      , 'heading' => html_tag( 'h3', '', we('Staff:','Mitarbeiter_innen:') )
      )
    );
  
  close_ccbox();
}

$group = sql_one_group( array( 'status' => GROUPS_STATUS_LABCOURSE, 'acronym' => 'fp', 'flag_publish' ), 0 );
if( $group ) {
  $groups_id = $group['groups_id'];
  open_ccbox( 'group', we('Advanced Lab Course','Fortgeschrittenenpraktikum') );
    open_div( 'illu', image('fp') );

    echo html_div( 'medskips', $group['note'] );
    echo groupcontact_view( $group );

    peoplelist_view( "groups_id=$groups_id"
    , array( 
        'columns' => 'groups=t=0'
      , 'select' => 1
      , 'insert' => 1
      , 'heading' => html_tag( 'h3', '', we('Staff:','Mitarbeiter_innen:') )
      )
    );

  close_ccbox();
}

?>
