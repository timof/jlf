<?php 

// kludge alert - make this configurable!
$allow_edit = 1;
$term_edit = 'S';
$year_edit = 2012;

init_var( 'options', 'global,type=u,sources=http self,set_scopes=self' );

$actions = array();
handle_action( $actions );


need_priv( 'teaching', 'list' );

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

teachinganon_view();


?>
