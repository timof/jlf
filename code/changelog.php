<?php

need_priv( 'changelog', 'list' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );
init_var( 'changelog_id', 'global,type=W128,sources=http persistent,set_scopes=self' );

echo html_tag( 'h1', '', "changelog:" );

open_list();
  open_list_row('header');
    open_list_cell('id');
    open_list_cell('id');

close_list();


?>
