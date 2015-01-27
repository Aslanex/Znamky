<?php
  require_once 'funkce.php'; if(!$funkce) $funkce=new funkce();
  if(!isset($_POST['bakaUrl']) || !isset($_POST['bakaPrihlJmeno']) || !isset($_POST['bakaHeslo'])) exit(json_encode(array('s'=>0,'e'=>0)));
  
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bakaUcty WHERE url="'.$funkce->mysqli->real_escape_string($_POST['bakaUrl']).'" AND prihlJmeno="'.$funkce->mysqli->real_escape_string($_POST['bakaPrihlJmeno']).'" AND heslo="'.$funkce->mysqli->real_escape_string($_POST['bakaHeslo']).'" AND ucetID='.$_SESSION['ucetID'])->fetch_assoc()['COUNT(*)']>0) {
    exit(json_encode(array('s'=>0,'e'=>4)));
    }
  
  /* test zdroje, přihlášení do bakalářů */
  $bodyZnamky=new DOMDocument;
  $json['ckfile']=tempnam('/home/www/vsechno-atd.cz/tmp','ZWCC');
  $ch=curl_init($_POST['bakaUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  $html=curl_exec($ch);
  $bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));

  $prihlTable=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'logintable')[0];
  if ($prihlTable) $prihlInput=$prihlTable->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  else exit(json_encode(array('s'=>0,'e'=>3)));

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_POST,true);
  $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&'.urlencode($prihlInput).'='.$_POST['bakaPrihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$_POST['bakaHeslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
  curl_exec($ch);
  if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=302) exit(json_encode(array('s'=>0,'e'=>3)));

  /* uložení zdroje */

  $funkce->mysqli->query('INSERT INTO bakaUcty (url,prihlJmeno,heslo,skola,jmeno,zobrazeni,nove,posledniKontrola,ucetID) VALUES ("'.$funkce->mysqli->real_escape_string($_POST['bakaUrl']).'","'.$funkce->mysqli->real_escape_string($_POST['bakaPrihlJmeno']).'","'.$funkce->mysqli->real_escape_string($_POST['bakaHeslo']).'","","",2,0,0,'.$_SESSION['ucetID'].')');
  $zdrojID=$funkce->mysqli->insert_id;
  
  echo json_encode(array('s'=>1));

  /* synchronizace známek --- */
  
  $bodyUvod=new DOMDocument; $bodyZnamky=new DOMDocument(); $bodyPololeti=new DOMDocument(); $bodyVychOpatreni=new DOMDocument();
  libxml_use_internal_errors(true);
  curl_setopt($ch,CURLOPT_POST,false);
  curl_setopt($ch,CURLOPT_POSTFIELDS,null);
  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].'uvod.aspx');
  $html=curl_exec($ch);                      // návštěva úvodu pro obranný mechanismus, zjištění základních údajů

  $bodyUvod->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $skola    = trim($funkce->getElementsByClassName($bodyUvod->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $jmeno    = trim($funkce->getElementsByClassName($bodyUvod->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);
  $znamkyURL= $bodyUvod->getElementById('hlavnimenu_DXM2_')->getElementsByTagName('ul')->item(0)->getElementsByTagName('li')->item(0)->getElementsByTagName('div')->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href');
  
  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'uvod.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].$znamkyURL);
  $html=curl_exec($ch);                // zobrazení známek
  @$bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  if($bodyZnamky->getElementById('cphmain_Flyout2_Checktypy')) $vahyChecked=$bodyZnamky->getElementById('cphmain_Flyout2_Checktypy')->getAttribute('checked');
  else $vahyChecked=true;

  if(!$vahyChecked) {
    $__VIEWSTATE=$bodyZnamky->getElementById('__VIEWSTATE')->getAttribute('value');
    $__EVENTVALIDATION=$bodyZnamky->getElementById('__EVENTVALIDATION')->getAttribute('value');
    curl_setopt($ch,CURLOPT_POST,true);
    $postdata='__EVENTTARGET=ctl00%24cphmain%24listdoba&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE='.urlencode($__VIEWSTATE).'&__EVENTVALIDATION='.urlencode($__EVENTVALIDATION).'&hlavnimenuSI=2i0&ctl00%24cphmain%24Flyout2%24Checkdatumy:on&ctl00%24cphmain%24Flyout2%24Checktypy=on&ctl00%24cphmain%24Flyout2%24Checkprumery:on&ctl00%24cphmain%24listdoba:m%C4%9Bs%C3%ADc+%C4%8Derven&DXScript=1_44%2C1_76%2C1_69%2C1_74%2C1_60%2C2_34%2C2_40';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
    curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'prehled.aspx?s=2');
    $html=curl_exec($ch);                // případné zobrazení s váhou, pokud defaultně zakázáno
    $bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    }

  $tableZnamky=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'radekznamky')[0];
  
  $predmety=$tableZnamky->getElementsByTagName('tr');

  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].'prehled.aspx?s=4');
  $html=curl_exec($ch);                // pololetní známky
  @$bodyPololeti->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $tablePololeti=$funkce->getElementsByClassName($bodyPololeti->getElementsByTagName('table'),'tablepolo')[0];
  $predmetyPololeti=$tablePololeti->getElementsByTagName('tr');
  foreach($predmetyPololeti as $predmet) {
    if(!$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]) continue;
    $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'poloznamka');
    $znamkyPololeti[trim($funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]->nodeValue)]=trim($znamky[(sizeof($znamky)-1)]->nodeValue);
    }

  $noveZnamkyC=0;
  foreach($predmety as $predmet) {     // výpis předmětů do databáze
    if(sizeof($funkce->getElementsByClassName($predmet->getElementsByTagName('table'),'znmala'))<1) continue;
    $arrId=sizeof($predmety);
    $nazev=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('a'),'nazevpr')[0]->nodeValue);
    $prumer=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detprumerdiv')[0]->nodeValue);
    $ctvrtleti=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detzn')[0]->nodeValue);
    $ctvrtletiDb=($ctvrtleti) ? '"'.$funkce->mysqli->real_escape_string($ctvrtleti).'"' : 'NULL';
    $pololeti=$znamkyPololeti[$nazev];
    $pololetiDb=($pololeti) ? '"'.$funkce->mysqli->real_escape_string($pololeti).'"' : 'NULL';

    $funkce->mysqli->query('INSERT INTO bakaPredmety (nazev,ucitel,prumer,ctvrtleti,pololeti,bakaUcetID) VALUES ("'.$funkce->mysqli->real_escape_string($nazev).'","","'.$funkce->mysqli->real_escape_string($prumer).'",'.$ctvrtletiDb.','.$pololetiDb.','.$zdrojID.')');  // uložení nového předmětu
    $predmetID=$funkce->mysqli->insert_id;

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

      $funkce->mysqli->query('INSERT INTO bakaZnamky (poradi,znamka,vaha,datum,popis,nove,predmetID) VALUES ('.$c.',"'.$funkce->mysqli->real_escape_string($znamkaH).'",'.$vahaDb.','.$datum.','.$popisDb.',NULL,'.$predmetID.')');  // uložení nové známky
      $noveZnamkyC++;
      }
    }

  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].'prehled.aspx?s=5');
  $html=curl_exec($ch);                // výchovná opatření
  @$bodyVychOpatreni->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $tdVychOpatreni=$bodyVychOpatreni->getElementById('cphmain_roundvych_RPC');
  if($tdVychOpatreni) $vychOpatreni=$tdVychOpatreni->getElementsByTagName('table');
  if($vychOpatreni) foreach($vychOpatreni as $c=>$opatreni) {
    $datum =($funkce->getElementsByClassName($opatreni->getElementsByTagName('span'),'vychdatum')) ? $funkce->getElementsByClassName($opatreni->getElementsByTagName('span'),'vychdatum')[0]->nodeValue : '';
    $druh ='"'.trim(str_replace($datum,'',$funkce->mysqli->real_escape_string($funkce->getElementsByClassName($opatreni->getElementsByTagName('td'),'vychdruh')[0]->nodeValue))).'"';
    $datumA=explode('.',trim(str_replace(array('(',')'),'',$datum)));
    $datum =($datum) ? '"'.mktime(0,0,0,$datumA[1],$datumA[0],$datumA[2]).'"' : 'NULL';
    $text ='"'.$funkce->mysqli->real_escape_string(trim($funkce->getElementsByClassName($opatreni->getElementsByTagName('td'),'vychtext')[0]->nodeValue)).'"';

      $funkce->mysqli->query('INSERT INTO bakaVychOpatreni (poradi,druh,datum,text,bakaUcetID) VALUES ('.$c.','.$druh.','.$datum.','.$text.','.$zdrojID.')');  // uložení výchovného opatření
    }


  $funkce->mysqli->query('UPDATE bakaUcty SET skola="'.$funkce->mysqli->real_escape_string($skola).'", jmeno="'.$funkce->mysqli->real_escape_string($jmeno).'", nove='.$noveZnamkyC.', posledniKontrola='.time().' WHERE bakaUcetID='.$zdrojID);  // aktualizace údajů účtu

  curl_close($ch);
?>
