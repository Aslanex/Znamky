<? require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $bakaUcetID=(int) $_GET['bakaUcet'];
  if(!$_SESSION['ucetID'] || !$bakaUcetID) {
    header('HTTP/1.0 307 Retarded');
    header('Location: /');
    exit();
    }
  $funkce->zacatek('spravuj','Správa účtu Známek');
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'" AND ucetID='.$_SESSION['ucetID'])->fetch_assoc()['COUNT(*)']!=1) exit('Nejsi majitel tohoto účtu.');

  $ucet=$funkce->mysqli->query('SELECT url,skola,jmeno,zobrazeni,nove,posledniKontrola FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'"')->fetch_assoc();
  $zobrazeni1=($ucet['zobrazeni']==1) ? ' selected' : '';
  $zobrazeni2=($ucet['zobrazeni']==2) ? ' selected' : '';
  echo '<div id="hlavicka"><div id="zpet" onclick="if(event.which==1) location.href=\'/\'"></div><div id="skola">'.$ucet['skola'].'</div><div id="jmeno">'.$ucet['jmeno'].'</div></div><div id="obsahStranky">Poslední aktualizace známek <b>'.date('j.n.y G:i',$ucet['posledniKontrola']).'</b><br>Url adresa Bakalářů <i>'.$ucet['url'].'</i><br><b>'.$ucet['nove'].'</b> nových známek od posledního zobrazení<form onsubmit="return false;" style="margin:0"><a href="javascript:void(0)" onclick="promaz('.$bakaUcetID.')">promazat data</a> – <a href="javascript:void(0)" onclick="pripravOdstraneni('.$bakaUcetID.')">odstranit zdroj</a><div class="formPreloader" id="promazatPreloader"></div></form>';
  $funkce->htmlKonec();
