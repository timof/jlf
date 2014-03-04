<?php  // /pi/windows/teachinganon.php

sql_transaction_boundary('*');

need_priv( 'teaching', 'list' );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );
define( 'OPTION_EXTERN', 0x01 );

$f = init_fields(  array(
  'term' => array( 'default' => '0', 'initval' => $teaching_survey_term )
, 'year' => array( 'default' => '0', 'initval' => $teaching_survey_year, 'min' => '2011', 'max' => '2020' )
) );

$filters = $f['_filters'];


open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Term:','Semester:') );
    open_td( 'oneline', filter_term( $f['term'] ) . filter_year( $f['year'] ) );

  open_tr('td:smallpads;qquads');
    open_th( '', 'Optionen:' );
    open_td( 'oneline' );
    echo checkbox_element( array(
    'name' => 'options'
    , 'normalized' => $options
    , 'mask' => OPTION_EXTERN
    , 'text' => 'externe'
    , 'auto' => 1
    ) );
close_table();

medskip();


$rows = sql_affiliations( "teaching_obligation>0" );
$obligations = array();
foreach( $rows as $r ) {
  $p_id = $r['people_id'];
  $g_id = $r['groups_id'];
  if( ! isset( $obligations[ $p_id ] ) ) {
    $obligations[ $p_id ] = array();
  }
  $obligations[ $p_id ] [ $g_id ] = $r;
}

// externe:

  $teachers = array_merge(
    sql_teaching( array( '&&', $filters, "teacher_group.flag_institute=0" ), 'groupby=teacher_people_id' )  // merge: members of non-institute groups...
  , sql_teaching( array( '&&', $filters, "extern" ), 'groupby=extteacher_cn' )      // ...plus unknown aliens (kludge on special request by diph)
  );
  $teachings = array_merge(
    sql_teaching( array( '&&', $filters, "teacher_group.flag_institute=0,lesson_type!=X,lesson_type!=N" ) )  // merge: members of non-institute groups...
  , sql_teaching( array( '&&', $filters, "extern,lesson_type!=X,lesson_type!=N" ) )       // ...plus unknown aliens (kludge on special request by diph)
  );

// erfasste:


// nicht erfasste:


  $teachers = sql_teaching(
    array( '&&', $filters, "teacher_groups_id=$groups_id" )
  , array(
      'groupby' => 'teacher_people_id'
    , 'orderby' => "IF( teaching.teacher_people_id = $head_people_id, 0, 1 ), teacher.privs DESC,teacher_cn"
    )
  );
  $teachings = sql_teaching(
    array( '&&', $filters, "teacher_groups_id=$groups_id,lesson_type!=X,lesson_type!=N" )
  , array(
      'orderby' => "IF( teaching.teacher_people_id = $head_people_id, 0, 1 ), CAST( course_number AS UNSIGNED )"
    )
  );





?>
