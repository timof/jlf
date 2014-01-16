<?php // /pi/windows/document_view.php

sql_transaction_boundary('*');

init_var( 'documents_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $documents_id ) {
  open_div( 'warn', we('no document selected','keine Datei gewählt') );
  return;
}

$document = sql_one_document( $documents_id );
$a = $document['programme_flags'];
$document['programme_cn'] = '';
$comma = '';
foreach( $programme_text as $programme_flags => $programme_cn ) {
  if( $a & $programme_flags ) {
    $document['programme_cn'] .= "$comma$programme_cn";
    $comma = ', ';
  }
}

if( $deliverable ) switch( $deliverable ) {

  case 'document':
    begin_deliverable( 'document', 'pdf' , base64_decode( $document['pdf'] ) );
    return;

  default:
    error("no such deliverable: $deliverable");
}


$v = ( have_priv('*','*') ? html_span('qquadl', any_link( 'documents', $documents_id ) ) : '' );
open_fieldset( '' ); // , we( 'topic / postion', 'Thema / Stelle' ) . $v );
  open_table('hfill td:qquads');

    open_tr( 'td:bigskips' );
      open_td( 'colspan=2,center bold larger', $document['cn'] );

    open_tr( 'td:smallskipt' );
      open_td( '', we('type:','Art:' ) );
      open_td( '', adefault( $choices_documenttype, $document['type'], we('(not valid type set)',"(kein g{$uUML}ltiger Typ gew{$aUML}hlt)") ) );

    open_tr( 'td:smallskipt' );
      open_td( '', we('tag:','Kurzbezeichnung:' ) );
      open_td( '', $document['tag'] );

    open_tr( 'td:smallskipt' );
      open_td( '', we('file name:','Dateiname:' ) );
      open_td( '', $document['filename'] );

    open_tr( 'td:smallskipt' );
      open_td( '', we('valid from:',"g{$uUML}ltig ab:" ) );
      open_td( '', $document['valid_from'] );

    open_tr( 'td:smallskipt' );
      open_td( '', we('Programme:','Studiengang:') );
      open_td( '', $document['programme_cn'] );

    open_tr( 'td:smallskipt' );
      open_td( '', we('attributes:','Attribute:' ) );
      open_td();
        open_div( '', ( $document['flag_publish'] ? we('is published',"wird {$oUML}ffentlich angezeigt") : we('is NOT published',"wird NICHT {$oUML}ffentlich angezeigt" ) ) );
        open_div( '', ( $document['flag_current'] ? we('is current version',"ist aktuelle Fassung") : we('is NOT current version',"ist NICHT aktuelle Fassung" ) ) );

    open_tr('td:smallskipt');
      open_td( 'colspan=2', $document['note'] );

    open_tr( 'td:medskipt' );
      if( $document['pdf'] ) {
        open_td( '', we('saved file:', 'gespeicherte Datei:' ) );
        open_td( 'oneline', inlink( 'document_view', array(
          'documents_id' => $documents_id
        , 'f' => 'pdf'
        , 'i' => 'document'
        , 'class' => 'file'
        , 'window' => 'download'
        , 'n' => hex_encode( $document['filename'] )
        , 'text' => $document['filename']
        ) ) );
      } else if( $document['url'] ) {
        open_td( '', we('external link:', 'externer Link:' ) );
        open_td( 'oneline', html_alink( $document['url'], array( 'text' => $document['url'], 'class' => 'href outlink' ) ) );
      } else {
        open_td( 'warn,colspan=2', we('no document saved (yet):', '(noch) keine Datei gespeichert' ) );
      }

    open_tr( 'td:bigskips' );
      open_td( 'right,colspan=2' );
      if( $logged_in ) {
        echo inlink( 'document_edit', array(
          'class' => 'button edit', 'text' => we('edit...','bearbeiten...' )
        , 'documents_id' => $documents_id
        , 'inactive' => priv_problems( 'documents', 'edit', $documents_id )
        ) );
      }

  close_table();

close_fieldset();

?>
