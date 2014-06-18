<?php // /pi/windows/applicant_view.php

init_var( 'applicants_id', 'global,type=u,sources=self http,set_scopes=self' );
if( ! $applicants_id ) {
  open_div( 'warn', we('no applicant selected','kein Bewerber gewaehlt') );
  return;
}

sql_transaction_boundary( array( 'applicants' ) );
$applicant = sql_one_applicant( $applicants_id );

if( $deliverable ) switch( $deliverable ) {

  case 'applicant':
    $applicant = array(
      'gn' => $applicant['gn']
    , 'sn' => $applicant['sn']
    , 'mail' => $applicant['mail']
    , 'street' => $applicant['street']
    , 'city' => $applicant['city']
    , 'country' => $applicant['country']
    , 'language' => $applicant['language']
    , 'programme' => $programme_text[ $applicant['programme'] ]
    , 'questions' => $applicant['questions']
    );
    switch( $global_format ) {
      case 'pdf':
        begin_deliverable( 'applicant', 'pdf'
        , tex2pdf( 'applicant.tex', array( 'loadfile', 'row' => $applicant ) )
        );
        break;
      case 'ldif':
        begin_deliverable( 'applicant', 'ldif'
        , ldif_encode( $applicant )
        );
        break;
      default:
        error( "unsupported format: [$global_format]" );
    }
    return;
  
  default:
    error("no such deliverable: $deliverable");
}


open_fieldset( 'quads qqpads old', we('Prospective Student','StudieninteressierteR') );

  open_div('bold medskips', "{$applicant['gn']} {$applicant['sn']}" );

  open_table('td:qpads;smallpads th:left;qpads;smallpads');
    open_tr();
      open_th( '', we('street',"Stra{$SZLIG}e") );
      open_td( '', $applicant['street'] );

    open_tr();
      open_th( '', we('city',"Ort") );
      open_td( '', $applicant['city'] );

    open_tr();
      open_th( '', we('country',"Land") );
      open_td( '', $applicant['country'] );

    open_tr();
      open_th( '', 'email' );
      open_td( '', $applicant['mail'] );

    open_tr();
      open_th( '', we('language','Sprache') );
      open_td( '', $applicant['language'] );

    open_tr();
      open_th( '', we('programme','Studiengang') );
      open_td( '', $programme_text[ $applicant['programme'] ] );

    open_tr();
      open_th( 'top', we('questions','Fragen') );
      open_td( '', $applicant['questions'] );

  close_table();

  open_div( 'right medskipt', download_button( 'applicant', 'ldif,pdf', "applicants_id=$applicants_id" ) );

close_fieldset();

?>
