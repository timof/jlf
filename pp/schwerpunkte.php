<?php // pp/schwerpunkte.php

$schwerpunkte = array();

isset( $captionlink ) or ( $captionlink = true );

$p = sql_person( 'gn=ralf,sn=metzler', 'default=0' );
if( $p ) {
  $schwerpunkte[] = array( 'keyarea' => 'theophys'
  , 'title' => we('Theoretical and Statistical Physics','Theoretische und Statistische Physik')
  , 'photoview' => photo_view( '/pp/fotos/general_nld.png', $p['cn_notitle'], "format=url,captionlink=$captionlink" )
  , 'text' => we(
      'Many phenomena in Nature, society, or engineering exhibit complex dynamic
       behaviour, that usually cannot be described by first principles approaches.
       
       Our interdisciplinary research groups apply multiple techniques to such
       phenomena including statistical physics, stochasticity, and non-linear
       approaches. We describe systems over many time and length scales. Thus, we
       study the dynamics of nanoscopic materials (for instance, quantum dots or
       biological membranes) up to geophysical scales (atmosphere, groundwater) and
       astrophysical systems (Saturn). We are also interested in living systems
       ranging from biophysical processes of living cells to human motion patterns.
  
     ', 'Viele Phänomene in Natur, Technik und Gesellschaft zeigen ein komplexes dynamisches
         Verhalten, das nicht aus fundamentalen physikalischen Prinzipien ableitbar ist.
         
         Zur Beschreibung derartiger chaotischer Phänomene kombinieren
         unsere interdisziplinär kooperierenden Arbeitsgruppen ganz unterschiedliche
         Techniken unter anderen aus der Statistischen Physik, der nichtlinearen Physik und
         der Stochastik.
  
         Damit beschreiben wir Systeme auf sehr unterschiedlichen Zeit- und Längenskalen: von
         nanoskopischen Materialien (etwa Quantenpunkte oder biologische Membranen)
         über geophysikalische Skalen (Atmosphäre, Grundwasser) bis hin zu
         Systemen der Astrophysik (wie das Ringsystem des Saturn).
         Insbesondere befassen wir uns auch mit lebenden Systemen, von den Prozessen
         einer einzelenen Zelle bis hin zu menschlichen Bewegungsmustern.
    ')
  );
}

$p = sql_person( 'gn=dieter,sn=neher', 'default=0' );
if( $p ) {
  $schwerpunkte[] = array( 'keyarea' => 'softmatter'
  , 'title' => we('Soft Matter Phycis','Physik Weicher Materie')
  , 'photoview' => photo_view( '/pp/fotos/pwm.gif', $p['cn_notitle'], "format=url,captionlink=$captionlink" )
  , 'text' => "Die Erforschung der Struktur und der Eigenschaften weicher Materie
       (Soft Matter) ist eine der aktivsten Forschungsrichtungen der
       Physik kondensierter Materie. Diese molekularen Materialsysteme sind häufig
       nur durch schwache Kräfte wie z.B.
       van-der-Waals Wechsel{$SHY}wirkungen
       oder Wasserstoffbrücken{$SHY}bindungen gebunden. Daraus ergibt sich eine
       hohe Vielfalt an Strukturen, wie sie in den klassischen, kovalent
       gebundenen Materialien wie Metallen und anorganischen Halbleitern
       nicht zu finden ist. Das Verständnis von Struktur und Funktion
       gezielt hergestellter weicher Materie daher ist eine der zentralen
       Herausforderungen dieses modernen Forschungsgebietes und bildet den
       Schwerpunkt der Arbeiten des Forschungsschwerpunkts am Institut für
       Physik. "
  // , 'modules' => array(
  //     '541a' => "Modul 541a: Fachspezialisierung: Physik kondensierter Systeme"
  //   , '741a' => "Modul 741a: Vertiefungsgebiet: Physik kondensierter Systeme"
  //   )
  );
}
    
//  Am Institut für Physik und Astronomie existieren verschiedene Arbeitsgruppen,
//  die sich mit astrophysikalischen Themen beschäftigen.
$p = sql_person( 'gn=philipp,sn=richter', 'default=0' );
if( $p ) {
  $schwerpunkte[] = array( 'keyarea' => 'astro'
  , 'title' => we('Astrophysics','Astrophysik')
  , 'photoview' => photo_view( '/pp/fotos/astrophysik.jpg', $p['cn_notitle'], "format=url,captionlink=$captionlink" )
  , 'text' => "
    Mehrere Arbeitsgruppen am Institut für Physik und Astronomie befassen sich
    mit astrophysikalischen Themen:
    In der stellaren
    Astrophysik liegt der Forschungs-Schwerpunkt auf dem Gebiet der massereichen
    Sterne und deren Sternwinde. Für die Untersuchungen mithilfe der
    Spektralanalyse werden Beobachtungen mit internationalen Großteleskopen
    durchgeführt und mit aufwendigen Computersimulationen verglichen. In der
    galaktischen und extragalaktischen Astrophysik geht es um die Erforschung der
    diffusen Gaskomponente im Universum. Mit Hilfe spektroskopischer Untersuchungen
    und numerischer Simulationen werden die physikalischen Bedingungen im
    interstellaren und intergalaktischen Medium und die Rolle dieses Gases für die
    Entwicklung von Galaxien untersucht. Im Bereich der Planetologie werden mit den
    Methoden der statistischen Physik und der Hydrodynamik die Eigenschaften
    planetarischer und stellarer Staubscheiben erforscht. Dabei werden auch
    Beobachtungsdaten aktueller Raumfahrtmissionen wissenschaftlich ausgewertet.
    Die Astroteilchenphysik schließlich widmet sich den teilchenphysikalischen
    Aspekten kosmischer Objekte. Sowohl mit theoretischen Methoden als auch mit
    Beobachtungsdaten werden die Herkunft der kosmischen Strahlung und die ihr
    zugrunde liegende Physik untersucht. Der Forschungsbereich Astrophysik am
    Institut zeichnet sich durch eine besonders intensive Vernetzung mit den
    verschiedenen außeruniversitären Instituten aus.
  "
  );
}

$p = sql_person( 'gn=ralf,sn=menzel', 'default=0' );
if( $p ) {
  $schwerpunkte[] = array( 'keyarea' => 'photonik'
  , 'title' => we('Photonics','Photonik')
  , 'photoview' => photo_view( '/pp/fotos/photonik1.gif', $p['cn_notitle'], "format=url,captionlink=$captionlink" )
  , 'text' => "
        Der Forschungsschwerpunkt  Photonik/Quantenoptik an der Universität
        Potsdam
        konzentriert sich auf Fragen zum Verständnis der Physik des Lichts und
        seiner Wechselwirkung mit Materie. Von besonderem Interesse sind die dabei
        wirksam werdenden Quanteneigenschaften: merkwürdige Phänomene mit
        teils überraschenden Konsequenzen, etwa in der Quanteninformation.
  
        In der Anwendung spiegelt sich das wider in der Realisierung besonderer
        Lichtquellen, die es erlauben bekannte physikalische Grenzen zu
        überwinden.
  
        Nichtlineare optische Methoden werden sowohl in der spektroskopischen Untersuchung und
        Charakterisierung von Stoffen als auch in der Anwendung bei der Realisierung neuartiger
        Lichtquellen eingesetzt. Die theoretischen und praktischen Arbeiten beziehen
        sich dabei auf die Bereiche ultrakalte Gase, Nano-Optik, Quanteninformationsverarbeitung,
        Nichtlineare Spektroskopie, auf die Lasertechnik sowie die optische Messtechnik.
  
        Im Ergebnis der Forschung finden
        neuartige Konzepte ihre praktische Anwendung
        z.B. in der Medizintechnik, Informationstechnologie, Lasertechnik sowie  weiteren
        analytischen Verfahren für die Lebenswissenschaften.
      "
  );
}
    
$p = sql_person( 'gn=andreas,sn=borowski', 'default=0' );
if( $p ) {
  $schwerpunkte[] = array( 'keyarea' => 'didaktik'
  , 'title' => we('Physics Education','Didaktik der Physik')
  , 'photoview' => photo_view( '/pp/fotos/didaktik.jpg', $p['cn_notitle'], "format=url,captionlink=$captionlink" )
  , 'text' => "
        Die fachdidaktische Forschung an der Universität Potsdam beshäftigt sich
        sowohl mit der Entwicklung von neuen Inhalten für den Unterricht, als auch mit
        empirisch-fachdidaktischer Grundlagenforschung. Bei der Entwicklung von neuen
        Inhalten geht es zum einen um die Entwicklung und Einbindung von Experimenten
        mit Smartphones in den Schul-Unterricht und in das Lehramts-Studium. Zum
        anderen werden neue Inhaltsfelder wie z. B. die Teilchenphysik für den
        Unterricht erschlossen. Im Bereich der fachdidaktischen Grundlagenforschung
        beschäftigen wir uns mit der Modellierung und Erfassung physikalischer
        Kompetenz in verschiedenen Stadien der Ausbildung an Schule und Hochschule
        unter besonderer Berücksichtigung der mathematischen Anforderungen in der
        Physik. 
  "
  );
}


init_var( 'keyareakeys', 'global,type=a,source=self,set_scopes=self,default=' );
$schwerpunkte_keys = explode( ',', $keyareakeys );
if( $keyareakeys && ( count( $schwerpunkte_keys ) == count( $schwerpunkte ) ) ) {
  // nop
} else {
  $schwerpunkte_keys = array_keys( $schwerpunkte );
  shuffle( $schwerpunkte_keys );
  $keyareakeys = implode( ',', $schwerpunkte_keys );
}
?>
