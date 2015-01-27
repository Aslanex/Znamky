<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  /*  s 0 error
        1 přihlašuji
        2 čtu předměty
        3 čtu známky
        4 hotovo
      e 0 retarded
        1 ban
        2 url nenalezena
        3 nepřihlášen  */
  if(!$_POST['bakaUrl'] || !$_POST['bakaPrihlJmeno'] || !$_POST['bakaHeslo']) exit(json_encode(array('s'=>0,'e'=>0)));
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT pocet FROM bany WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"')->fetch_assoc()['pocet']>3) {
    $json['s']=0; $json['e']=1;
    exit(json_encode($json));
    }
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bany WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"')->fetch_assoc()['COUNT(*)']<1)
    $funkce->mysqli->query('INSERT INTO bany (ip,pocet) VALUES ("'.$_SERVER['REMOTE_ADDR'].'",1)');
  else $funkce->mysqli->query('UPDATE bany SET pocet=pocet+1 WHERE ip="'.$_SERVER['REMOTE_ADDR'].'"');

  $body=new DOMDocument(); $bodyZnamky=new DOMDocument(); $bodyPololeti=new DOMDocument(); $json['predmety']=array();

  $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','CURLCOOKIE');  // první návštěva pro cookie a test adresy
  $ch=curl_init($_POST['bakaUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  $html=curl_exec($ch);
  if($body->loadHTML($html)!==true) exit(json_encode(array('s'=>0,'e'=>2)));
  
  echo json_encode(array('s'=>1)); flush(); ob_clean();
  
  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'login.aspx');  // přihlášení
  curl_setopt($ch,CURLOPT_POST,true);
  $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&ctl00%24cphmain%24TextBoxjmeno='.$_POST['bakaPrihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$_POST['bakaHeslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
  $html=curl_exec($ch);                // přihlášení
  if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=302) {
    if(strpos($html,'Bylo provedeno příliš mnoho neúspěšných pokusů o přihlášení')) $json['state']='tooManyAttempts';
    elseif(strpos($html,'Přihlášení neproběhlo v pořádku')) $json['state']='loginError';
    else $json['state']='unknownError';
    exit(json_encode($json));
    }
  
  echo json_encode(array('s'=>1)); flush(); ob_clean();
