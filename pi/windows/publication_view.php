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

  open_div( 'center bold medskips', $publication['title'] );

  open_div( 'center smallskips', $publication['authors'] );

  open_div( 'left smallskips', $publication['abstract'] );

  open_div( 'medskips', we('Working Group: ','Arbeitsgruppe: ') . alink_group_view( $publication['groups_id'] ) );

  if( $publication['jpegphoto'] ) {
    open_div( 'center medskips', photo_view( $publication['jpegphoto'], $publication['jpegphotorights_people_id'] ) );
  }
//   if( $publication['pdf'] ) {
//     open_div( 'medskips', 'download .pdf: ' . inlink('publication_view', "f=pdf,i=pdf,publications_id=$publications_id,text=publication.pdf,class=file" ) );
//   }

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
