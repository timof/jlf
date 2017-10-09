<?php // pp/windows/forschung/forschung.php


sql_transaction_boundary('*');


open_div('id=teaser');
  open_div( array( 'class' => 'overlay init', 'id' => 'i0' ) );
    echo image('forschung');
    echo html_tag( 'h1', '', we('Research','Forschung am Institut') );
  close_div();
close_div();

// echo html_tag( 'h1', '', we('Research','Forschung') );


function schwerpunkt( $topic, $title, $image_view, $text, $modules = array() ) {
  static $topstyle = '';
  open_div("keyarea $topstyle");
    $topstyle = 'solidtop';

    open_div('textaroundphoto top');
      open_div( 'illu', $image_view );
      open_tag( 'h3', '', $title );
      open_span( 'smallskips', $text );
    close_div();

    open_div('smalllistbox top');

      open_tag('h4', '', we('groups and professors:','Arbeitsgruppen und Professuren:') );
      $profs = array_merge(
        sql_groups( array( 'flag_publish', 'flag_research', 'keyarea' => $topic, 'status' => GROUPS_STATUS_PROFESSOR ), array( 'orderby' => 'head_people_id=0, head_sn, head_gn' ) )
      , sql_groups( array( 'flag_publish', 'flag_research', 'keyarea' => $topic, 'status' => GROUPS_STATUS_SPECIAL ), array( 'orderby' => 'head_sn, head_gn' ) )
      , sql_groups( array( 'flag_publish', 'flag_research', 'keyarea' => $topic, 'status' => GROUPS_STATUS_OTHER ), array( 'orderby' => 'head_sn, head_gn' ) )
      );
      if( $topic == 'astro' ) {
        $g = sql_one_group( 'acronym=astro I', 'default=0' );
        $p_id = sql_people( 'cn=achim feldmeier', 'single_field=people_id,default=0' );
        if( $g && $p_id ) {
          $g['head_people_id'] = $p_id;
          $g['head_gn'] = 'Achim';
          $g['head_sn'] = 'Feldmeier';
          $g['status'] = GROUPS_STATUS_SPECIAL;
          $profs[] = $g;
        }
      }
      if( $topic == 'photonik' ) {
        $g = sql_one_group( 'acronym=quanten', 'default=0' );
        $p_id = sql_people( 'cn=carsten henkel', 'single_field=people_id,default=0' );
        if( $g && $p_id ) {
          $g['head_people_id'] = $p_id;
          $g['head_gn'] = 'Carsten';
          $g['head_sn'] = 'Henkel';
          $g['status'] = GROUPS_STATUS_SPECIAL;
          $profs[] = $g;
        }
      }
      if( $profs ) {
        open_ul('plain');
        foreach( $profs as $p ) {
          $t = '';
          if( ( $head_people_id = $p['head_people_id'] ) ) {
            $t .= "{$p['head_gn']} {$p['head_sn']}";
          } else {
            $t .= we( ' - vacant - ', ' - vakant - ' );
          }
          $t .= ' (';
          if( $p['status'] == GROUPS_STATUS_SPECIAL ) {
            $t .= 'apl., ';
          }
          $t .= $p['cn'];
          $t .= ')';
          $p[ -1 ] = 'groups_record';
          open_li( 'oneline', alink_group_view( $p, array( 'text' => $t ) ) );
        }
        close_ul('plain');
      }

      $profs = sql_people( array( 'flag_publish', 'keyarea' => $topic, 'status' => array( PEOPLE_STATUS_JOINT, PEOPLE_STATUS_HONORARY, PEOPLE_STATUS_EMERITUS, PEOPLE_STATUS_EXTERNAL ) )
                         , array( 'orderby' => 'sn, gn' )
      );
      if( $profs ) {
        open_ul('plain bigskipt');
        foreach( $profs as $p ) {
          $p[ -1 ] = 'people_record';
          $cn = "{$p['gn']} {$p['sn']}";
          $t = $cn;
          switch( $p['status'] ) {
            case PEOPLE_STATUS_JOINT:
            case PEOPLE_STATUS_HONORARY:
              if( $p['affiliation_acronym'] ) {
                $s = " ({$p['affiliation_acronym']})";
                if( $p['affiliation_cn'] ) {
                  $s = html_tag( 'abbr', array( 'title' => $p['affiliation_cn'] ), $s );
                }
                $t .= $s;
              } else if( $p['affiliation_cn'] ) {
                $t .= " ({$p['affiliation_cn']})";
              }
              break;
            case PEOPLE_STATUS_EMERITUS:
              $t .= ' (em.)';
              break;
          }
          open_li( 'oneline', alink_person_view( $p['people_id'], array( 'text' => $t, 'title' => $cn ) ) );
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

    close_div();

    open_div( 'clear', '' );
  close_div();
}


open_ccbox( '', we('Key areas and professors','Forschungsschwerpunkte und Professuren') );

$captionlink = true;
require( 'pp/schwerpunkte.php' );

// open_table('keyareas td:qquads;medskipt;medskipb,colgroup=62% 38%');

  foreach( $schwerpunkte_keys as $k ) {
    $s = $schwerpunkte[ $k ];
    $modules = adefault( $s, 'modules', array() );
    schwerpunkt( $s['keyarea'], $s['title'], $s['photoview'], $s['text'], $modules );
  }

// close_table();

close_ccbox();







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



// echo html_tag( 'h2', 'medskips', we('Current topics suggested for theses',"Aktuelle Themenvorschl{$aUML}ge f{$uUML}r Abschlussarbeiten") );
// 
// $positions = sql_positions( 'groups.flag_publish' , array( 'limit_from' => 1 , 'limit_count' => 5 , 'orderby' => 'ctime DESC' ) );
// $ids = array();
// foreach( $positions as $p ) {
//   $ids[] = $p['positions_id'];
// }
// init_var( 'positions_id', 'global=1,set_scopes=self,sources=http persistent' );
// positionslist_view( array( 'positions_id' => $ids ) , 'insert=1,select=positions_id' );
// 
// open_div( 'medskips', inlink( 'themen', 'class=href smallskipt inlink,text='.we('more topics...','weitere Themen...') ) );
// 

?>
