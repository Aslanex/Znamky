<?
  require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->zacatek('anonym','Anonymní prohlížení Známek');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $funkce->zpetButton();
  echo '<div id="obsahStranky">Vyber si svou školu:<div style="margin: 10px auto">';

  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $res=$funkce->mysqli->query('SELECT nazev,adresa FROM skoly');
  while($a=$res->fetch_assoc()) echo '<a href="javascript:void(0)" onclick="pripravBakawebLogin(\''.htmlspecialchars($a['adresa']).'\',\''.htmlspecialchars($a['nazev']).'\')">'.htmlspecialchars($a['nazev']).'</a><br>';

?>
  </div><a href="javascript:void(0)" onclick="oldContent=$('#obsahStranky').html();pripravBakawebUrl()">nemůžu nalézt svou školu</a>
<?
  $funkce->htmlKonec();
