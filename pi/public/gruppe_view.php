<?php

init_var( 'groups_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $groups_id ) {
  open_div( 'warn', we('no group selected','keine Gruppe gewaehlt') );
  return;
}

$group = sql_one_group( $groups_id );
open_fieldset( 'small_form old', we('Group','Gruppe') );

  open_table('small_form hfill');
    // open_tr( 'medskip' );
    //   open_td( '', we('Short Name:','Kurzname:') );
    //   open_td( '', $group['kurzname'] );

    open_tr( 'smallskip' );
      open_td( array( 'label' => $group['cn'] ), we('Group:','Gruppe:') );
      open_td( '', $group['cn_we'] );

    if( $group['head_people_id'] ) {
      open_tr('medskip');
        open_td( '', we('Group leader:','Leiter der Gruppe:' ) );
        open_td( '', html_alink_person( $group['head_people_id'] ) );
    }

    if( $group['secretary_people_id'] ) {
      open_tr('medskip');
        open_td( '', we('Secretary:','Sekretariat:' ) );
        open_td( '', html_alink_person( $group['secretary_people_id'] ) );
    }

    if( $group['url_we'] ) {
      open_tr( 'medskip' );
        open_td( '', we('Internet page:','Internetseite:') );
        open_td( '', html_alink( $group['url_we'], array( 'text' => $group['url_we'] ) ) );
    }

    if( $group['note_we'] ) {
      open_tr( 'medskip' );
        open_td( 'colspan=2', $group['note_we'] );
    }

    if( have_priv( 'group', 'edit', $groups_id ) ) {
      open_tr();
        open_td( 'colspan=2', inlink( 'gruppe_edit', array(
          'class' => 'edit', 'text' => we('edit...','bearbeiten...' )
        , 'groups_id' => $groups_id
        ) ) );
    }
  close_table();

  medskip();
  echo html_tag( 'h4', '', we('group members:','Gruppenmitglieder:') );
  peoplelist_view( "groups_id=$groups_id" );
  bigskip();

  echo html_tag( 'h4', '', we('open topics for theses','Offene Themen fuer Bachelor/Master/...-Arbeiten:') );
  bamathemenlist_view( "groups_id=$groups_id" );

close_fieldset();

?>
