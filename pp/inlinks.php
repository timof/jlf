<?php


// $sidenav: defines tree-structure of main menu
//
$sidenav_map = array(
  'menu' => 1
, 'aktuelles' => array( 'menu' => 1, 'childs' => array(
      'termine' => 1
    , 'veranstaltungen' => 1
  ) )
, 'institut' => array( 'menu' => 1, 'childs' => array(
      'institutsrat' => 1
    , 'pruefungsausschuss' => 1
    , 'impressum' => 1
  ) )
, 'mitarbeiter' => array( 'menu' => 1, 'childs' => array(
    'visitenkarte' => 0
  ) )
// , 'professuren' => array( 'menu' => 1, 'childs' => array(
//     'gemberufene' => 1
//   , 'aplprofs' => 1
//   ) )
, 'forschung' => array( 'menu' => 1, 'childs' => array(
//      'schwerpunkte' => array( 'menu' => 1, 'childs' => array(
//        'photonik' => 1
//      , 'astro' => 1
//      , 'nld' => 1
//      , 'softmatter' => 1
//      , 'didaktik' => 1
//      ) )
    'gemberufene' => 1
  , 'aplprofs' => 1
//    , 'gruppen' => array( 'menu' => 1, 'childs' => array(
//        'gruppe' => 0
//      ) )
   , 'publikationen' => array( 'menu' => 1, 'childs' => array(
        'publikation' => 0
      ) )
   , 'stellen' => array( 'menu' => 1, 'childs' => array(
        'stelle' => 0
      ) )
  ) )
, 'lehre' => array( 'menu' => 1, 'childs' => array(
    'monobachelor' => 1
  , 'lehramt' => 1
  , 'master' => 1
  , 'diplom' => 1
  , 'studierendenvertretung' => 1
  ) )
, 'links' => 1
);



