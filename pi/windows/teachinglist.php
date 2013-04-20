<?php 


$filter_fields = array(
  'term' => array( 'default' => '0', 'initval' => $teaching_survey_term, 'allow_null' => '0' )
, 'year' => array( 'default' => '0', 'initval' => $teaching_survey_year, 'min' => '2011', 'max' => '2020', 'allow_null' => '0' )
, 'REGEX' => 'size=20,auto=1'
);

$f = init_fields( $filter_fields );
$filters = $f['_filters'];
// debug( $f, 'f' );

if( have_priv( 'teaching', 'list' ) ) {
  $f_teacher = filters_person_prepare( true, 'sql_prefix=teacher_,cgi_prefix=F_teacher_,auto_select_unique' );
  // debug( $f_teacher, 'f_teacher' );
  $f_signer = filters_person_prepare( true, 'sql_prefix=signer_,cgi_prefix=F_signer_,auto_select_unique' );
  $f_creator = filters_person_prepare( true, 'sql_prefix=creator_,cgi_prefix=F_creator_,auto_select_unique' );
  $filters = array_merge( $filters, $f_teacher['_filters'], $f_signer['_filters'], $f_creator['_filters'] );
  // unset( $filters['creator_groups_id'] );
}


// if( $global_format == 'csv' ) {
//   // download request
//   need_priv( 'teaching', 'list' );
//   // echo "[[start: [$global_format] ]]";
//   teachinglist_view( $filters, array( 'format' => $global_format ) );
//   // echo "[[end]]";
//   return;
// }


echo html_tag( 'h1', '', we('Teaching','Lehre') . html_span( 'small qquads'
  , $teaching_survey_open
    ? we('survey open for ','Erfassung ist freigegeben für ' ) . $teaching_survey_term . $teaching_survey_year
    : we('survey is currently closed','Erfassung ist zur Zeit gesperrt')
  )
);

open_div('menu');

  open_table('css=1');
    open_caption( 'center th', 'Filter' );
    // open_caption( 'center th', filter_reset_button( $f, 'floatright' ). 'Filter' );
    open_tr();
      open_th( '', we('Term / year:','Semester / Jahr:') );
      open_td( 'oneline' );
        echo filter_term( $f['term'] );
        echo ' / ';
        echo filter_year( $f['year'] );

  if( have_priv( 'teaching', 'list' ) ) {
    open_tr();
      open_th( '', we('Teacher:','Lehrender:') );
      open_td();
        open_div( '', filter_group( $f_teacher['groups_id'] ) );

        if( ( $g_id = $f_teacher['groups_id']['value'] ) ) {
          open_div( 'smallskips', filter_person( $f_teacher['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
        }

    open_tr();
      open_th( '', we('Signer:','Unterzeichner:') );
      open_td();
        open_div( 'smallskips', filter_group( $f_signer['groups_id'] ) );
        if( ( $g_id = $f_signer['groups_id']['value'] ) ) {
          open_div( 'smallskips', filter_person( $f_signer['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
        }

    open_tr();
      open_th( '', we('Submitter:','Erfasser:') );
      open_td();
        open_div( 'smallskips', filter_group( $f_creator['groups_id'] ) );
        if( ( $g_id = $f_creator['groups_id']['value'] ) ) {
          open_div( 'smallskips', filter_person( $f_creator['people_id'], array( 'filters' => "groups_id=$g_id,privs >= 1" ) ) );
        }
  }

    open_tr();
      open_th( '', we('search:','suche:') );
      open_td( '', string_element( $f['REGEX'] ) . filter_reset_button( $f['REGEX'] ) );

  close_table();

  $actions = array();
  if( have_priv( 'teaching', 'create' ) ) {
    $actions[] = inlink( 'teaching_edit', array(
      'class' => 'bigbutton', 'text' => we('add entry','neuer Eintrag' )
    ) );
  }
  if( have_priv( 'teaching', 'list' ) ) {
    $actions[] = inlink( 'teachinglist', array(
        'class' => 'bigbutton', 'format' => 'csv', 'window' => 'download'
      , 'text' => we('download CSV','CSV erzeugen' )
    ) );
    if( ( "{$f['year']['value']}" !== '0' ) && ( "{$f['term']['value']}" !== '0' ) ) {
      $actions[] = inlink( 'teachinganon', array(
          'class' => 'bigbutton', 'window' => 'teachinganon'
        , 'text' => we('anonymized List','anonymisierte Liste' )
        , 'year' => $f['year']['value'], 'term' => $f['term']['value']
      ) );
    }
  }
  if( $actions ) {
    open_div( 'center th', we('Actions','Aktionen') );
    foreach( $actions as $a ) {
      open_div( 'center', $a );
    }
  }

close_div();

medskip();

teachinglist_view( $filters, 'list_options=allow_download=1' );

?>
