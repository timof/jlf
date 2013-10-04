<?php // /pi/windows/document_view.php

sql_transaction_boundary('*');

init_var( 'documents_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $documents_id ) {
  open_div( 'warn', we('no document selected','keine Datei gewählt') );
  return;
}

$document = sql_one_document( $documents_id );
$a = $document['programme_id'];
$document['programme_cn'] = '';
$comma = '';
foreach( $programme_text as $programme_id => $programme_cn ) {
  if( $a & $programme_id ) {
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
  open_table('hfill');

    open_tr( 'bigskips' );
      open_td( 'colspan=2,center bold larger', $document['cn'] );

    open_tr( 'medskip' );
      open_td( '', we('Programme:','Studiengang:') );
      open_td( 'oneline', $document['programme_cn'] );

    open_tr();
      open_td( 'colspan=2', $document['note'] );

    open_tr( 'bigskip' );
      open_td( '', we('saved file:', 'gespeicherte Datei:' ) );
      if( $document['pdf'] ) {
        open_td( 'oneline', inlink( 'document_view', "text=download .pdf,class=file,f=pdf,window=download,i=document,documents_id=$documents_id" ) );
      } else {
        open_td( 'warn', we('no document saved (yet):', '(noch) keine Datei gespeichert' ) );
      }

    open_tr();
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
