<?php

sql_transaction_boundary('*');


open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('Professors','Professuren am Institut') );
  close_div();
close_div();

open_ccbox( '', we('Professors','Professuren') );

// open_div('column');
open_tag('h2', '', we('Full Professors:','Ordentliche Professuren:') );
$profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_PROFESSOR ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
  }
close_ul('plain');
// close_div();

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_SPECIAL ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('Auxiliary Professors:',"Au{$SZLIG}erplanm{$aUML}{$SZLIG}ige Professuren:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_JOINT ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('Jointly Appointed Professors:',"Gemeinsam Berufene:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_HONORARY ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('Honorary Professors:',"Honorarprofessor_innen:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_EXTERNAL ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('External Professors:',"Professuren mit Zweitmitgliedschaft:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_SENIOR ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('Senior Academic Assistants:',"Privatdozent_innen:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_FORMER ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('Professors who left the institute:',"Professor_innen, die uns in den letzten Jahren verlassen haben:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0' , 'status' => array( PEOPLE_STATUS_EMERITUS, PEOPLE_STATUS_RIP ) ) ) ) ) {
  // open_div('column');
  open_tag('h2', '', we('Retired Professors:',"Professor_innen im Ruhestand:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
  // close_div();
}


close_ccbox();

?>
