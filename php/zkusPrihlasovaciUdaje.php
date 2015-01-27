<?
  require 'funkce.php';
  $funkce=new funkce();
  if(!$_POST['bakawebUrl'] || !$_POST['bakawebPrihlJmeno'] || !$_POST['bakawebHeslo'] || !$_SESSION['ucetID']) exit('retarded');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT pocet FROM bany WHERE ucetID="'.$_SESSION['ucetID'].'"')->fetch_assoc()['pocet']>4) {
    $json['state']='tooManyUserAttempts';
    exit(json_encode($json));
    }
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bany WHERE ucetID="'.$_SESSION['ucetID'].'"')->fetch_assoc()['COUNT(*)']<1)
    $funkce->mysqli->query('INSERT INTO bany (ucetID,pocet) VALUES ("'.$_SESSION['ucetID'].'",1)');
  else $funkce->mysqli->query('UPDATE bany SET pocet=pocet+1 WHERE ucetID="'.$_SESSION['ucetID'].'"');

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
  $bodyUvod=new DOMDocument();
  $html=curl_exec($ch);                // načtení úvodu a zjištění jména
  $bodyUvod->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($bodyUvod->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $json['jmeno']=trim($funkce->getElementsByClassName($bodyUvod->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);
  if($json['skola'] && $json['jmeno']) {
    $json['state']='success';
    $funkce->mysqli->query('UPDATE bany SET pocet=0 WHERE ucetID="'.$_SESSION['ucetID'].'"');
    }
  exit(JSON_encode($json));
?>
