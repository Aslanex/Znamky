<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  $bakaUcetID=(int) $_POST['bakaUcetID'];
  if(!$bakaUcetID || !$_SESSION['ucetID']) exit('retarded');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'" AND ucetID='.$_SESSION['ucetID'])->fetch_assoc()['COUNT(*)']!=1) exit('unauthorised');

  $json['state']='success';
  $predmety=$funkce->mysqli->query('SELECT predmetID FROM bakaPredmety WHERE bakaUcetID="'.$bakaUcetID.'"');
  while($predmet=$predmety->fetch_assoc()) $funkce->mysqli->query('DELETE FROM bakaZnamky WHERE predmetID="'.$predmet['predmetID'].'"');
  $funkce->mysqli->query('DELETE FROM bakaPredmety WHERE bakaUcetID="'.$bakaUcetID.'"');
  $funkce->mysqli->query('DELETE FROM bakaVychOpatreni WHERE bakaUcetID="'.$bakaUcetID.'"');
  $funkce->mysqli->query('DELETE FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'"');
  exit(JSON_encode($json));