// script_defaults: define default parameters and default options for views:
//
function script_defaults( $target_script ) {
  // for the public pages, we don't need most of the functionality here:
  // - we don't open new windows here (yet), so $options and $parameters['window'] are pretty meaningless
  // - 
  $options = array();
  $parameters = array(
    'window' => 'menu'
  , 'script' => $target_script
  , 'class'=> 'href inlink'
  );

  switch( strtolower( $target_script ) ) {
    //
    // Anzeige im Hauptfenster (aus dem Hauptmenue) oder in "grossem" Fenster moeglich:
    //
    case 'menu':
    case 'main':
    case 'index':
      $parameters['text'] = we('Home','Start');
      $parameters['title'] = ''; // we('Start page','Startseite');
      $parameters['script'] = 'menu';
      $file = 'menu/menu.php';
      break;
    case 'aktuelles':
      $parameters['text'] = we('News','Aktuelles');
      $parameters['title'] = we('News','Aktuelles');
      $file = 'aktuelles/aktuelles.php';
      break;
    case 'termine':
      $parameters['text'] = we('Dates','Termine');
      $parameters['title'] = we('Dates','Termine');
      $file = 'aktuelles/termine.php';
      break;
    case 'veranstaltungen':
      $parameters['text'] = we('Events','Veranstaltungen');
      $parameters['title'] = we('Events','Veranstaltungen');
      $file = 'aktuelles/veranstaltungen.php';
      break;
    case 'institut':
      $parameters['text'] = we('Institute','Institut');
      $parameters['title'] = we('Institute','Institut');
      $file = 'institut/institut.php';
      break;
    case 'institutsrat':
      $parameters['text'] = we('Institute board','Institutsrat');
      $parameters['title'] = we('Institute board','Institutsrat');
      $file = 'institut/irat.php';
      break;
    case 'impressum':
      $parameters['text'] = we('Impressum','Impressum');
      $parameters['title'] = we('Impressum','Impressum');
      $file = 'institut/impressum.php';
      break;
    case 'gruppen':
      $parameters['title'] = we('Groups','Gruppen und Struktureinheiten');
      $parameters['text'] = we('Groups','Gruppen');
      $file = 'forschung/gruppen.php';
      break;
    case 'gruppe':
    case 'group_view': // used in /shared/views.php
      $parameters['text'] = we('Details on Group','Details zur Gruppe');
      $parameters['title'] = '';
      $file = 'forschung/gruppe.php';
      $parameters['script'] = 'gruppe';
      break;
    case 'mitarbeiter':
      $parameters['text'] = we('People','Mitarbeiter');
      $parameters['title'] = we('People','Mitarbeiter');
      $file = 'mitarbeiter/mitarbeiter.php';
      break;
    case 'visitenkarte':
    case 'person_view': // used in /shared/views.php
      $parameters['text'] = we('Details on Person','Details zur Person');
      $parameters['title'] = '';
      $file = 'mitarbeiter/visitenkarte.php';
      $parameters['script'] = 'visitenkarte';
      break;
    case 'professuren':
      $parameters['text'] = we('Professors','Professuren');
      $parameters['title'] = we('Professors','Professuren am Institut');
      $parameters['function'] = 'full';
      $file = 'professuren/professuren.php';
      break;
    case 'gemberufene':
      $parameters['text'] = we('jointly appointed','Gemeinsam berufene');
      $parameters['title'] = we('jointly appointed professors','Gemeinsam berufene Professuren');
      $parameters['function'] = 'joint';
      $file = 'professuren/professuren.php';
      break;
    case 'aplprofs':
      $parameters['text'] = we('by special appointment','außerplanmäßige Professuren und Privatdozenten');
      $parameters['title'] = we('professors by special appointment','Außerplanmäßige Professuren und Privatdozenten');
      $parameters['function'] = 'special';
      $file = 'professuren/professuren.php';
      break;

    case 'forschung':
      $parameters['text'] = we('Research','Forschung');
      $parameters['title'] = we('research','Forschung');
      $file = 'forschung/forschung.php';
      break;
    case 'schwerpunkte':
      $parameters['text'] = we('Key areas','Schwerpunkte');
      $parameters['title'] = we('Key areas','Schwerpunkte');
      $file = 'forschung/schwerpunkte.php';
      break;
    case 'astro':
      $parameters['text'] = we('Astro physics','Astrophysik');
      $parameters['title'] = we('Astro physics','Astrophysik');
      $file = 'forschung/astro.php';
      break;
    case 'photonik':
      $parameters['text'] = we('Photonics','Photonik');
      $parameters['title'] = we('Photonics','Photonik');
      $file = 'forschung/photonik.php';
      break;
    case 'didaktik':
      $parameters['text'] = we('Physics Education','Didaktik der Physik');
      $parameters['title'] = we('Physics Education','Didaktik der Physik');
      $file = 'forschung/didaktik.php';
      break;
    case 'softmatter':
      $parameters['text'] = we('Soft Matter','Weiche Materie');
      $parameters['title'] = we('Soft Matter','Weiche Materie');
      $file = 'forschung/softmatter.php';
      break;
    case 'nld':
      $parameters['text'] = we('Nonlinear Dymamics','Nichtlineare Dynamik');
      $parameters['title'] = we('Nonlinear Dymamics','Nichtlineare Dynamik');
      $file = 'forschung/nld.php';
      break;
    case 'publikationen':
      $parameters['text'] = we('Publications','Veröffentlichungen');
      $parameters['title'] = we('Publications','Veröffentlichungen');
      $file = 'forschung/publikationen.php';
      break;
    case 'publikation':
    case 'publication_view':
      $parameters['text'] = we('Publication','Artikel');
      $parameters['title'] = we('Publications','Artikel');
      $file = 'forschung/publikation.php';
      $parameters['script'] = 'publikation';
      break;
    case 'stellen':
      $parameters['text'] = we('Topics','Themenvorschlaege');
      $parameters['title'] = we('Topics','Themenvorschlaege');
      $file = 'forschung/stellen.php';
      break;

    case 'lehre':
      $parameters['text'] = we('Studies','Lehre');
      $parameters['title'] = we('studies','Lehre');
      $file = 'lehre/lehre.php';
      break;
    case 'monobachelor':
      $parameters['text'] = 'BSc';
      $parameters['title'] = we('bachelor programme','Bachelorstudium');
      $file = 'lehre/monobachelor.php';
      break;
    case 'themenBachelor':
      $parameters['text'] = we('suggested topics','Themenvorschlaege Bachelor');
      $parameters['title'] = we('suggested topics for bachelor theses','Themenvorschlaege fuer Bachelorarbeiten');
      $parameters['programme'] = PROGRAMME_BSC;
      $file = 'lehre/stellen.php';
      break;
    case 'master':
      $parameters['text'] = 'Msc';
      $parameters['title'] = we('master programme','Masterstudium');
      $file = 'lehre/master.php';
      break;
    case 'themenMaster':
      $parameters['text'] = we('suggested topics','Themenvorschlaege Master');
      $parameters['title'] = we('suggested topics for master theses','Themenvorschlaege fuer Masterarbeiten');
      $parameters['programme'] = PROGRAMME_MASTER;
      $file = 'lehre/stellen.php';
      break;
    case 'lehramt':
      $parameters['text'] = 'BEd / MEd';
      $parameters['title'] = we('BEd / MEd programme','Lehramtsstudium');
      $file = 'lehre/lehramt.php';
      break;
    case 'diplom':
      $parameters['text'] = we('diploma programme','Diplomstudium');
      $parameters['title'] = we('diploma programme','Diplomstudium');
      $file = 'lehre/diplom.php';
      break;
    case 'studierendenvertretung':
      $parameters['text'] = we('student representation','Studierendenvertretung');
      $parameters['title'] = we('student representation','Studierendenvertretung');
      $file = 'lehre/studierendenvertretung.php';
      break;
    case 'prueferDiplom':
      $parameters['text'] = we('examiners','Prüfer');
      $parameters['title'] = we('list of examiners (diploma programme)','Prüferverzeichnis (Diplom)');
      $file = 'lehre/pruefer.diplom.php';
      break;
    case 'prueferBachelor':
      $parameters['text'] = we('examiners','Prüfer');
      $parameters['title'] = we('list of examiners (BSc)','Prüferverzeichnis (BSc)');
      $file = 'lehre/pruefer.bachelor.php';
      break;
    case 'pruefungsausschuss':
      $parameters['text'] = we('examination board','Prüfungsausschuss');
      $parameters['title'] = we('examination board','Prüfungsausschuss');
      $file = 'institut/pruefungsausschuss.php';
      break;
    case 'praktika':
      $parameters['text'] = we('lab courses','Praktika');
      $parameters['title'] = we('lab courses','Praktika');
      $file = 'lehre/praktika.php';
      break;

    case 'links':
      $parameters['text'] = we('links','Links');
      $parameters['title'] = we('links','Links');
      $file = 'links/links.php';
      break;

    case 'eventslist':
      $parameters['script'] = 'eventslist';
      $parameters['text'] = we('Events','Veranstaltungen');
      $parameters['title'] = we('Events','Veranstaltungen');
      break;
    case 'examslist':
      $parameters['script'] = 'examslist';
      $parameters['text'] = we('Examination dates','Prüfungstermine');
      $parameters['title'] = we('Examination dates','Prüfungstermine');
      break;
    case 'positionslist':
      $parameters['script'] = 'positionslist';
      $parameters['text'] = we('Positions & Topics','Stellen & Themen');
      $parameters['title'] = we('open positions & topics for theses','offene Stellen und Themen f&uuml;r Ba/Ma/PhD-Arbeiten');
      $file = 'forschung/positionslist.php';
      break;
    case 'publicationslist':
      $parameters['script'] = 'publicationslist';
      $parameters['text'] = we('Publications','Veröffntlichungen');
      $parameters['title'] = we('Publications','Veröffentlichungen');
      $file = 'forschung/publicationslist.php';
      break;
    //
    default:
      // logger( "unexpected target script: [$target_script]", LOG_LEVEL_ERROR, LOG_FLAG_CODE | LOG_FLAG_INPUT, 'links' );
      return NULL;
  }
  return array( 'parameters' => $parameters, 'options' => $options, 'file' => $file );
}

