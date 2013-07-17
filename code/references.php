<?php

need_priv( 'references', 'list' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
init_var( 'referent', 'global,type=W128,sources=http persistent,set_scopes=self' );
init_var( 'referent_id', 'global,type=U,sources=http persistent,set_scopes=self' );

echo html_tag( 'h1', '', "references: $referent/$referent_id" );

references_view( $referent, $referent_id );

?>
