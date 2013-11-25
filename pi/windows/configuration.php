<?php // /pi/windows/configuration.php

need_priv( 'config', 'read' );

sql_transaction_boundary('*');

init_var( 'flag_problems', 'global,type=b,sources=self,set_scopes=self' );
init_var( 'category', 'global,type=w,sources=http self,set_scopes=self' );
if( $category ) {
  need_priv( 'config', 'write', $category );
}

$reinit = ( $action === 'reset' ? 'reset' : 'init' );

while( $reinit ) {

  switch( $reinit ) {
    case 'init':  // first call: try all sources in order
      $sources = 'http self initval';
      break;
    case 'self':  // when iterating
      $sources = 'self';
      break;
    case 'reset': // on forces reset
      $flag_problems = 0;
      $sources = 'initval';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'person,init' );
  }

  $reinit = false;

    // $config_fields = array(
    //   'current_year' => array(
    //     'type' => 'U4'
    //   , 'sources' => 'http initval'
    //   , 'initval' => $current_year
    //   , 'default' => $current_year
    //   , 'min' => 2012, 'max' => 2100
    //   , 'global' => 1
    //   )
    // , 'current_term' => array(
    //     'type' => 'W1'
    //   , 'sources' => 'http initval'
    //   , 'initval' => $current_term
    //   , 'pattern' => '/^[WS]$/'
    //   , 'global' => 1
    //   )
    // );

  if( $category === '_LEHRERFASSUNG' ) {

    $config_fields = array(
      'teaching_survey_open' => array(
        'type' => 'u1'
      , 'sources' => $sources
      , 'initval' => $teaching_survey_open
      , 'pattern' => '/^[01]$/'
      , 'global' => 1
      , 'auto' => 1
      )
    , 'teaching_survey_year' => array(
        'type' => 'U4'
      , 'sources' => $sources
      , 'initval' => $teaching_survey_year
      , 'default' => $current_year
      , 'min' => 2012, 'max' => 2100
      , 'global' => 1
      )
    , 'teaching_survey_term' => array(
        'type' => 'W1'
      , 'sources' => $sources
      , 'initval' => $teaching_survey_term
      , 'pattern' => '/^[WS]$/'
      , 'global' => 1
      )
    );

    $config_fields = init_fields( $config_fields, 'failsafe=0' );

    if( $action == 'save' ) {
      $flag_problems = true;
      if( isset( $config_fields['_changes'] ) ) {
        foreach( $config_fields['_changes'] as $fieldname => $raw ) {
          if( $config_fields[ $fieldname ]['value'] !== NULL ) {
            sql_update( 'leitvariable', "name=$fieldname-*", array( 'value' => $config_fields[ $fieldname ]['value'] ) );
          }
        }
      }
      reinit('reset');
      $category = '';
      break;
    }
  }

  if( isset( $boards[ $category ] ) ) {

    //  foreach( $boards as $board => $functions ) {
    $board = $category;
    $functions = $boards[ $board ];
      foreach( $functions as $function => $props ) {
        if( $function[ 0 ] == '_' ) {
          continue;
        }
        $p =& $boards[ $board ][ $function ];
        if( $p['count'] == '*' ) {
          $count = sql_offices( "board=$board,function=$function", 'single_field=COUNT' );
          init_var( "n_{$board}_{$function}", "global,type=U,sources=$sources,default=1,set_scopes=self,initval=$count" );
        } else {
          ${"n_{$board}_{$function}"} = $p['count'];
        }
        for( $rank = 1; $rank <= ${"n_{$board}_{$function}"}; $rank++ ) {
          $row = sql_offices( "board=$board,function=$function,rank=$rank", 'single_row=1,default=0' );
          if( $row ) {
            if( ! sql_person( array( 'people_id' => $row['people_id'] ), 'default=0' ) ) {
              $row['people_id'] = 0;
            }
          }
          $p[ $rank ] = init_var( "people_id_{$board}_{$function}_{$rank}", "type=u,sources=$sources,set_scopes=self,initval=".adefault( $row, 'people_id', 0 ) );
        }
        unset( $p ); // break reference
      }
    // }

    switch( $action ) {
      case 'save':
        $flag_problems = true;
  
        // foreach( $boards as $board => $functions ) {
          foreach( $functions as $function => $props ) {
            if( $function[ 0 ] == '_' ) {
              continue;
            }
            $p = $boards[ $board ][ $function ];
            for( $rank = 1; $rank <= ${"n_{$board}_{$function}"}; $rank++ ) {
              if( ( $id = $p[ $rank ]['value'] ) !== NULL ) {
                sql_save_office( $board, $function, $rank, array( 'people_id' => $id ), 'action=hard' );
              }
            }
            sql_delete_offices( "board=$board,function=$function,rank>=$rank" );
          }
        // }
        $category = '';
        reinit('reset');
        break;
  
      case 'addOffice':
        // init_var( 'board', 'global,type=W,sources=http' );
        init_var( 'function', 'global,type=W,sources=http' );
        need( isset( $boards[ $board ][ $function ] ), 'no such function' );
        $p = $boards[ $board ][ $function ];
        need( $p['count'] == '*', 'cannot add office' );
        $n = ++${"n_{$board}_{$function}"};
        init_var( "people_id_{$board}_{$function}_$n", 'type=u,sources=initval,inital=0,set_scopes=self' );
        reinit('self');
        break;
  
      case 'deleteOffice':
        // init_var( 'board', 'global,type=W,sources=http' );
        init_var( 'function', 'global,type=W,sources=http' );
        init_var( 'rank', 'global,type=U,sources=http' );
  
        need( isset( $boards[ $board ][ $function ] ), 'no such function' );
        need( $boards[ $board ][ $function ]['count'] == '*', 'cannot delete office' );
        need( ${"n_{$board}_{$function}"} >= 2, 'cannot delete office' );
        while( $rank < ${"n_{$board}_{$function}"} ) {
          mv_persistent_vars( 'self', "/^people_id_{$board}_{$function}_".($rank+1).'$/', "people_id_{$board}_{$function}_$rank" );
          $rank++;
        }
        ${"n_{$board}_{$function}"}--;
        reinit('self');
        break;
    }

  }
}