$cgi_get_vars = array(
  'function' => array( 'type' => 'W32', 'persistent' => 'url' )
, 'people_id' => array( 'type' => 'u6', 'persistent' => 'url' )
, 'groups_id' => array( 'type' => 'u6', 'persistent' => 'url' )
, 'exams_id' => array( 'type' => 'u' )
, 'teaching_id' => array( 'type' => 'u' )
, 'positions_id' => array( 'type' => 'u' )
, 'publications_id' => array( 'type' => 'u', 'persistent' => 'url' )
, 'degree_id' => array( 'type' => 'u' )
, 'programme_id' => array( 'type' => 'u' )
, 'item' => array( 'type' => 'w' )
, 'term' => array( 'type' => 'W1', 'pattern' => '/^[WS0]$/', 'default' => '0' )
, 'id' => array( 'type' => 'u', 'persistent' => 'url' )
, 'year' => array( 'type' => 'u4', 'format' => '%04u' )
, 'month' => array( 'type' => 'u2', 'format' => '%02u' )
, 'day' => array( 'type' => 'u2', 'format' => '%02u' )
);

$jlf_cgi_vars = array(
  'hour' => array( 'type' => 'u2', 'format' => '%02u' )
, 'minute' => array( 'type' => 'u2', 'format' => '%02u' )
);

