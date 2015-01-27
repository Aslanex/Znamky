<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  $bakaUcetID=(int) $_POST['bakaUcetID'];
  $zobrazeni=(int) $_POST['zobrazeni'];
  if(!$bakaUcetID || !$zobrazeni || !$_SESSION['ucetID']) exit('retarded');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'" AND ucetID='.$_SESSION['ucetID'])->fetch_assoc()['COUNT(*)']!=1) exit('unauthorised');

  $funkce->mysqli->query('UPDATE bakaUcty SET zobrazeni="'.$zobrazeni.'" WHERE bakaUcetID="'.$bakaUcetID.'"');
  if($funkce->mysqli->affected_rows>0) $json['state']='success';
  else $json['state']='not affected';
  exit(json_encode($json));
