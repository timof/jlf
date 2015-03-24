<?php // /pi/windows/highlight_view.php

sql_transaction_boundary('*');

init_var( 'highlights_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $highlights_id ) {
  open_div( 'warn', we('no highlight selected','kein Highlight gewaehlt') );
  return;
}

$highlight = sql_one_highlight( $highlights_id );

if( $deliverable ) switch( $deliverable ) {

  case 'highlight':
    $highlight = array(
      'cn_en' => $highlight['cn_en']
    , 'cn_de' => $highlight['cn_de']
    , 'note_en' => $highlight['note_en']
    , 'note_de' => $highlight['note_de']
    , 'date' => $highlight['date']
    , 'time' => $highlight['time']
    , 'url' => $highlight['url']
    , 'jpegphoto' => $highlight['jpegphoto']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'highlight', 'pdf'
        , tex2pdf( 'highlight.tex', array( 'loadfile', 'row' => $highlight ) )
        );
        break;
      case 'ldif':
        unset( $highlight['jpegphoto'] );
        begin_deliverable( 'highlight', 'ldif'
        , ldif_encode( $highlight )
        );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;

  case 'attachment': // for attached file
    begin_deliverable( 'attachment', 'pdf' , base64_decode( $highlight['pdf'] ) );
    return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'highlights', $highlights_id ) ) : '' );
open_fieldset( 'qquads old', we( 'highlight', 'Highlight' ) . $v );

  open_fieldset( '', we('attributes','Attribute') );
    open_ul();
      open_li( '', $highlight['flag_publish'] ? we('published',"ver{$oUML}ffentlicht") : we('not published',"nicht ver{$oUML}ffentlicht") );
//      open_li( '', $highlight['flag_detailview'] ? we('has detail view',"mit Detailanzeige") : we('no detail view',"keine Detailanzeige") );
    close_ul();
  close_fieldset();

  echo highlight_view( $highlight, 'format=highlight' );

  open_div( 'right bigskips' );
    // echo download_button( 'highlight', 'ldif,pdf', "highlights_id=$highlights_id" );
    if( have_priv( 'highlights', 'edit', $highlights_id ) ) {
      echo inlink( 'highlight_edit', array(
        'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
      , 'highlights_id' => $highlights_id
      , 'inactive' => priv_problems( 'highlights', 'edit', $highlights_id )
      ) );
    }
  close_div();

close_fieldset();

?>
