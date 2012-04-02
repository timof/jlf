<?php


// functions for drop-down selectors; we usually provide a triple of functions:
// - choices_X( $filters = array() )
//     returns an array of <id> => <option> pairs matching $filters
// - selector_X( $field, $opts )
//     create drop-down selection gadget
//     $opts may contain
//      'filters': filter (array or string) to narrow selection
//      'more_choices': array of 'value' => 'text' pairs to also offer for selection
// - filter_X( $field, $opts = array() )
//     create drop-down selection gadget for filtering; $opts may contain
//       'filters': to narrow selection
//       'choice_0': extra choice with value '0' (default: '(all)'; set to NULL to offer no choice_0)

function choices_people( $filters = array() ) {
  $choices = array();
  foreach( sql_people( $filters ) as $p ) {
    $choices[ $p['people_id'] ] = $p['cn'];
  }
  $choices[''] = $choices ? we(' - select person - ', ' - Person w'.H_AMP.'auml;hlen - ') : we('(no people available)', '(keine Personen vorhanden)');
  return $choices;
}

function selector_people( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'people_id' );
  $opts = parameters_explode( $opts );
  $filters = parameters_explode( adefault( $opts, 'filters', ''), array( 'keep' => 'groups_id' ) );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_people( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_person( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ',' (alle) ') );
  selector_people( $field, $opts );
}


function choices_groups( $filters = array() ) {
  $choices = array();
  foreach( sql_groups( $filters, 'kurzname' ) as $g ) {
    $choices[ $g['groups_id'] ] = $g['kurzname'];
  }
  $choices[''] = $choices ? we(' - select group - ', ' - Gruppe w'.H_AMP.'auml;hlen - ') : we('(no groups available)', '(keine Gruppen vorhanden)');
  return $choices;
}

function selector_groups( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'groups_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_groups( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_group( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_groups( $field, $opts );
}


function choices_abschluss() {
  $choices = $GLOBALS['abschluss_text'];
  $choices[''] = we(' - select degree - ',' - Abschluss w'.H_AMP.'auml;hlen - ');
  return $choices;
}

function selector_abschluss( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'abschluss_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_abschluss( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_abschluss( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_abschluss( $field, $opts );
}
  

function choices_studiengang() {
  $choices = $GLOBALS['studiengang_text'];
  $choices[''] = ' - Studiengang w'.H_AMP.'auml;hlen - ';
  return $choices;
}

function selector_studiengang( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'studiengang_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_studiengang( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_studiengang( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_studiengang( $field, $opts );
}


function selector_semester( $field = NULL, $opts = array() ) {

  if( ! $field )
    $field = array( 'name' => 'semester' );

  $opts = parameters_explode( $opts, array( 'keep' => 'min,max,choice_0' ) );

  $s = adefault( $field, 'value', 0 );

  $field['min'] = adefault( $field, 'min', 1 );
  $field['max'] = adefault( $field, 'max', 12 );
  if( $s ) {
    $s = max( min( $s, $field['max'] ), $field['min'] );
  }

  $choice_0 = adefault( $opts, 'choice_0', '' );
  // debug( $choice_0, 'choice_0' );
  if( $s || ! $choice_0 ) {
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => 1 ) ) );
  }
}

function filter_semester( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=1,max=12,choice_0= '.we(' (all) ', ' (alle) ' ) ) );
  selector_semester( $field, $opts );
}



?>
