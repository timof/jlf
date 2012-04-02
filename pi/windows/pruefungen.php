<?php

echo html_tag( 'h1', '', we('Exams','Prüfungen') );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields( array(
    'jahr_von' => 'type=U,min=2012,max=2020,default='.substr( $utc, 0, 4 )
  , 'jahr_bis' => 'type=U,min=2012,max=2020,default='.substr( $utc, 0, 4 )
  , 'kw_von' => 'type=U,min=1,max=52,default=1'
  , 'kw_bis' => 'type=U,min=1,max=52,default=52'
  , 'studiengang_id'
  , 'semester' => 'type=u,min=0,max=12'
  )
, ''
);


$f['jahr_bis']['value'] = max( $f['jahr_bis']['value'], $f['jahr_von']['value'] );
$f['jahr_bis']['min'] = $f['jahr_von']['value'];

$f['kw_bis']['value'] = max( $f['kw_bis']['value'], $f['kw_von']['value'] );
$f['kw_bis']['min'] = $f['kw_von']['value'];

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'Studiengang:' );
    open_td();
      echo filter_studiengang( $f['studiengang_id'] );
  open_tr();
    open_th( 'right', 'Regelsemester:' );
    open_td();
      echo filter_semester( $f['semester'] );
  open_tr();
    open_th( 'right', 'Zeitraum von' );
    open_td( 'oneline' );
      selector_int( $f['jahr_von'] );
      echo '/ KW ';
      selector_int( $f['kw_von'] );
  open_tr();
    open_th( 'right', 'bis' );
    open_td( 'oneline' );
      selector_int( $f['jahr_bis'] );
      echo '/ KW ';
      selector_int( $f['kw_bis'] );
  open_tr();
    open_th( 'center,colspan=1', 'Aktionen' );
    open_td( 'center,colspan=1', inlink( 'pruefung', 'class=bigbutton,text=Neue Pruefung' ) );
close_table();

bigskip();


handle_action( array( 'update', 'deletePruefung' ) );
switch( $action ) {
  case 'deletePruefung':
    need( $message > 0, 'keine pruefung ausgewaehlt' );
    sql_delete_pruefungen( $message );
    break;
}

medskip();

pruefungenlist_view( $f['_filters'], '' );

?>
