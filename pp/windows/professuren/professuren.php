<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Professors','Professuren') );


open_tag('h2', '', we('full Professors:','ordentliche Professuren:') );
$profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_PROFESSOR ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
  }
close_ul('plain');

open_tag('h2', '', we('associate Professors:',"au{$SZLIG}erplanm{$aUML}{$SZLIG}ige Professuren:") );
$profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_SPECIAL ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
  }
close_ul('plain');

open_tag('h2', '', we('Professors by joinr appointment:',"gemeinsam berufene Professuren:") );
$profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_JOINT ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
  }
close_ul('plain');

open_tag('h2', '', we('external Professors:',"externe Professuren:") );
$profs = sql_people( array( 'flag_publish', 'flag_deleted=0', 'flag_virtual=0', 'status' => PEOPLE_STATUS_EXTERNAL ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_person_view( $p['people_id'], 'showgroup=1' ) );
  }
close_ul('plain');

// $profs = sql_groups( array( 'flag_publish, ' &=' => GROUPS_FLAG_INSTITUTE, 'status' => GROUPS_STATUS_SPECIAL ) );
// if( $profs ) {
//   open_tag('h2', '', we('Professors by special appointment:','außerplanmäßige Professuren:') );
//   open_ul('plain');
//     foreach( $profs as $p ) {
//       open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
//     }
//   close_ul('plain');
// }
// 
// $profs = sql_groups( array( 'flags &=' => GROUPS_FLAG_INSTITUTE, 'status' => GROUPS_STATUS_JOINT ) );
// if( $profs ) {
//   open_tag('h2', '', we('Professors by joint appointment:','gemeinsam berufene Professuren:') );
//   open_ul('plain');
//     foreach( $profs as $p ) {
//       open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
//     }
//   close_ul('plain');
// }
// 
// $profs = sql_groups( array( 'flags &=' => GROUPS_FLAG_INSTITUTE, 'status' => GROUPS_STATUS_EXTERNAL ) );
// if( $profs ) {
//   open_tag('h2', '', we('external Professors:','externe Professuren:') );
//   open_ul('plain');
//     foreach( $profs as $p ) {
//       open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
//     }
//   close_ul('plain');
// }

?>
