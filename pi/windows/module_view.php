<?php // /pi/windows/module_view.php

sql_transaction_boundary('*');

init_var( 'modules_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $modules_id ) {
  open_div( 'warn', we('no module selected','kein Modul gewählt') );
  return;
}

$module = sql_one_module( $modules_id );



$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'modules', $modules_id ) ) : '' );
open_fieldset( 'small_form old', we('Module:','Modul:' ) . $v );

  open_table('css small_form hfill td:smallskips;qquads');
    open_tr( 'bigskips' );
      open_td( '', we('Module:','Modul:') );
      open_td( 'bold', $module['tag'] );

    open_tr( 'medskip' );
      open_td( '', we('title:','Titel:') );
      open_td( 'bold', $module['cn'] );

    open_tr( 'medskip' );
      open_td( '', we('valid from:', "g{$uUML}ltig ab:") );
      open_td( '', $module['year_valid_from'] );

    open_tr( 'medskip' );
      open_td( '', we('responsible person:','verantwortliche Person:') );
      open_td( '', alink_person_view( $module['contact_people_id'], 'office' ) );

    open_tr( 'medskip' );
      open_td( '', we('programmes:','Studiengang:') );
      open_td( '', programme_cn_view( $module['programme_flags'] ) );

    open_tr();
      open_td();
      open_td( 'colspan=2', $module['note'] );

  close_table();

  open_div('right');
    if( $logged_in ) {
      echo inlink( 'module_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'modules_id' => $modules_id
      , 'inactive' => priv_problems( 'modules', 'edit', $modules_id )
      ) );
    }
  close_div();

close_fieldset();

?>
