<?php

need( false );

echo html_tag( 'h1', '', we('Surveys','Umfragen' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array() , '' );

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  if( have_priv( 'survey', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', inlink ( 'survey_edit', 'class=bigbutton,text='.we('New Survey','Neue Umfrage' ) ) );
  }
close_table();

bigskip();


handle_action( array( 'update', 'deleteSurvey' ) );
switch( $action ) {
  case 'deleteSurvey':
    need( $message > 0, we('no survey selected','keine Umfrage ausgewaehlt' ) );
    sql_delete_surveys( $message );
    break;
}

medskip();

surveyslist_view( $f['_filters'], '' );

?>
