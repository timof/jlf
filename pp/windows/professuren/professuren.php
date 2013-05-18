<?php

init_var( 'function', 'global,type=W,sources=self http,default=full,set_scopes=self url' );
need( isset( $boards['professors'][ $function ] ), "no such function: $function" );

$profs = sql_offices( "board=professors,function=$function", array( 'orderby' => 'sn,gn' ) );

// echo html_tag( 'h1', '', $boards['professors'][ $function ]['function'] );

$solidtop = '';
if( $function == 'full' ) {
  open_div( 'smallskipb', inlink( 'gemberufene', 'text='.$boards['professors']['joint']['function'] ) );
  open_div( 'bigskipb', inlink( 'aplprofs', 'text='.$boards['professors']['special']['function'] ) );
  $solidtop = 'solidtop';
}

// open_table( 'id=professuren' );
$n = 1;
//    if( $n % 2 ) {
//      open_tr('medskips');
//    }
foreach( $profs as $prof ) {
  open_div( "medskipskips $solidtop" );

    open_div('smallskips', html_alink_person( $prof['people_id'], 'office,class=href inlink bold' ) );

    open_div( 'smallskipb qquadl', we('secretary: ','Sekretariat: ') . html_alink_person( $prof['secretary_people_id'], 'office' ) );

    if( $prof['groups_id'] ) {
      open_div( 'medskipb qquadl', we('group: ','Gruppe: ') . html_alink_group( $prof['groups_id'] ) );
    } else if( $prof['url'] ) {
      open_div( 'medskipb qquadl', we('home page: ','Webseite: ') . html_tag( 'a', array( 'class=href outlink', 'href' => $prof['url'] ) ) );
    }

  close_div();
  $solidtop = 'solidtop';
  $n++;
}


?>
