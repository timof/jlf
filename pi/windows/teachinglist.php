<?php

echo html_tag( 'h1', '', we('Teaching','Lehre') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
  'term'
, 'year'
, 'teacher_people_id' => 'basename=people_id'
, 'teacher_groups_id' => 'basename=groups_id'
, 'submitter_people_id' => 'type=u'
) );

open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Term:','Semester:') );
    open_td();
      filter_term( $f['term'] );
if( have_priv( 'teaching', 'list' ) ) {
  open_tr();
    open_th( '', we('Group:','Gruppe:') );
    open_td();
      filter_group( $f['teacher_groups_id'] );
  open_tr();
    open_th( '', we('Submitter:','Erfasser:') );
    open_td();
      filter_person( $f['submitter_people_id'] );
  $filters = $f['_filters'];
}
close_table();

$filters = $f['_filters'];
if( ! have_priv( 'teaching', 'list' ) ) {
  $filters += array( 'submitter_people_id' => $login_people_id );
}

bigskip();

handle_action( array( 'update', 'deleteTeaching' ) );
switch( $action ) {
  case 'deleteTeaching':
    need( $message > 0, we('no entry selected','kein Eintrag ausgewaehlt') );
    need_priv( 'teaching', 'delete', $message );
    sql_delete_teaching( $message );
    break;
}

medskip();

teachinglist_view( $filters, '' );

if( have_priv( 'teaching', 'edit' ) ) {
  open_div( 'right', inlink( 'teaching_edit', array(
    'class' => 'edit', 'text' => we('edit...','bearbeiten...' )
  ) ) );
}

?>
