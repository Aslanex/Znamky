<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  if(!$_POST['bakawebUrl'] || !$_POST['bakawebPrihlJmeno'] || !$_POST['bakawebHeslo']) exit('retarded');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT pocet FROM bany WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"')->fetch_assoc()['pocet']>3) {
    $json['state']='tooManyUserAttempts';
    exit(json_encode($json));
    }
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bany WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"')->fetch_assoc()['COUNT(*)']<1)
    $funkce->mysqli->query('INSERT INTO bany (ip,pocet) VALUES ("'.$_SERVER['REMOTE_ADDR'].'",1)');
  else $funkce->mysqli->query('UPDATE bany SET pocet=pocet+1 WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"');

  $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','CURLCOOKIE');
  $ch=curl_init($_POST['bakawebUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  curl_exec($ch);                      // první návštěva pro cookie

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_POST,true);
  $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&ctl00%24cphmain%24TextBoxjmeno='.$_POST['bakawebPrihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$_POST['bakawebHeslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
  $html=curl_exec($ch);                // přihlášení
  if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=302) {
    if(strpos($html,'Bylo provedeno příliš mnoho neúspěšných pokusů o přihlášení')) $json['state']='tooManyAttempts';
    elseif(strpos($html,'Přihlášení neproběhlo v pořádku')) $json['state']='loginError';
    else $json['state']='unknownError';
    exit(json_encode($json));
    }

  curl_setopt($ch,CURLOPT_POST,false);
  curl_setopt($ch,CURLOPT_POSTFIELDS,null);
  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'uvod.aspx');
  curl_exec($ch);                      // návštěva úvodu pro obranný mechanismus

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'uvod.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'prehled.aspx?s=2');
  $bodyZnamky=new DOMDocument(); $bodyPololeti=new DOMDocument(); $json['predmety']=array();
  $html=curl_exec($ch);                // zobrazení známek
  @$bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $json['jmeno']=trim($funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);
  $tableZnamky=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'radekznamky')[0];
  $predmety=$tableZnamky->getElementsByTagName('tr');
  if($predmety->length<1) $json['content']='<div id="znamky">Vypadá to, že nemáš žádné známky.</div>';
  else {
    curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'prehled.aspx?s=2');
    curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'prehled.aspx?s=4');
    $html=curl_exec($ch);                // pololetní známky
    $bodyPololeti->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $tablePololeti=$funkce->getElementsByClassName($bodyPololeti->getElementsByTagName('table'),'tablepolo')[0];
    $predmetyPololeti=$tablePololeti->getElementsByTagName('tr');
    foreach($predmetyPololeti as $predmet) {
      if(!$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]) continue;
      $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'poloznamka');
      $znamkyPololeti[trim($funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]->nodeValue)]=$znamky[(sizeof($znamky)-1)]->nodeValue;
      }

    $pololeti=(date('n')<1 || date('n')>7) ? 1 : 2;
    $zacatekPololeti=(date('n')==1) ? mktime(0,0,0,9,1,date('Y')-1) : ($pololeti==1) ? mktime(0,0,0,9,1) : mktime(0,0,0,2,1);
//    $json['timelineSize']=($pololeti==2) ? (date('n')-1)*150 : (date('n')==1) ? 750 : (date('n')-8)*150;
    $json['content']='<div id="znamky">';
    foreach($predmety as $predC=>$predmet) {     // výpis předmětů
      if(sizeof($funkce->getElementsByClassName($predmet->getElementsByTagName('table'),'znmala'))<1) continue;
//      $arrId=sizeof($json['predmety']);
      $nazev=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('a'),'nazevpr')[0]->nodeValue);
      $prumer=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detprumerdiv')[0]->nodeValue);
      $ctvrtleti=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detzn')[0]->nodeValue);
      $pololeti=trim($znamkyPololeti[$nazev]);
      $vysledna=($pololeti) ? $pololeti : ( ($ctvrtleti) ? $ctvrtleti : '&nbsp;' ) ;
      $vyslednaClass=($pololeti) ? 'pololetniZnamka' : ( ($ctvrtleti) ? 'ctvrtletniZnamka' : '' ) ;

      $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'detznamka')[0]->getElementsByTagName('td');
      $vahy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]) ? $funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]->getElementsByTagName('td') : null;
      $datumy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]) ? $datumy=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]->getElementsByTagName('td') : null;
