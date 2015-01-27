<?php
  require_once 'funkce.php';
  $funkce=new funkce();
  if(!$_POST['bakawebUrl'] || !$_POST['bakawebPrihlJmeno'] || !$_POST['bakawebHeslo'] || !$_SESSION['ucetID']) exit('retarded');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bakaUcty WHERE url="'.$funkce->mysqli->real_escape_string($_POST['bakawebUrl']).'" AND prihlJmeno="'.$funkce->mysqli->real_escape_string($_POST['bakawebPrihlJmeno']).'" AND heslo="'.$funkce->mysqli->real_escape_string($_POST['bakawebHeslo']).'" AND ucetID='.$_SESSION['ucetID'])->fetch_assoc()['COUNT(*)']>0) {
    $json['state']='already';
    exit(JSON_encode($json));
    }

  $json['state']='success';
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
    
  $bodyZnamky=new DOMDocument(); $bodyPololeti=new DOMDocument();

  curl_setopt($ch,CURLOPT_POST,false);
  curl_setopt($ch,CURLOPT_POSTFIELDS,null);
  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'uvod.aspx');
  curl_exec($ch);                      // návštěva úvodu pro obranný mechanismus

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'uvod.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'prehled.aspx?s=2');
  $html=curl_exec($ch);                // zobrazení známek
  $bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $json['jmeno']=trim($funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);

  if($bodyZnamky->getElementById('cphmain_Flyout2_Checktypy')) $vahyChecked=$bodyZnamky->getElementById('cphmain_Flyout2_Checktypy')->getAttribute('checked');
  else $vahyChecked=true;

  if(!$vahyChecked) {
    $__VIEWSTATE=$bodyZnamky->getElementById('__VIEWSTATE')->getAttribute('value');
    $__EVENTVALIDATION=$bodyZnamky->getElementById('__EVENTVALIDATION')->getAttribute('value');
    curl_setopt($ch,CURLOPT_POST,true);
    $postdata='__EVENTTARGET=ctl00%24cphmain%24listdoba&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE='.urlencode($__VIEWSTATE).'&__EVENTVALIDATION='.urlencode($__EVENTVALIDATION).'&hlavnimenuSI=2i0&ctl00%24cphmain%24Flyout2%24Checkdatumy:on&ctl00%24cphmain%24Flyout2%24Checktypy=on&ctl00%24cphmain%24Flyout2%24Checkprumery:on&ctl00%24cphmain%24listdoba:m%C4%9Bs%C3%ADc+%C4%8Derven&DXScript=1_44%2C1_76%2C1_69%2C1_74%2C1_60%2C2_34%2C2_40';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
    curl_setopt($ch,CURLOPT_REFERER,$ucet['url'].'prehled.aspx?s=2');
    $html=curl_exec($ch);                // případné zobrazení s váhou, pokud defaultně zakázáno
    $bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    }

  $tableZnamky=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'radekznamky')[0];
  $predmety=$tableZnamky->getElementsByTagName('tr');

  $funkce->mysqli->query('INSERT INTO bakaUcty (url,prihlJmeno,heslo,skola,jmeno,zobrazeni,nove,posledniKontrola,ucetID) VALUES ("'.$funkce->mysqli->real_escape_string($_POST['bakawebUrl']).'","'.$funkce->mysqli->real_escape_string($_POST['bakawebPrihlJmeno']).'","'.$funkce->mysqli->real_escape_string($_POST['bakawebHeslo']).'","'.$funkce->mysqli->real_escape_string($json['skola']).'","'.$funkce->mysqli->real_escape_string($json['jmeno']).'",2,1,'.time().','.$_SESSION['ucetID'].')');  // uložení účtu
  $bakaUcetID=$funkce->mysqli->insert_id;

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakawebUrl'].'prehled.aspx?s=2');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'prehled.aspx?s=4');
  $html=curl_exec($ch);                // pololetní známky
  $bodyPololeti->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $tablePololeti=$funkce->getElementsByClassName($bodyPololeti->getElementsByTagName('table'),'tablepolo')[0];
  $predmetyPololeti=$tablePololeti->getElementsByTagName('tr');
  foreach($predmetyPololeti as $predmet) {
    if(!$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]) continue;
    $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'poloznamka');
    $znamkyPololeti[trim($funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]->nodeValue)]=trim($znamky[(sizeof($znamky)-1)]->nodeValue);
    }

  foreach($predmety as $predmet) {     // výpis předmětů do databáze
    if(sizeof($funkce->getElementsByClassName($predmet->getElementsByTagName('table'),'znmala'))<1) continue;
    $arrId=sizeof($predmety);
    $nazev=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('a'),'nazevpr')[0]->nodeValue);
    $prumer=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detprumerdiv')[0]->nodeValue);
    $ctvrtleti=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detzn')[0]->nodeValue);
    $ctvrtletiDb=($ctvrtleti) ? '"'.$funkce->mysqli->real_escape_string($ctvrtleti).'"' : 'NULL';
    $pololeti=$znamkyPololeti[$nazev];
    $pololetiDb=($pololeti) ? '"'.$funkce->mysqli->real_escape_string($pololeti).'"' : 'NULL';

    $funkce->mysqli->query('INSERT INTO bakaPredmety (nazev,ucitel,prumer,ctvrtleti,pololeti,bakaUcetID) VALUES ("'.$funkce->mysqli->real_escape_string($nazev).'","","'.$funkce->mysqli->real_escape_string($prumer).'",'.$ctvrtletiDb.','.$pololetiDb.','.$bakaUcetID.')');  // uložení předmětu
    $predmetID=$funkce->mysqli->insert_id;

    $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'detznamka')[0]->getElementsByTagName('td');
    $vahy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]) ? $funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]->getElementsByTagName('td') : null;
    $datumy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]) ? $datumy=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]->getElementsByTagName('td') : null;
    foreach($znamky as $c=>$znamka) {
      $datum=($datumy) ? explode('.',$datumy->item($c)->nodeValue) : null;
      $znamkaH=trim($znamka->nodeValue);
      $popis=trim($znamka->getAttribute('title'));
      $popisDb=($popis) ? '"'.$funkce->mysqli->real_escape_string($popis).'"' : 'NULL';
      $vaha=($vahy) ? trim($vahy->item($c)->nodeValue) : null;
      $vaha=($vaha=='X') ? $vaha=10 : $vaha;
      $vahaDb=($vaha) ? '"'.$funkce->mysqli->real_escape_string($vaha).'"' : 'NULL';
      $datum=($datumA!==null) ? '"'.mktime(0,0,0,$datumA[1],$datumA[0],$datumA[2]).'"' : 'NULL';

      $funkce->mysqli->query('INSERT INTO bakaZnamky (poradi,znamka,vaha,datum,popis,predmetID) VALUES ('.$c.',"'.$funkce->mysqli->real_escape_string($znamkaH).'",'.$vahaDb.','.$datum.','.$popisDb.','.$predmetID.')');  // uložení známky

      }
    }
  exit(JSON_encode($json));
?>
