<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  if(!$_POST['bakawebUrl']) exit('retarded');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $res=$funkce->mysqli->query("SELECT nazev,adresa FROM skoly WHERE adresa LIKE '".$funkce->mysqli->real_escape_string($_POST["bakawebUrl"])."%' LIMIT 0,8");
  $red=$funkce->mysqli->query("SELECT nazev,adresa FROM skoly WHERE adresa LIKE '% ".$funkce->mysqli->real_escape_string($_POST["bakawebUrl"])."%' AND adresa NOT LIKE '".$funkce->mysqli->real_escape_string($_POST["bakawebUrl"])."%' LIMIT 0,8");
  $ref=$funkce->mysqli->query("SELECT nazev,adresa FROM skoly WHERE adresa LIKE '%".$funkce->mysqli->real_escape_string($_POST["bakawebUrl"])."%' AND adresa NOT LIKE '% ".$funkce->mysqli->real_escape_string($_POST["bakawebUrl"])."%' AND adresa NOT LIKE '".$funkce->mysqli->real_escape_string($_POST["bakawebUrl"])."%' LIMIT 0,8");$c=($c<7) ? $res->num_rows+$red->num_rows : $res->num_rows;
  $json=array();
  while($a=$res->fetch_assoc()) {
    $arrId=sizeof($json['bakawebUrls']);
    $json['bakawebUrls'][$arrId]['adresa']=$a['adresa'];
    $json['bakawebUrls'][$arrId]['nazev']=$a['nazev'];
    }
  if($res->num_rows<7) while($a=$red->fetch_assoc()) {
    $arrId=sizeof($json['bakawebUrls']);
    $json['bakawebUrls'][$arrId]['adresa']=$a['adresa'];
    $json['bakawebUrls'][$arrId]['nazev']=$a['nazev'];
    }
  if(($res->num_rows+$red->num_rows)<7) while($a=$ref->fetch_assoc()) {
    $arrId=sizeof($json['bakawebUrls']);
    $json['bakawebUrls'][$arrId]['adresa']=$a['adresa'];
    $json['bakawebUrls'][$arrId]['nazev']=$a['nazev'];
    }
  exit(JSON_encode($json));
?>
