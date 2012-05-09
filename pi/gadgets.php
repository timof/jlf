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
  foreach( sql_groups( $filters, 'acronym' ) as $g ) {
    $choices[ $g['groups_id'] ] = $g['acronym'];
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


function choices_degree() {
  $choices = $GLOBALS['degree_text'];
  $choices[''] = we(' - select type / final degree - ',' - Art / Abschluss w'.H_AMP.'auml;hlen - ');
  return $choices;
}

function selector_degree( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'degree_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_degree( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_degree( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_degree( $field, $opts );
}
  

function choices_programme() {
  $choices = $GLOBALS['programme_text'];
  $choices[''] = we(' - select programme - ',' - Studiengang w'.H_AMP.'auml;hlen - ');
  return $choices;
}

function selector_programme( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'programme_id' );
  $opts = parameters_explode( $opts );
  $choices = adefault( $opts, 'more_choices', array() ) + choices_programme( adefault( $opts, 'filters', array() ) );
  dropdown_select( $field, $choices );
}

function filter_programme( $field, $opts = array() ) {
  $opts = prepare_filter_opts( $opts );
  $opts['more_choices'] = array( 0 => we(' (all) ', ' (alle) ') );
  selector_programme( $field, $opts );
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
  $opts = parameters_explode( $opts, array( 'keep' => 'min=1,max=12,choice_0= '.we(' (all) ',' (alle) ' ) ) );
  selector_semester( $field, $opts );
}


function selector_term( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'semester' );

  $opts = parameters_explode( $opts );

  $choices = adefault( $opts, 'more_choices', array() ) + array( 'S' => we('Summer','Sommer'), 'W' => 'Winter' );
  dropdown_select( $field, $choices );
}
function filter_term( $field, $opts = array() ) {
  selector_term( $field, array( 'more_choices' => array( '0' => we(' (all) ',' (alle) ') ) ) );
}


function selector_year( $field = NULL, $opts = array() ) {
  global $current_year;
  // kludge alert:
  $year_min = 2012;
  $year_max = 2020;

  if( ! $field )
    $field = array( 'name' => 'year' );

  $opts = parameters_explode( $opts, array( 'keep' => 'min,max,choice_0' ) );

  $g = adefault( $field, 'value', $current_year );

  if( $g ) {
    $g = max( min( $g, $year_max ), $year_min );
  }
  $field['min'] = adefault( $field, 'min', $year_min );
  $field['max'] = adefault( $field, 'max', $year_max );

  $choice_0 = adefault( $opts, 'choice_0', '' );
  // debug( $choice_0, 'choice_0' );
  if( $g || ! $choice_0 ) {
    selector_int( $field );
    if( $choice_0 ) {
      open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => "$choice_0", $field['name'] => 0 ) ) );
    }
  } else {
    open_span( 'quads', $choice_0 );
    open_span( 'quads', inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $field['name'] => $current_year ) ) );
  }
}

function filter_year( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'min=0,max=,choice_0= '.we(' (all) ',' (alle) ') ) );
  selector_year( $field, $opts );
}


function selector_typeofposition( $field = NULL, $opts = array() ) {
  if( ! $field )
    $field = array( 'name' => 'typeofposition' );

  $opts = parameters_explode( $opts );

  $choices = adefault( $opts, 'more_choices', array() ) + array( 'B' => we('budget','Haushalt'), 'T' => we('third-party','Drittmittel'), '0' => we('other','sonstige') );
  dropdown_select( $field, $choices );
}
function filter_typeofposition( $field, $opts = array() ) {
  $opts = parameters_explode( $opts, array( 'keep' => 'choice_0='.we(' (all) ',' (alle) ') ) );
  selector_typeofposition( $field, $opts );
}




?>
