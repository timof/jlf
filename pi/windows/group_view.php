<?php // /pi/windows/group_view.php

sql_transaction_boundary('*');

init_var( 'groups_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $groups_id ) {
  open_div( 'warn', we('no group selected','keine Gruppe gewaehlt') );
  return;
}

$group = sql_one_group( $groups_id );

$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'groups', $groups_id ) ) : '' );
open_fieldset( 'old', we('Group','Gruppe') . $v );

    // open_tr( 'medskip' );
    //   open_td( '', we('Short Name:','Kurzname:') );
    //   open_td( '', $group['acronym'] );

  open_fieldset('table',we('Properties','Stammdaten') );
    open_tr( 'smallskip' );
      open_td( '', we('Group:','Gruppe:') );
      open_td( '', $group['cn'] );

    if( $group['head_people_id'] ) {
      open_tr('medskip');
        open_td( '', we('head of group:','Leiter der Gruppe:' ) );
        open_td( '', alink_person_view( $group['head_people_id'] ) );
    }

    if( $group['secretary_people_id'] ) {
      open_tr('medskip');
        open_td( '', we('Secretary:','Sekretariat:' ) );
        open_td( '', alink_person_view( $group['secretary_people_id'] ) );
    }

    open_tr( 'medskip' );
      open_td( 'top', we('Attributes:','Attribute:') );
      open_td( '' );
        open_ul();
          open_li( '', $group['flag_institute']
            ? we('group is member of institute','Gruppe ist Institutsmitglied')
            : we('external group','externe Gruppe')
          );
          open_li( '', $group['flag_publish']
            ? we('group to be listed on public site','Gruppe soll auf öffentlicher Seite angezeigt werden')
            : we('group will not be listed on public site','Gruppe wird auf öffentlicher Seite nicht angezeigt')
          );
          open_li( '', $group['flag_research']
            ? we('group is listed as research group',"Gruppe wird auf den {$oUML}ffentlichen Webseiten als Forschungsgruppe gelisted")
            : we('not a research group',"Gruppe wird nicht als Forschungsgruppe gelisted")
          );
        close_ul();

    if( $group['url'] ) {
      open_tr( 'medskip' );
        open_td( '', we('Internet page:','Internetseite:') );
        open_td( '', html_alink( $group['url'], array( 'text' => $group['url'] ) ) );
    }

    if( $group['jpegphoto'] ) {
      open_tr( 'medskip' );
        open_td( '', we('photo (for public web page):','Foto (für öffentliche Webseite):') );
        open_td( 'oneline',
          html_tag( 'img', array(
              'height' => '100'
            , 'src' => 'data:image/jpeg;base64,' . $group['jpegphoto']
            ), NULL
          )
        );
    }

    if( $group['note'] ) {
      open_tr( 'medskip' );
        open_td();
        open_td( '', $group['note'] );
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

if( have_priv('*','*') ) {
  open_fieldset( '', we('open positions / topics for theses','Offene Stellen / Themen für Bachelor/Master/...-Arbeiten:') );
    positionslist_view( "groups_id=$groups_id" );
    if( have_priv( 'positions', 'create' ) ) {
      open_div( 'smallskips', action_link( 'class=button edit,script=position_edit,text='.we('add new position/topic','Neue Stelle/Thema eintragen'), "groups_id=$groups_id" ) );
    }
  close_fieldset();
}

  open_fieldset( '', we('laboratories: ','Labore: ') );
    roomslist_view( "groups_id=$groups_id,flag_lab", array( 'columns' => array( 'groups_id' => 't=off' ) ) );
    if( have_priv( 'rooms', 'create' ) ) {
      open_div( 'smallskips', action_link( 'class=button edit,script=room_edit,text='.we('add lab','neues Labor erfassen'), "groups_id=$groups_id" ) );
    }
  close_fieldset();


close_fieldset();

?>
