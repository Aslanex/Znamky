<?php
  require_once '../funkce.php';
  $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $ucty=$funkce->mysqli->query('SELECT bakaUcetID,url,prihlJmeno,heslo FROM bakaUcty WHERE chyba>1 ORDER BY posledniKontrola LIMIT 0, 100');
  $funkce->mysqli->query('UPDATE meta SET val="'.date('r').'" WHERE name="lastCheckCron"');
  while($ucet=$ucty->fetch_assoc()) {

    $bodyZnamky=new DOMDocument;

    $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','CURLCOOKIE');
    $ch=curl_init($ucet['url'].'login.aspx');
    curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    $html=curl_exec($ch);                      // první návštěva pro cookie
    $bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));

    $prihlTable=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'logintable')[0];
    if ($prihlTable) $prihlInput=$prihlTable->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    else continue;

    curl_setopt($ch,CURLOPT_REFERER,$ucet['url'].'login.aspx');
    curl_setopt($ch,CURLOPT_POST,true);
    $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&'.urlencode($prihlInput).'='.$ucet['prihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$ucet['heslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
    curl_exec($ch);                // přihlášení

    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
      $funkce->mysqli->query('UPDATE bakaUcty SET chyba=NULL WHERE bakaUcetID='.$ucet['bakaUcetID']);
      }

    curl_close($ch);
    }
?>