echo html_tag( 'h1', '', 'Konfiguration' );

open_table( 'menu td:smallskipt;smallskipb th:medskipt;medskipb' );

  open_tr();
    open_th( 'center larger solidtop,colspan=3', we( 'teaching survey', 'Lehrerfassung' ) );

  if( $category == '_LEHRERFASSUNG' ) {
    open_tr();
      open_td( 'right', we( 'for term:', "f{$uUML}r Semester:" ) );
      open_td( 'colspan=2', selector_term( $config_fields['teaching_survey_term'] ) . ' ' . selector_int( $config_fields['teaching_survey_year'] ) );
    open_tr();
       open_td();
       open_td( 'colspan=2', we( 'activate:', 'freischalten:' )
        . html_span( 'quads', radiobutton_element(
            $config_fields['teaching_survey_open']
          , array( 'value' => '0', 'text' => we( 'closed', 'geschlossen' ) )
          ) )
        . html_span( 'quads', radiobutton_element(
            $config_fields['teaching_survey_open']
          , array( 'value' => '1', 'text' => we( 'open', 'offen' ) )
          ) )
      );

    open_tr();
      open_td( 'right,colspan=3' );
        echo reset_button_view();
        echo inlink( '', array( 'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' ) , 'category' => '' ) );
        echo save_button_view();
  } else {
    open_tr('td:/.*skipb/medskipb/');
      open_td( 'center,colspan=2', $teaching_survey_open
        ? we('survey is open for ','Erfassung ist freigegeben für ' ) . $teaching_survey_term . $teaching_survey_year
        : we('survey is currently closed','Erfassung zur Zeit gesperrt')
      );
      open_td( 'right' );
        if( have_priv( 'config', 'write', '_LEHRERFASSUNG' ) && ! $category ) {
          echo inlink( '', array( 'class' => 'button', 'text' => we('edit','bearbeiten' ) , 'category' => '_LEHRERFASSUNG' ) );
        }
  }


  open_tr();
    open_th( 'center larger solidtop,colspan=3', 'Ämter am Institut' );

