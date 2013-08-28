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
//      'dn' => "publications_id=$publications_id,ou=publications,ou=physik,o=uni-potsdam,c=de"
      'title' => $publication['title']
    , 'authors' => $publication['authors']
    , 'journal' => $publication['journal']
    , 'reference' => $publication['reference']
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

//  case 'pdf':
//    begin_deliverable( 'pdf', 'pdf', base64_decode( $publication['pdf'] ) );
//    return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'publications', $publications_id ) ) : '' );
open_fieldset( 'qquads old', we( 'publication', 'Publikation' ) . $v );

  open_fieldset( '' , 'Highlight '. we('view','Ansicht') );
    open_table('css');
      echo publication_columns_view( $publication );
    close_table();
  close_fieldset();

  open_fieldset( ''
  , we('Reference','Verweis')
  , html_tag( 'ul', 'references', publication_reference_view( $publication ) )
  );

  open_div( 'right bigskips' );
    echo download_button( 'publication', 'ldif,pdf', "publications_id=$publications_id" );
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
