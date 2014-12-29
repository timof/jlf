<?php  // /pi/windows/teachinganon.php

sql_transaction_boundary('*');

need_priv( 'teaching', 'list' );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields(  array(
  'term' => array( 'default' => '0', 'initval' => $teaching_survey_term )
, 'year' => array( 'default' => '0', 'initval' => $teaching_survey_year, 'min' => '2011', 'max' => $current_year + 1 )
) );

$filters = $f['_filters'];

// debug( $f, 'f' );
$actions = array();
handle_actions( $actions );


open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Term:','Semester:') );
    open_td( 'oneline', filter_term( $f['term'] ) . filter_year( $f['year'] ) );

//  open_tr();
//    open_th( 'center,colspan=2', 'Aktionen' );
close_table();

medskip();

if( $debug ) {
  // debug( $filters, 'filters' );
}

teachinganon_view( $filters );


?>