foreach( $boards as $board => $functions ) {

  if( $category == $board ) {

    open_tr();
      open_th( 'dottedtop left qquads,colspan=3', $functions['_BOARD'] );

    foreach( $functions as $function => $p ) {
      if( $function[ 0 ] == '_' ) {
        continue;
      }
//       open_tr('smallskips');
//         open_th('colspan=1');
//         open_th( 'colspan=2', $p['function'] );
//         if( $p['count'] == '*' ) {
//           open_th( 'right', inlink( '', "action=addOffice,board=$board,function=$function,class=plus,title=".we('add member','hinzufügen') ) );
//         } else {
//           open_th();
//         }
      for( $rank = 1; isset( $p[ $rank ] ); $rank++ ) {
        open_tr();
          open_td( array( 'colspan' => 1, 'class' => 'qquad right', 'label' => $p[ $rank ] ), ( $rank == 1 ) ? $p['function'] : '' );
          open_td( 'quads', selector_people( $p[ $rank ], array(
            'filters' => 'flag_publish=1,flag_deleted=0,flag_virtual=0' , 'choices' => array( '0' => we(' - vacant - ',' - vakant - ' ) )
          ) ) );
          open_td('right');
            if( ( $rank == 1 ) && ( $p['count'] == '*' ) ) {
              echo inlink( '', "action=addOffice,board=$board,function=$function,class=icon plus,title=".we('add member','hinzufügen') );
            }
            if( ( $p['count'] == '*' ) && isset( $p[ 2 ] ) ) {
              echo inlink( '', "action=deleteOffice,board=$board,function=$function,rank=$rank,class=icon drop,title=".we('remove member','entfernen') );
            }
      }
    }
    open_tr();
      open_td( 'right,colspan=3' );
        echo reset_button_view();
        echo inlink( '', array( 'class' => 'button', 'text' => we('cancel edit','Bearbeitung abbrechen' ) , 'category' => '' ) );
        echo save_button_view();

  } else {

    open_tr('th:dottedtop');
      open_th( 'left qquads,colspan=2', $functions['_BOARD'] );
      open_th( 'right' );
        if( have_priv( 'config', 'write', $board ) && ! $category ) {
          echo inlink( '', array( 'class' => 'button', 'text' => we('edit','bearbeiten' ) , 'category' => $board ) );
        }

    foreach( $functions as $function => $p ) {
      if( $function[ 0 ] == '_' ) {
        continue;
      }
//       open_tr('smallskips');
//         open_th('colspan=1');
//         open_th( 'colspan=2', $p['function'] );
//         if( $p['count'] == '*' ) {
//           open_th( 'right', inlink( '', "action=addOffice,board=$board,function=$function,class=plus,title=".we('add member','hinzufügen') ) );
//         } else {
//           open_th();
//         }
      $rows = sql_offices( "board=$board,function=$function", 'orderby=rank' );
      $count = $p['count'];
      if( $count == '*' ) {
        $count = max( 1, count( $rows ) );
      }
      for( $rank = 1; $rank <= $count; $rank++ ) {
        $row = adefault( $rows, $rank - 1, 0 );
        open_tr();
          open_td( 'colspan=1,qquad right', ( $rank == 1 ) ? $p['function'] : ' ' );
          open_td( 'qquads,colspan=2', alink_person_view( adefault( $row, 'people_id', 0 ), 'office' ) ); 
      }
    }
  }
}

close_table();



?>
