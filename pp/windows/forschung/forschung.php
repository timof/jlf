<?php // pp/windows/forschung/forschung.php


sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Research','Forschung') );


function schwerpunkt( $topic, $title, $image_view, $text, $modules = array() ) {
  open_tr('keyarea');
    open_td('textaroundphoto');
      open_span( 'floatright', $image_view );
      open_tag( 'h3', '', $title );
      open_span( 'smallskips', $text );

    open_td();

      open_tag('h3', '', we('Professors:','Professuren:') );
      $profs = sql_groups( array( 'flag_publish', 'flag_research', 'keyarea' => $topic, 'status' => GROUPS_STATUS_PROFESSOR ) );
      open_ul('plain');
      foreach( $profs as $p ) {
        open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
      }
      close_ul('plain');

      $profs = sql_groups( array( 'flag_publish', 'flag_research', 'keyarea' => $topic, 'status' => GROUPS_STATUS_SPECIAL ) );
      // kludge alert:
      if( $topic == 'astro' ) {
        $g = sql_one_group( 'acronym=astro I' );
        $g['head_people_id'] = sql_people( 'cn=achim feldmeier', 'single_field=people_id' );
        $profs[] = $g;
      }
      //
      if( $profs ) {
        open_tag('h3', '', we('Auxiliary Professors:','Außerplanmäßige Professuren:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          $p[ -1 ] = 'groups_record';
          open_li( '', alink_group_view( $p, 'fullname=1,showhead=1' ) );
        }
        close_ul();
      }

      if( ( $profs = sql_people( array( 'flag_publish', 'keyarea' => $topic, 'status' => PEOPLE_STATUS_JOINT ) ) ) ) {
        open_tag('h3', '', we('Jointly Appointed:','Gemeinsam Berufene:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          $more = '';
          // if( $p['acronym'] == 'exatp' ) {
          //   if( ( $person = sql_person( 'cn=christian stegmann', 'default=0' ) ) ) {
          //     $more = "showmore={$person['people_id']},";
          //   }
          // }
          open_li( '', alink_person_view( $p['people_id'], "fullname=1" ) );
        }
        close_ul();
      }

      if( ( $profs = sql_people( array( 'flag_publish', 'keyarea' => $topic, 'status' => PEOPLE_STATUS_HONORARY ) ) ) ) {
        open_tag('h3', '', we('Honorary Professors:','Honorarprofessuren:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          open_li( '', alink_person_view( $p['people_id'], "fullname=1" ) );
        }
        close_ul();
      }

//       if( ( $profs = sql_people( array( 'flag_publish', 'keyarea' => $topic, 'status' => PEOPLE_STATUS_EMERITUS ) ) ) ) {
//         open_tag('h3', '', we('Emeriti:','Emeritierte:') );
//         open_ul('plain');
//         foreach( $profs as $p ) {
//           open_li( '', alink_person_view( $p['people_id'], "fullname=1" ) );
//         }
//         close_ul();
//       }

      if( ( $profs = sql_groups( array( 'flag_publish', 'flag_research', 'keyarea' => $topic, 'status' => GROUPS_STATUS_EXTERNAL ) ) ) ) {
        open_tag('h3', '', we('External Professors:','Externe Professuren:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
        }
        close_ul();
      }

      if( $modules ) {
        open_tag('h3', '', we('Courses in the field of Soft Matter',"Lehrangebot im Bereich Weiche Materie") );
        open_ul('plain');
        foreach( $modules as $m => $text ) {
          open_li( '', inlink( 'modul', array( 'modul' => $m, 'class' => 'href inlink', 'text' => $text ) ) );
        }
        close_ul('plain');
      }

  close_tr();
}


echo html_tag( 'h2', 'medskips', we('Key areas and professors','Forschungsschwerpunkte und Professuren') );

require( 'pp/schwerpunkte.php' );



open_table('keyareas td:qquads;medskipt;medskipb;solidtop,colgroup=62% 38%');

  foreach( $schwerpunkte_keys as $k ) {
    $s = $schwerpunkte[ $k ];
    $modules = adefault( $s, 'modules', array() );
    schwerpunkt( $s['keyarea'], $s['title'], $s['photoview'], $s['text'], $modules );
  }

close_table();








// publications = sql_publications(
//  'year >= '.( $current_year - 1 )
//  array( 'limit_from' => 1 , 'limit_to' => 3 , 'orderby' => 'year DESC, ctime DESC' )
// ;
// f( count( $publications ) >= 2 ) {
//  echo html_tag( 'h2', 'medskips', we('Recent Research Highlights','Aktuelle Forschungshighlights') );
//  echo html_tag( 'h2','bigskipt', we('Current Publications','Aktuelle Veröffentlichungen') );
//  echo publication_columns_view( $publications );
//  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
// 


// publicationslist_view( '', 'allow_download=1,orderby=year' );

// open_div( 'medskips', inlink( 'publicationslist', 'class=href smallskipt inlink,text='.we('more publications...','weitere Veröffentlichungen..') ) );



echo html_tag( 'h2', 'medskips', we('Current topics suggested for theses',"Aktuelle Themenvorschl{$aUML}ge f{$uUML}r Abschlussarbeiten") );

$positions = sql_positions( 'groups.flag_publish' , array( 'limit_from' => 1 , 'limit_count' => 5 , 'orderby' => 'ctime DESC' ) );
$ids = array();
foreach( $positions as $p ) {
  $ids[] = $p['positions_id'];
}
init_var( 'positions_id', 'global=1,set_scopes=self,sources=http persistent' );
positionslist_view( array( 'positions_id' => $ids ) , 'insert=1,select=positions_id' );

open_div( 'medskips', inlink( 'themen', 'class=href smallskipt inlink,text='.we('more topics...','weitere Themen...') ) );


?>
