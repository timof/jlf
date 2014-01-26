<?php

sql_transaction_boundary('*');

echo html_tag('h1', '', we('Suggested Topics for Theses','Themenvorschläge für Abschlussarbeiten' ) );

init_var( 'positions_id', 'global=1,set_scopes=self,sources=http persistent' );

$f = init_fields( array(
  'groups_id'
, 'programme_flags' => 'relation=&='
, 'SEARCH' => 'size=40,auto=1,relation=%='
) );

open_div('menubox');
  open_table('css menubox');
    open_caption( '', filter_reset_button( $f, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', we('Programme/Degree:','Studiengang/Abschluss:' ) );
      open_td( '', filter_programme( $f['programme_flags'] ) );
    open_tr();
      open_th( '', we('Group:','Bereich:') );
      open_td( '', filter_group( $f['groups_id'] ) );
    open_tr();
      open_th( '', we('search:','Suche:') );
      open_td( '', string_element( $f['SEARCH'] ) );
  close_table();
close_div();

$filters = $f['_filters'];
if( adefault( $filters, 'SEARCH %=' ) ) {
  unset( $filters['SEARCH %='] );
  $filters['SEARCH %='] = "%{$f['_filters']['SEARCH %=']}%";
}
positionslist_view( $filters, 'allow_download=1,insert=1,select=positions_id' );

?>
