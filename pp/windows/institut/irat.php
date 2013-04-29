<?php

$board = $boards['instituteBoard'];

open_table( 'th;td:smallskips;qquads;formcolor;shaded th:black;bold,id=institutsrat,colgroup=50% 50%' );
  open_tr('medskips');
    open_th( 'colspan=2,center', we('chair person','Geschäftsführender Leiter (Vorsitz)' ) );

  open_tr('medskips');
    open_td( 'colspan=2,center', html_alink_person( 'board=executive,function=chief', 'office' ) );

  foreach( array( 'professors' => 'deputyProfs'
                , 'academicStaff' => 'deputyAcademicStaff'
                , 'students' => 'deputyStudents'
                , 'technicalStaff' => 'deputyTechnicalStaff' ) as $function => $deputy ) {

    open_tr('medskips');
      open_th( 'left', $board[ $function ]['function'] );
      open_th( 'left', we('deputies','Stellvertreter') );

    $rank = 1;
    while( true ) {
      $id1 = sql_offices( "board=instituteBoard,function=$function,rank=$rank", 'single_field=people_id,default=0' );
      $id2 = sql_offices( "board=instituteBoard,function=$deputy,rank=$rank", 'single_field=people_id,default=0' );
      if( $id1 || $id2 ) {
        open_tr();
          open_td( '', html_alink_person( $id1, 'default=' ) );
          open_td( '', html_alink_person( $id2, 'default=' ) );
        $rank++;
      } else {
        break;
      }
    }
  }

close_table();

// _m4_medskip
// _m4_tr
// _m4_td(class='bigskip')
//   _m4_file(/institut/geschaeftsordnung.pdf,[[Geschäftsordnung des Instituts]])
// 
// 
// 
// 
// 
// _m4_bigskip
// _m4_tr
// _m4_td(class='bigskip')
// 
// 
//   <h2>Sitzungen des Institutsrats</h2>
// 
// 
// _m4_ifelse(1,1,[[
// 
// 	_m4_p
// 	<table id="program" width="98%">
// 	  <tr> 
// 		  <th colspan="2">
//         Mittwoch, 30. Januar 2013
// 			</th>
// 	  </tr> 
//     <tr>
//       <td>Beginn:</td>
//       <td>14.00 Uhr</td>
//     </tr>
//     <tr>
//       <td>Raum:</td>
//       <td>2.28.0.104 (Seminarraum im Erdgeschoss)</td>
//     </tr>
//     <tr>
//       <td>Tagesordnung:</td>
//       <td><ul>
//         <li>Bericht des Gesch&auml;ftsf&uuml;hrenden Leiters</li>
//         <li>Personalia</li>
//         <li>Studienangelegenheiten im Wintersemester</li>
//         <li>offene Themen</li>
//       </ul>
//       </td>
//     </tr>
// 
// 	</table>
// ]])
// 
// 

?>