function inlink( $target = '', $parameters = array(), $opts = array() ) {
  global $script, $global_format, $pseudo_parameters, $H_SQ, $jlf_persistent_vars;

  $parameters = parameters_explode( $parameters );
  $opts = parameters_explode( $opts );
  
  if( $global_format !== 'html' ) {
    // \href makes no sense for (deep) inlinks - and neither should it look like a link if it isn't one:
    return adefault( $parameters, 'text', ' - ' );
  }
  $self = 0;
  if( ! $target ) {
    $target = $script;
    $self = 1;
  }

  $context = adefault( $parameters, 'context', 'a' );
  $inactive = adefault( $parameters, 'inactive', false );
  $inactive = adefault( $inactive, 'problems', $inactive );
  $confirm = '';
  $js = '';
  $url = '';

  if( $target[ 0 ] === '!' ) {
    $form_id = substr( $target, 1 );
    if( ! $form_id ) {
      $form_id = 'update_form';
    }
    $r = array();
    $l = '';
    foreach( $parameters as $key => $val ) {
      if( in_array( $key, $pseudo_parameters ) ) {
        continue;
      }
      if( ( $key == 'login' ) || ( $key == 'l' ) ) {
        $l = $val;
      } else {
        $r[ $key ] = bin2hex( $val );
      }
    }

    $s = parameters_implode( $r );
    // debug( $s, 's' );
    $js = $inactive ? 'true;' : "submit_form( {$H_SQ}$form_id{$H_SQ}, {$H_SQ}$s{$H_SQ}, {$H_SQ}$l{$H_SQ} ); ";

  } else {

    $script_defaults = script_defaults( $target );
    if( ! $script_defaults ) {
      need( $context === 'a', "broken link in context [$context]" );
      return html_tag( 'img', array( 'class' => 'icon brokenlink', 'src' => 'img/broken.tiny.trans.gif', 'title' => "broken: $target" ), NULL );
    }

    // force canonical script name:
    $target = $script_defaults['parameters']['script'];

    if( $self ) {
      $parameters = array_merge( $jlf_persistent_vars['url'], $parameters );
    }
    $parameters = array_merge( $script_defaults['parameters'], $parameters );
    $parameters['m'] = $target;
    $url = get_internal_url( $parameters );

  }

  switch( $context ) {
    case 'a':
      $attr = array();
      $baseclass = array( 'a', 'inlink' ); // basic type - should always apply
      $linkclass = 'href';                 // look of the link - default, may be changed
      foreach( $parameters as $a => $val ) {
        switch( $a ) {
          case 'title':
          case 'text':
          case 'img':
          case 'id':
            $attr[ $a ] = $val;
            break;
          case 'class':
            $linkclass = $val;
            break;
          case 'display':
            $attr['style'] = "display:$val;";
            break;
        }
      }
      if( $inactive ) {
        $baseclass[] = 'inactive';
        $attr['class'] = merge_classes( $baseclass, $linkclass );
        if( isarray( $inactive ) ) {
          $inactive = implode( ' / ', $inactive );
        }
        if( isstring( $inactive ) && ! isnumber( $inactive ) ) {
          $attr['title'] = ( ( strlen( $inactive ) > 80 ) ? substr( $inactive, 0, 72 ) .'...' : $inactive );
        }
        $text = adefault( $attr, 'text', '' );
        unset( $attr['text'] );
        return html_span( $attr, $text );
      } else {
        $attr['class'] = merge_classes( $baseclass, $linkclass );
        return html_alink( $js ? "javascript: $js" : $url , $attr );
      }
    case 'url':
      need( $url, 'inlink(): no plain url available' );
      return $url;
    case 'js':
      if( ! $js ) {
        $js = "load_url( {$H_SQ}$url{$H_SQ} );";
      }
      return ( $inactive ? 'true;' : "$confirm $js" );
    case 'form':
      need( $url, 'inlink(): need plain url in context form' );
      $r = array( 'target' => '', 'action' => '#', 'onsubmit' => '', 'onclick' => '' );
      if( $inactive ) {
        return $r;
      }
      need( $form_id = adefault( $parameters, 'form_id', false ), 'context form requires parameter form_id' );
      $r['action'] = $url;
      return $r;
    default:
      error( 'undefined context: [$context]', LOG_FLAG_CODE, 'links' );
  }

}

?>
