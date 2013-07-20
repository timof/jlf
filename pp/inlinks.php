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
, 'professuren' => array( 'menu' => 1, 'childs' => array(
    'gemberufene' => 1
  , 'aplprofs' => 1
  ) )
, 'forschung' => array( 'menu' => 1, 'childs' => array(
     'schwerpunkte' => array( 'menu' => 1, 'childs' => array(
       'photonik' => 1
     , 'astro' => 1
     , 'nld' => 1
     , 'softmatter' => 1
     , 'didaktik' => 1
     ) )
    , 'gruppen' => array( 'menu' => 1, 'childs' => array(
        'gruppe' => 0
      ) )
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
  ) )
, 'links' => 1
);



// script_defaults: define default parameters and default options for views:
//
function script_defaults( $target_script, $enforced_target_window = '', $target_thread = 1 ) {
  global $large_window_options, $small_window_options;

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
      $parameters['text'] = we('Details on Group','Details zur Gruppe');
      $parameters['title'] = '';
      $file = 'forschung/gruppe.php';
      break;
    case 'mitarbeiter':
      $parameters['text'] = we('People','Mitarbeiter');
      $parameters['title'] = we('People','Mitarbeiter');
      $file = 'mitarbeiter/mitarbeiter.php';
      break;
    case 'visitenkarte':
      $parameters['text'] = we('Details on Person','Details zur Person');
      $parameters['title'] = '';
      $file = 'mitarbeiter/visitenkarte.php';
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
      $parameters['text'] = we('Publication','Artikel');
      $parameters['title'] = we('Publications','Artikel');
      $file = 'forschung/publikation.php';
      break;
    case 'stellen':
      $parameters['text'] = we('Open positions','Offene Stellen');
      $parameters['title'] = we('Open positions','Offene Stellen');
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
  'p' => array( 'type' => 'u', 'persistent' => 'url', 'pattern' => '/^\d{1,6}$/' )
, 'g' => array( 'type' => 'u', 'persistent' => 'url', 'pattern' => '/^\d{1,6}$/' )
, 'function' => array( 'type' => 'W32', 'persistent' => 'url' )
, 'people_id' => array( 'type' => 'u' )
, 'groups_id' => array( 'type' => 'u' )
, 'exams_id' => array( 'type' => 'u' )
, 'teaching_id' => array( 'type' => 'u' )
, 'positions_id' => array( 'type' => 'u' )
, 'publications_id' => array( 'type' => 'u' )
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

?>
