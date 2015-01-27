<?
  require 'funkce.php';
  $funkce=new funkce();
  if(!$_POST['prihlJmeno'] || !$_POST['heslo']) exit('retarded');
  $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','CURLCOOKIE');
  $ch=curl_init('https://alef.arcig.cz/login.aspx');
  curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  curl_exec($ch);              //první návštěva pro cookie
  curl_setopt($ch,CURLOPT_REFERER,'https://alef.arcig.cz/login.aspx');
  curl_setopt($ch,CURLOPT_POST,true);
  $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&ctl00%24cphmain%24TextBoxjmeno='.$_POST['prihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$_POST['heslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
  $body=curl_exec($ch);        //přihlášení
  if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=320) {
    if(strpos($body,'Bylo provedeno příliš mnoho neúspěšných pokusů o přihlášení')) exit('tooManyAttempts');
    elseif(strpos($body,'Přihlášení neproběhlo v pořádku')) exit('loginError');
    else exit('unknownError');
    }
  curl_setopt($ch,CURLOPT_REFERER,'https://alef.arcig.cz/uvod.aspx');
  curl_setopt($ch,CURLOPT_POST,false);
  curl_setopt($ch,CURLOPT_POSTFIELDS,null);
  curl_setopt($ch,CURLOPT_URL,'https://alef.arcig.cz/prehled.aspx?s=2');
  $body=curl_exec($ch);
  
?>
