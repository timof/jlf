<?php

init_var( 'publications_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $publications_id ) {
  open_div( 'warn', we('no publications selected','keine Publikation gewaehlt') );
  return;
}

$publication = sql_one_publication( $publications_id );

if( $deliverable ) switch( $deliverable ) {

  case 'publication':
    $publication = array(
      'dn' => "publications_id=$publications_id,ou=publications,ou=physik,o=uni-potsdam,c=de"
    , 'title' => $publication['title']
    , 'authors' => $publication['authors']
    , 'abstract' => $publication['abstract']
    , 'url' => $publication['url']
    , 'year' => $publication['year']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'publication', 'pdf'
        , tex2pdf( 'publication.tex', array( 'loadfile', 'row' => $publication ) )
        );
        break;
      case 'ldif':
        begin_deliverable( 'publication', 'ldif'
        , ldif_encode( $publication )
        );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'publications', $publications_id ) ) : '' );
open_fieldset( 'qquads old', we( 'publication', 'Publikation' ) . $v );

  open_table('css hfill');
    open_caption( 'center bold medskips', $publication['title'] );

    open_tr();
      open_td( 'center', $publication['authors'] );

    open_tr();
      open_td( 'left', $publication['abstract'] );

    open_tr( 'medskip' );
      open_td( '', we('Group:','Gruppe:') );
      open_td( '', html_alink_group( $publication['groups_id'] ) );

  close_table();

  open_div( 'right smallskips' );
    echo download_button( 'ldif,pdf', 'publication' );
    if( have_priv( 'publications', 'edit', $publications_id ) ) {
      echo inlink( 'publication_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'publications_id' => $publications_id
      , 'inactive' => priv_problems( 'publications', 'edit', $publications_id )
      ) );
    }
  close_div();


close_fieldset();

?>
