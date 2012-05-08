<?php

init_var( 'surveys_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $surveys_id ) {
  open_div( 'warn', we('no survey selected','keine Umfrage gewählt') );
  return;
}

$survey = sql_one_survey( $surveys_id );

open_fieldset( 'small_form old', we( 'Survey', 'Umfrage' ) );

  open_table('small_form hfill');
    open_tr( 'medskip' );
      open_td( 'colspan=2', $survey['cn'] );


    open_tr();
    if( have_priv( 'survey', 'edit', $surveys_id ) ) {
        open_td( 'colspan=2', inlink( 'survey_edit', array(
          'class' => 'edit', 'text' => we('edit...','bearbeiten...' )
        , 'surveys_id' => $surveys_id
        ) ) );
    }
  close_table();

close_fieldset();

?>
