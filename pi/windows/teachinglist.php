<?php  // /pi/windows/teachinglist.php

sql_transaction_boundary('*');

$filter_fields = array(
  'term' => array( 'default' => '0', 'initval' => $teaching_survey_term, 'allow_null' => '0' )
, 'year' => array( 'default' => '0', 'initval' => $teaching_survey_year, 'min' => '2011', 'max' => '2020', 'allow_null' => '0' )
, 'course_number' => 'u,allow_null=0,min=1,max=999'
, 'SEARCH' => 'size=20,auto=1,relation=~='
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


echo html_tag( 'h1', '', we('Teaching','Lehre') . html_span( 'smaller qquads'
  , $teaching_survey_open
    ? we('survey open for ','Erfassung ist freigegeben für ' ) . $teaching_survey_term . $teaching_survey_year
    : we('survey is currently closed','Erfassung ist zur Zeit gesperrt')
  )
);

open_div('menubox');

  open_table('css filters');
    open_caption( '', 'Filter' );
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
          open_div( '', filter_person( $f_teacher['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
        }

    open_tr();
      open_th( '', we('Signer:','Unterzeichner:') );
      open_td();
        open_div( '', filter_group( $f_signer['groups_id'] ) );
        if( ( $g_id = $f_signer['groups_id']['value'] ) ) {
          open_div( '', filter_person( $f_signer['people_id'], array( 'filters' => "groups_id=$g_id" ) ) );
        }

    open_tr();
      open_th( '', we('Submitter:','Erfasser:') );
      open_td();
        open_div( '', filter_group( $f_creator['groups_id'] ) );
        if( ( $g_id = $f_creator['groups_id']['value'] ) ) {
          open_div( '', filter_person( $f_creator['people_id'], array( 'filters' => "groups_id=$g_id,privs >= 1" ) ) );
        }
  }
    open_tr();
      open_th( '', we('course number:','Nummer im VVZ:') );
      open_td( '', filter_int( $f['course_number'] ) );

    open_tr();
      open_th( '', we('search:','suche:') );
      open_td( 'oneline', ' / '.string_element( $f['SEARCH'] ).' / ' . filter_reset_button( $f['SEARCH'], '/floatright//' ) );

  close_table();

  $actions = array();
  $year = $f['year']['value'] ? $f['year']['value'] : $teaching_survey_year;
  $term = $f['term']['value'] ? $f['term']['value'] : $teaching_survey_term;
  if( have_priv( 'teaching', 'create', "year=$year,term=$term" ) ) {
    $actions[] = inlink( 'teaching_edit', array(
      'class' => 'big button', 'text' => we('add entry','neuer Eintrag')
    , 'year' => $year, 'term' => $term
    ) );
  }
  if( have_priv( 'teaching', 'list' ) ) {
    $actions[] = inlink( 'teachinglist', array(
        'class' => 'big button', 'format' => 'csv', 'i' => 'teachinganon'
      , 'text' => we('download CSV','CSV erzeugen' )
    ) );
    if( ( "{$f['year']['value']}" !== '0' ) && ( "{$f['term']['value']}" !== '0' ) ) {
      $actions[] = inlink( 'teachinganon', array(
          'class' => 'big button', 'window' => 'teachinganon'
        , 'text' => we('anonymized List','anonymisierte Liste' )
        , 'year' => $f['year']['value'], 'term' => $f['term']['value']
      ) );
    }
  }
  if( $actions ) {
    open_table('css actions' );
      open_caption( '', we('Actions','Aktionen') );
      foreach( $actions as $a ) {
        open_tr( '', $a );
      }
    close_table();
  }

close_div();

medskip();

teachinglist_view( $filters, 'allow_download=' );

?>
