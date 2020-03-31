<?php

sql_transaction_boundary('*');

open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('h28innenhof');
    echo html_tag( 'h1', '', we('Institute / Committees','Institut / Gremien') );
  close_div();
close_div();

open_ccbox( '', 'id=irat,'.we('Institute Board','Institutsrat') );

  $board = $boards['instituteBoard'];
  
  open_table( 'th;td:smallskipb;qquads th:black;bold;solidtop,id=institutsrat,colgroup=50% 50%' );
    open_tr('medskips');
      open_th( 'colspan=2,center', we('chair person',"Gesch{$aUML}ftsf{$uUML}hrende Leitung (Vorsitz)" ) );
  
    open_tr('medskips');
      open_td( 'colspan=2,center', alink_person_view( 'board=executive,function=chief', 'office' ) );
  
    foreach( array( 'professors' => 'deputyProfs'
                  , 'academicStaff' => 'deputyAcademicStaff'
                  , 'students' => 'deputyStudents'
                  , 'technicalStaff' => 'deputyTechnicalStaff' ) as $function => $deputy ) {
  
      open_tr('medskips');
        open_th( 'left', $board[ $function ]['function'] );
        open_th( 'left', we('deputies','Stellvertreter_innen') );
  
      $rank = 1;
      while( true ) {
        $id1 = sql_offices( "board=instituteBoard,function=$function,rank=$rank", 'single_field=people_id,default=0' );
        $id2 = sql_offices( "board=instituteBoard,function=$deputy,rank=$rank", 'single_field=people_id,default=0' );
        if( $id1 || $id2 ) {
          open_tr();
            open_td( '', alink_person_view( $id1, 'default=' ) );
            open_td( '', alink_person_view( $id2, 'default=' ) );
          $rank++;
        } else {
          break;
        }
      }
    }
  
  close_table();
  
  echo tb( we('Rules of procedure of the institute',"Gesch{$aUML}ftsordnung des Instituts")
  , alink_document_view( 'tag=go_inst_1998' )
  );

close_ccbox();


foreach( array( 'examBoardMono' , 'examBoardEdu', 'studiesBoard' ) as $boardname ) {
  $board = $boards[ $boardname ];

  open_ccbox( "id=$boardname", $board['_BOARD'] );
    open_table( "hfill th;td:smallskipb;qquads th:smallskipb;black;bold;solidtop" );
      foreach( $board as $fname => $function ) {
        if( $fname[ 0 ] === '_' )
          continue;
        open_tr();
          open_th( 'left,colspan=4', $function['function'] );
          $members = sql_offices( "board=$boardname,function=$fname", 'orderby=rank' );
          foreach( $members as $m ) {
            open_tr();
              open_td( 'top' );
                open_div( '', alink_person_view( $m['people_id'], 'office' ) );
                if( ( $boardname == 'examBoardMono' ) && ( $fname == 'chair' ) ) {
                  open_div( '', 'email: ' . html_obfuscate_email( 'PA-Physik@uni-potsdam.de' ) );
                }
              open_td( 'top', $m['roomnumber'] );
              open_td( 'top', $m['telephonenumber'] );
              open_td( 'top,style=max-width:200px;', $m['office_hours'] );
          }
      }
    close_table();
  close_ccbox();
}

?>
