<?php 

// kludge alert - make this configurable!
$allow_edit = 1;
$term_edit = 'S';
$year_edit = 2012;

need_priv( 'teaching', 'list' );

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$f = init_fields(  array(
  'term' => array( 'default' => $term_edit )
, 'year' => array( 'default' => $year_edit, 'min' => '2011', 'max' => '2020' )
) );

$filters = $f['_filters'];

// debug( $f, 'f' );
$actions = array();
handle_action( $actions );


open_table('menu');
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( '', we('Term:','Semester:') );
    open_td( 'oneline' );
      filter_term( $f['term'] );
      filter_year( $f['year'] );

//  open_tr();
//    open_th( 'center,colspan=2', 'Aktionen' );
close_table();

medskip();

if( $debug ) {
  // debug( $filters, 'filters' );
}

teachinganon_view( $filters );


?>