/*      $poradiMinus=0;
      $lastXpozice=0; $Ypozice=7; $radek=1; $maxRadek=2;
      foreach($znamky as $c=>$znamka) {
        $datum=($datumy) ? explode('.',$datumy->item($c)->nodeValue) : null;
        $poradi=$c-$poradiMinus;
        if($lastNazev==$znamka->getAttribute('title') && $lastDatum==mktime(0,0,0,$datum[1],$datum[0],$datum[2]) && $lastVaha==$vahy->item($c)->nodeValue) {
          $poradi--; $poradiMinus++;
          $json['predmety'][$arrId]['znamky'][$poradi]['znamky'][]=$znamka->nodeValue;
          $json['predmety'][$arrId]['znamky'][$poradi]['delka']+=10;
          }
        else {
          $json['predmety'][$arrId]['znamky'][$poradi]['znamky'][]=$znamka->nodeValue;
          $json['predmety'][$arrId]['znamky'][$poradi]['nazev']=$znamka->getAttribute('title');
          $json['predmety'][$arrId]['znamky'][$poradi]['vaha']=($vahy) ? $vahy->item($c)->nodeValue : null;
          $json['predmety'][$arrId]['znamky'][$poradi]['datum']=($datum) ? mktime(0,0,0,$datum[1],$datum[0],$datum[2]) : null;
          $json['predmety'][$arrId]['znamky'][$poradi]['fdatum']=($datum) ? date('j.n.y',mktime(0,0,0,$datum[1],$datum[0],$datum[2])) : null;
          $json['predmety'][$arrId]['znamky'][$poradi]['delka']=(strlen($json['predmety'][$arrId]['znamky'][$poradi]['nazev'])*8)+90;
          $lastNazev=$json['predmety'][$arrId]['znamky'][$poradi]['nazev'];
          $lastDatum=$json['predmety'][$arrId]['znamky'][$poradi]['datum'];
          $lastVaha=$json['predmety'][$arrId]['znamky'][$poradi]['vaha'];
          if($json['predmety'][$arrId]['znamky'][$poradi]['vaha']=='X') $json['predmety'][$arrId]['znamky'][$poradi]['vaha']=10;

          $json['predmety'][$arrId]['znamky'][$poradi]['Xpozice']=(mktime(0,0,0,$datum[1],$datum[0],$datum[2])-$zacatekPololeti)/17280;
          $radek=radekZnamky($json['predmety'][$arrId]['znamky'],$json['predmety'][$arrId]['znamky'][$poradi]['Xpozice']);
          if($radek>$maxRadek) {
            $maxRadek=$radek;
            if((strlen($json['predmety'][$arrId]['nazev'])*12)-130>$json['predmety'][$arrId]['znamky'][$poradi]['Xpozice']) $maxRadek++;
            }
          $lastXpozice=$json['predmety'][$arrId]['znamky'][$poradi]['Xpozice'];
          $json['predmety'][$arrId]['znamky'][$poradi]['radek']=$radek;

          }
        }
      $json['predmety'][$arrId]['maxRadek']=$maxRadek;*/
      $json['content'].='<section onclick="podrobnostiPredmetu('.$predC.')" id="sectionPredmetu'.$predC.'"><div class="nazevPredmetu">'.$nazev.'</div><div class="vyslednaPredmetu '.$vyslednaClass.'">'.$vysledna.'</div><div class="prumerPredmetu">'.$prumer.'</div><div class="znamkyPredmetu" id="znamkyPredmetu'.$predC.'">';

      $znamkyA=array(); // vertikální zobrazení
      $poradiMinus=0;
      foreach($znamky as $c=>$znamka) {
        $datum=($datumy) ? explode('.',trim($datumy->item($c)->nodeValue)) : null;
        $vaha=($vahy) ? ((trim($vahy->item($c)->nodeValue)=='X') ? 10 : trim($vahy->item($c)->nodeValue)) : null;

        $fontSize=($vahy) ? $vaha : 6;
        $json['content'].='<article style="font-size:'.(20+$fontSize*2).'px'.$nova.'">'.$znamka->nodeValue.'</article>';

        $poradi=$c-$poradiMinus;
        if($lastPopis==$znamka->getAttribute('title') && $lastDatum==mktime(0,0,0,$datum[1],$datum[0],$datum[2]) && $lastVaha==$vaha) {
          $poradi--; $poradiMinus++;
          $znamkyA[$poradi]->znamka.=', '.$znamka->nodeValue;
          }
        else {
          $znamkyA[$poradi]=new StdClass;
          $znamkyA[$poradi]->znamka=trim($znamka->nodeValue);
          $znamkyA[$poradi]->popis=trim($znamka->getAttribute('title'));
          $znamkyA[$poradi]->vaha=$vaha;
          $znamkyA[$poradi]->mesic=($datum) ? date('n',mktime(0,0,0,$datum[1],$datum[0],$datum[2])) : -1;
          $znamkyA[$poradi]->fdatum=($datum) ? date('j.n.y',mktime(0,0,0,$datum[1],$datum[0],$datum[2])) : 'bez data';
          $lastPopis=$znamkyA[$poradi]->popis; $lastVaha=$znamkyA[$poradi]->vaha; $lastDatum=mktime(0,0,0,$datum[1],$datum[0],$datum[2]);
          }
        }

      $ctvrtletniPodrobnost=($pololeti && $ctvrtleti) ? '<div class="ctvrtletniPodrobnost">čtvrtletí: '.$ctvrtleti.'</div>' : '';
      $json['content'].='</div><div class="podrobnostiPredmetu2" id="podrobnostiPredmetu'.$predC.'">'.$ctvrtletniPodrobnost.'';

      $mesic=-2; $mesicC=0;
      foreach($znamkyA as $c=>$a) {
        if($a->mesic>$mesic) {
          $json['content'].='<div class="znamkyMesic">'.$funkce->month[($a->mesic-1)].'</div>';
          $mesic=$a->mesic; $mesicC++;
          $margin=' style="margin-top:4px"';
          }
        else $margin='';
        $vaha=($a->vaha) ? ', v'.$a->vaha : '';
        $json['content'].='<article'.$margin.'><b>'.$a->znamka.'</b> '.$a->popis.' <span>'.$a->fdatum.$vaha.'</span></article>';
        }
      $json['content'].='</div><script>predmetHeight['.$predC.']='.(sizeOf($znamkyA)*17+$mesicC*4+44).'</script></section>';
      }
    $json['content'].='</div>';
/*    function radekZnamky($znamky,$Xpozice,$radek=1) {
      foreach($znamky as $a) {
        if($a['radek']==$radek && $a['delka']+$a['Xpozice']>$Xpozice) {
          $radek++;
          $radek=radekZnamky($znamky,$Xpozice,$radek);
          }
        }
      return $radek;
      }*/
    }

  if($json['skola'] && $json['jmeno']) {
    $json['state']='success';
    $funkce->mysqli->query('UPDATE bany SET pocet=0 WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"');
    }
  exit(JSON_encode($json));
?>
