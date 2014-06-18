<?php // /pi/windows/peoplelist.php

sql_transaction_boundary( array( 'applicants' ) );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );


echo html_tag( 'h1', '', we('Prospective Students','Studieninteressierte') );

$filters = array();

$filters = restrict_view_filters( $filters, 'applicants' );
$opts = array();

$opts = parameters_explode( $opts, 'set=filename='.we('prospective_students','studieninteressierte') );
$list_options = handle_list_options( $opts, 'positions', array(
    'id' => 's=applicants_id,t=1'
  , 'nr' => 't=1'
  , 'ctime' => 's,t'
  , 'sn' => 's,t'
  , 'gn' => 's,t'
  , 'street' => 's,t'
  , 'city' => 's,t'
  , 'country' => 's,t'
  , 'mail' => 's,t'
  , 'language' => 's,t'
  , 'programme' => 's,t'
  , 'questions' => 's,t'
) );

if( ! ( $applicants = sql_applicants( $filters, array( 'orderby' => $list_options['orderby_sql'] ) ) ) ) {
  open_div( '', we('no such entries', "Keine Eintr{$aUML}ge" ) );
  return;
}
$count = count( $applicants );
$limits = handle_list_limits( $list_options, $count );
$list_options['limits'] = & $limits;

// $selected_positions_id = adefault( $GLOBALS, $opts['select'], 0 );

open_list( $list_options );
  open_list_row('header');
  open_list_cell( 'nr' );
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    open_list_cell( 'id' );
  }
  open_list_cell( 'ctime', 'ctime' );
  open_list_cell( 'language', we('language','Sprache') );
  open_list_cell( 'sn', we('surename','Nachname') );
  open_list_cell( 'gn', we('given name','Vorname') );
  open_list_cell( 'street', we('street',"Stra{$SZLIG}e") );
  open_list_cell( 'city', we('city','Ort') );
  open_list_cell( 'country', we('country','Land') );
  open_list_cell( 'mail', 'email' );
  open_list_cell( 'programme', we('programme','Studiengang') );
  open_list_cell( 'questions', we('questions','Fragen') );
  foreach( $applicants as $a ) {
    $id = $a['applicants_id'];
    open_list_row();
      open_list_cell( 'nr', inlink( 'applicant_view', "applicants_id=$id,text={$a['nr']}", 'right' ) );
      if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
        open_list_cell( 'id', any_link( 'applicants', $id, "text=$id" ), 'number' );
      }
      open_list_cell( 'ctime', $a['ctime'] );
      open_list_cell( 'language', $a['language'] );
      open_list_cell( 'sn', inlink( 'applicant_view', array( 'applicants_id' => $id, 'text' => $a['sn'] ) ) );
      open_list_cell( 'gn', $a['gn'] );
      open_list_cell( 'street', $a['street'] );
      open_list_cell( 'city', $a['city'] );
      open_list_cell( 'country', $a['country'] );
      open_list_cell( 'mail', $a['mail'] );
      open_list_cell( 'programme', $programme_text[ $a['programme'] ] );
      $t = $a['questions'];
      if( strlen( $t ) > 20 ) {
        $t = substr( $t, 0, 18 ) .  '...';
      }
      open_list_cell( 'questions', $t );
  }

close_list();

?>
