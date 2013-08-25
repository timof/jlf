<?php

need( false );

echo html_tag( 'h1', '', we('Exams','Prüfungen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'year_from' => 'type=U,min=2012,max=2020,default='.substr( $utc, 0, 4 )
  , 'year_to' => 'type=U,min=2012,max=2020,default='.substr( $utc, 0, 4 )
  , 'week_from' => 'type=U,min=1,max=52,default=1'
  , 'week_to' => 'type=U,min=1,max=52,default=52'
  , 'programme_id'
  , 'semester' => 'type=u,min=0,max=12'
  )
, ''
);


$f['year_to']['value'] = max( $f['year_to']['value'], $f['year_from']['value'] );
$f['year_to']['min'] = $f['year_from']['value'];

$f['week_to']['value'] = max( $f['week_to']['value'], $f['week_from']['value'] );
$f['week_to']['min'] = $f['week_from']['value'];

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', we('Programme','Studiengang:') );
    open_td( '', filter_programme( $f['programme_id'] ) );
  open_tr();
    open_th( 'right', we('Term:','Regelsemester:') );
    open_td( '', filter_semester( $f['semester'] ) );
  open_tr();
    open_th( 'right', we('Date from','Zeitraum von') );
    open_td( 'oneline', selector_int( $f['year_from'] ) . '/ '.we('week ','KW ') . selector_int( $f['week_from'] ) );
  open_tr();
    open_th( 'right', we('to','bis') );
    open_td( 'oneline', selector_int( $f['year_to'] ) . '/ '.we('week ','KW ') . selector_int( $f['week_to'] ) );
  if( have_priv( 'exam', 'create' ) ) {
    open_tr();
      open_th( 'center,colspan=1', we('Actions','Aktionen') );
      open_td( 'center,colspan=1', inlink( 'exam_edit', 'class=bigbutton,text='.we('New exam','Neue Pruefung') ) );
  }
close_table();

bigskip();


handle_action( array( 'update', 'deleteExam' ) );
switch( $action ) {
  case 'deleteExam':
    need( $message > 0, we('no exam selected','keine pruefung ausgewaehlt') );
    sql_delete_exams( $message );
    break;
}

medskip();

examslist_view( $f['_filters'], '' );

?>
