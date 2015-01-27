<?
  require 'php/funkce.php';
  $funkce=new funkce();
  if(!$_SESSION['ucetID']) {
    header('HTTP/1.0 307 Retarded');
    header('Location: /');
    exit();
    }
  $funkce->zacatek('pridej','Přidání zdroje do Známek');
  $funkce->zpetButton();
  echo '<div id="obsahStranky">Vyber si svou školu:<div style="margin: 10px auto">';

  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $res=$funkce->mysqli->query('SELECT nazev,adresa FROM skoly');
  while($a=$res->fetch_assoc()) echo '<a href="javascript:void(0)" onclick="oldContent=$(\'#obsahStranky\').html();pripravBakawebLogin(\''.htmlspecialchars($a['adresa']).'\',\''.htmlspecialchars($a['nazev']).'\')">'.htmlspecialchars($a['nazev']).'</a><br>';

?>
  </div><a href="javascript:void(0)" onclick="oldContent=$('#obsahStranky').html();pripravBakawebUrl()">nemůžu nalézt svou školu</a>
<?
  $funkce->htmlKonec();
