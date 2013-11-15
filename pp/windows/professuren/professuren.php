<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Professors','Professuren') );


open_tag('h2', '', we('Full Professors:','Ordentliche Professuren:') );
$profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_PROFESSOR ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
  }
close_ul('plain');

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_SPECIAL ) ) ) ) {
  open_tag('h2', '', we('Auxiliary Professors:',"Au{$SZLIG}erplanm{$aUML}{$SZLIG}ige Professuren:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_JOINT ) ) ) ) {
  open_tag('h2', '', we('Jointly Appointed Professors:',"Gemeinsam berufene:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_HONORARY ) ) ) ) {
  open_tag('h2', '', we('Honorary Professors:',"Honorarprofessoren:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_EXTERNAL ) ) ) ) {
  open_tag('h2', '', we('External Professors:',"Externe Professuren:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
}

if( ( $profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_SENIOR ) ) ) ) {
  open_tag('h2', '', we('Senior Academic Assistants:',"Privatdozenten:") );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
    }
  close_ul('plain');
}


?>
