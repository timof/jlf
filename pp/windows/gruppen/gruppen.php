<?php

sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Research Groups','Arbeitsgruppen') );


open_tag('h2', '', we('Professors:','Professuren:') );
$profs = sql_groups( array( 'status' => GROUPS_STATUS_PROFESSOR ) );
open_ul('plain');
  foreach( $profs as $p ) {
    open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
  }
close_ul('plain');

$profs = sql_groups( array( 'flags &=' => GROUPS_FLAG_INSTITUTE, 'status' => GROUPS_STATUS_SPECIAL ) );
if( $profs ) {
  open_tag('h2', '', we('Professors by special appointment:','außerplanmäßige Professuren:') );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
    }
  close_ul('plain');
}

$profs = sql_groups( array( 'flags &=' => GROUPS_FLAG_INSTITUTE, 'status' => GROUPS_STATUS_JOINT ) );
if( $profs ) {
  open_tag('h2', '', we('Professors by joint appointment:','gemeinsam berufene Professuren:') );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
    }
  close_ul('plain');
}

$profs = sql_groups( array( 'flags &=' => GROUPS_FLAG_INSTITUTE, 'status' => GROUPS_STATUS_EXTERNAL ) );
if( $profs ) {
  open_tag('h2', '', we('external Professors:','externe Professuren:') );
  open_ul('plain');
    foreach( $profs as $p ) {
      open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
    }
  close_ul('plain');
}

?>
