<?php

init_var( 'groups_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $groups_id ) {
  open_div( 'warn', we('no group selected','keine Gruppe gewaehlt') );
  return;
}

$group = sql_one_group( $groups_id );
open_fieldset( 'old', we('Group','Gruppe') );

    // open_tr( 'medskip' );
    //   open_td( '', we('Short Name:','Kurzname:') );
    //   open_td( '', $group['acronym'] );

  open_fieldset('table',we('Properties','Stammdaten') );
    open_tr( 'smallskip' );
      open_td( '', we('Group:','Gruppe:') );
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

    open_tr( 'medskip' );
      open_td( 'top', we('Attributes:','Attribute:') );
      open_td( '' );
        open_ul();
          open_li( '', $group['flags'] & GROUPS_FLAG_INSTITUTE
            ? we('group is member of institute','Gruppe ist Institutsmitglied')
            : we('external group','externe Gruppe')
          );
          open_li( '', $group['flags'] & GROUPS_FLAG_ACTIVE
            ? we('group is still active','Gruppe ist noch aktiv')
            : we('group is inactive','inaktive Gruppe')
          );
          open_li( '', $group['flags'] & GROUPS_FLAG_LIST
            ? we('group to be listed on public site','Gruppe soll auf öffentlicher Seite angezeigt werden')
            : we('group will not be listed on public site','Gruppe wird auf öffentlicher Seite nicht angezeigt')
          );
        close_ul();

    if( $group['url_we'] ) {
      open_tr( 'medskip' );
        open_td( '', we('Internet page:','Internetseite:') );
        open_td( '', html_alink( $group['url_we'], array( 'text' => $group['url_we'] ) ) );
    }

    if( $group['note_we'] ) {
      open_tr( 'medskip' );
        open_td();
        open_td( '', $group['note_we'] );
    }

    if( have_priv( 'groups', 'edit', $groups_id ) ) {
      open_tr();
        open_td( '', inlink( 'group_edit', array(
          'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
        , 'groups_id' => $groups_id
        ) ) );
    }
  close_fieldset();

  open_fieldset( '', we('group members:','Gruppenmitglieder:') );
    peoplelist_view( "groups_id=$groups_id" );
    if( have_priv( 'person', 'create' ) ) {
      open_div( 'smallskips', action_link( 'class=button edit,script=person_edit,text='.we('add new member','Neues Mitglied eintragen'), "aff0_groups_id=$groups_id" ) );
    }
  close_fieldset();

  open_fieldset( '', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );
    positionslist_view( "groups_id=$groups_id" );
    if( have_priv( 'positions', 'create' ) ) {
      open_div( 'smallskips', action_link( 'class=button edit,script=position_edit,text='.we('add new position/topic','Neue Stelle/Thema eintragen'), "groups_id=$groups_id" ) );
    }
  close_fieldset();

close_fieldset();

?>
