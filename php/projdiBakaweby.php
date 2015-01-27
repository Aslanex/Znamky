<?php
  require_once 'funkce.php';
  $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $ucty=$funkce->mysqli->query('SELECT bakaUcetID,url,prihlJmeno,heslo,chyba FROM bakaUcty ORDER BY posledniKontrola LIMIT 0, 30');
  $funkce->mysqli->query('UPDATE meta SET val="'.date('r').'" WHERE name="lastCron"');
  while($ucet=$ucty->fetch_assoc()) {

    if($ucet['chyba']>=2) continue;
    elseif($ucet['chyba']>=1) $funkce->mysqli->query('UPDATE bakaUcty SET chyba='.($ucet['chyba']+1).' WHERE bakaUcetID='.$ucet['bakaUcetID']);
    else $funkce->mysqli->query('UPDATE bakaUcty SET chyba=1 WHERE bakaUcetID='.$ucet['bakaUcetID']);

    $predmety=null;$predmet=null;$html=null;$bodyZnamky=null;$skola=null;$jmeno=null;$tableZnamky=null;$bodyVychOpatreni=null;$vychOpatreni=null;$tdVychOpatreni=null;

    $funkce->mysqli->query('BEGIN');

    $bodyUvod=new DOMDocument; $bodyZnamky=new DOMDocument(); $bodyPololeti=new DOMDocument(); $bodyVychOpatreni=new DOMDocument();

    $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','CURLCOOKIE');
    $ch=curl_init($ucet['url'].'login.aspx');
    curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    $html=curl_exec($ch);                      // první návštěva pro cookie
    $bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $prihlInput=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'logintable')[0]->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');

    curl_setopt($ch,CURLOPT_REFERER,$ucet['url'].'login.aspx');
    curl_setopt($ch,CURLOPT_POST,true);
    $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&'.urlencode($prihlInput).'='.$ucet['prihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$ucet['heslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
    curl_exec($ch);                // přihlášení
    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=302) {
      continue;
      }

    curl_setopt($ch,CURLOPT_POST,false);
    curl_setopt($ch,CURLOPT_POSTFIELDS,null);
    curl_setopt($ch,CURLOPT_REFERER,$ucet['url'].'login.aspx');
    curl_setopt($ch,CURLOPT_URL,$ucet['url'].'uvod.aspx');
    $html=curl_exec($ch);                      // návštěva úvodu pro obranný mechanismus, zjištění základních údajů

    $bodyUvod->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola    = trim($funkce->getElementsByClassName($bodyUvod->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $jmeno    = trim($funkce->getElementsByClassName($bodyUvod->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);
    $znamkyURL= $bodyUvod->getElementById('hlavnimenu_DXM2_')->getElementsByTagName('div')->item(0)->getElementsByTagName('ul')->item(0)->getElementsByTagName('li')->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href');

    curl_setopt($ch,CURLOPT_REFERER,$ucet['url'].'uvod.aspx');
    curl_setopt($ch,CURLOPT_URL,$ucet['url'].$znamkyURL);
    $html=curl_exec($ch);                // zobrazení známek
    @$bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));

    $tableZnamky=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'radekznamky')[0];

    $predmety=$tableZnamky->getElementsByTagName('tr');

    curl_setopt($ch,CURLOPT_URL,$ucet['url'].'prehled.aspx?s=4');
    $html=curl_exec($ch);                // pololetní známky
    $bodyPololeti->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $tablePololeti=$funkce->getElementsByClassName($bodyPololeti->getElementsByTagName('table'),'tablepolo')[0];
    $predmetyPololeti=$tablePololeti->getElementsByTagName('tr');
    foreach($predmetyPololeti as $predmet) {
      if(!$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]) continue;
      $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'poloznamka');
      $znamkyPololeti[trim($funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]->nodeValue)]=trim($znamky[(sizeof($znamky)-1)]->nodeValue);
      }

    $noveZnamkyC=0; $predmetyC=0;
    foreach($predmety as $predmet) {     // výpis předmětů do databáze
      if(sizeof($funkce->getElementsByClassName($predmet->getElementsByTagName('table'),'znmala'))<1) continue;
      $predmetyC++;
      $arrId=sizeof($predmety);
      $znamkyC=0;
      $nazev=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('a'),'nazevpr')[0]->nodeValue);
      $prumer=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detprumerdiv')[0]->nodeValue);
      $ctvrtleti=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detzn')[0]->nodeValue);
      $ctvrtletiDb=($ctvrtleti) ? '"'.$funkce->mysqli->real_escape_string($ctvrtleti).'"' : 'NULL';
      $pololeti=$znamkyPololeti[$nazev];
      $pololetiDb=($pololeti) ? '"'.$funkce->mysqli->real_escape_string($pololeti).'"' : 'NULL';

      $r=$funkce->mysqli->query('SELECT predmetID FROM bakaPredmety WHERE nazev="'.$funkce->mysqli->real_escape_string($nazev).'" AND bakaUcetID="'.$ucet['bakaUcetID'].'"');
      if($r->num_rows<1) {
        $funkce->mysqli->query('INSERT INTO bakaPredmety (nazev,ucitel,prumer,ctvrtleti,pololeti,bakaUcetID) VALUES ("'.$funkce->mysqli->real_escape_string($nazev).'","","'.$funkce->mysqli->real_escape_string($prumer).'",'.$ctvrtletiDb.','.$pololetiDb.','.$ucet['bakaUcetID'].')');  // uložení nového předmětu
        $predmetID=$funkce->mysqli->insert_id;
        }
      else {
        $predmetID=$r->fetch_assoc()['predmetID'];
        $funkce->mysqli->query('UPDATE bakaPredmety SET ucitel="", prumer="'.$funkce->mysqli->real_escape_string($prumer).'", ctvrtleti='.$ctvrtletiDb.', pololeti='.$pololetiDb.' WHERE predmetID='.$predmetID);  // aktualizace údajů předmětu
        }

      $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'detznamka')[0]->getElementsByTagName('td');
      $vahy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]) ? $funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]->getElementsByTagName('td') : null;
      $datumy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]) ? $datumy=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]->getElementsByTagName('td') : null;
      foreach($znamky as $c=>$znamka) {
        $datumA=($datumy && trim($datumy->item($c)->nodeValue)) ? explode('.',trim($datumy->item($c)->nodeValue)) : null;
        $znamkaH=trim($znamka->nodeValue);
        $popis=trim($znamka->getAttribute('title'));
        $popisDb=($popis) ? '"'.$funkce->mysqli->real_escape_string($popis).'"' : 'NULL';
        $vaha=($vahy) ? trim($vahy->item($c)->nodeValue) : null;
        $vaha=($vaha=='X') ? $vaha=10 : $vaha;
        $vahaDb=($vaha) ? '"'.$funkce->mysqli->real_escape_string($vaha).'"' : 'NULL';
        $datum=($datumA!==null) ? '"'.mktime(0,0,0,$datumA[1],$datumA[0],$datumA[2]).'"' : 'NULL';

        $a=$funkce->mysqli->query('SELECT COUNT(*),znamkaID,nove FROM bakaZnamky WHERE poradi='.$c.' AND predmetID='.$predmetID)->fetch_assoc();
        if($a['COUNT(*)']!=1) {
          $funkce->mysqli->query('INSERT INTO bakaZnamky (poradi,znamka,vaha,datum,popis,nove,predmetID) VALUES ('.$c.',"'.$funkce->mysqli->real_escape_string($znamkaH).'",'.$vahaDb.','.$datum.','.$popisDb.',1,'.$predmetID.')');  // uložení nové známky
          $noveZnamkyC++;
          }
        else {
          $funkce->mysqli->query('UPDATE bakaZnamky SET znamka="'.$funkce->mysqli->real_escape_string($znamkaH).'", vaha='.$vahaDb.', datum='.$datum.', popis='.$popisDb.' WHERE znamkaID='.$a['znamkaID']);  // aktualizace údajů známky
          if($funkce->mysqli->affected_rows==1 || $a['nove']==1) {
            $funkce->mysqli->query('UPDATE bakaZnamky SET nove=1 WHERE znamkaID='.$a['znamkaID']);
            $noveZnamkyC++;
            }
          }

        $znamkyC++;
        }
      $navic=$funkce->mysqli->query('SELECT COUNT(*) FROM bakaZnamky WHERE predmetID='.$predmetID)->fetch_row()[0];
      if($navic>$znamkyC) $funkce->mysqli->query('DELETE FROM bakaZnamky WHERE predmetID='.$predmetID.' AND poradi>='.$znamkyC);
      }

  if($predmetyC<1) {  // promazání předmětů, když nejsou
    $predmety=$funkce->mysqli->query('SELECT predmetID FROM bakaPredmety WHERE bakaUcetID='.$ucet['bakaUcetID']);
    while($predmet=$predmety->fetch_assoc()) $funkce->mysqli->query('DELETE FROM bakaZnamky WHERE predmetID='.$predmet['predmetID']);
    $funkce->mysqli->query('DELETE FROM bakaPredmety WHERE bakaUcetID='.$ucet['bakaUcetID']);
    $funkce->mysqli->query('UPDATE bakaUcty SET nove=0 WHERE bakaUcetID='.$ucet['bakaUcetID']);
    }

    curl_setopt($ch,CURLOPT_URL,$ucet['url'].'prehled.aspx?s=5');
    $html=curl_exec($ch);                // výchovná opatření
    $bodyVychOpatreni->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $tdVychOpatreni=$bodyVychOpatreni->getElementById('cphmain_roundvych_RPC');
    if($tdVychOpatreni) $vychOpatreni=$tdVychOpatreni->getElementsByTagName('table');
    if($vychOpatreni) foreach($vychOpatreni as $c=>$opatreni) {
      $datum =($funkce->getElementsByClassName($opatreni->getElementsByTagName('span'),'vychdatum')) ? $funkce->getElementsByClassName($opatreni->getElementsByTagName('span'),'vychdatum')[0]->nodeValue : '';
      $druh ='"'.trim(str_replace($datum,'',$funkce->mysqli->real_escape_string($funkce->getElementsByClassName($opatreni->getElementsByTagName('td'),'vychdruh')[0]->nodeValue))).'"';
      $datumA=explode('.',trim(str_replace(array('(',')'),'',$datum)));
      $datum =($datum) ? '"'.mktime(0,0,0,$datumA[1],$datumA[0],$datumA[2]).'"' : 'NULL';
      $text ='"'.$funkce->mysqli->real_escape_string(trim($funkce->getElementsByClassName($opatreni->getElementsByTagName('td'),'vychtext')[0]->nodeValue)).'"';

      $a=$funkce->mysqli->query('SELECT COUNT(*),opatreniID FROM bakaVychOpatreni WHERE poradi='.$c.' AND bakaUcetID='.$ucet['bakaUcetID'])->fetch_assoc();
      if($a['COUNT(*)']!=1) {
        $funkce->mysqli->query('INSERT INTO bakaVychOpatreni (poradi,druh,datum,text,bakaUcetID) VALUES ('.$c.','.$druh.','.$datum.','.$text.','.$ucet['bakaUcetID'].')');  // uložení výchovného opatření
        }
      else {
        $funkce->mysqli->query('UPDATE bakaVychOpatreni SET druh='.$druh.', datum='.$datum.', text='.$text.' WHERE opatreniID='.$a['opatreniID']);  // aktualizace výchovného opatření
        }
      }



    $funkce->mysqli->query('UPDATE bakaUcty SET skola="'.$funkce->mysqli->real_escape_string($skola).'", jmeno="'.$funkce->mysqli->real_escape_string($jmeno).'", nove='.$noveZnamkyC.', posledniKontrola='.time().', chyba=NULL WHERE bakaUcetID='.$ucet['bakaUcetID']);  // aktualizace údajů účtu
    $funkce->mysqli->query('COMMIT');
    curl_close($ch);
    }
?>
