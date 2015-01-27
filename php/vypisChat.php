<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');

  $json['s']=2; $json['zpravy']=array();
  $zpravy=$funkce->mysqli->query('SELECT text,ucetID FROM chat ORDER BY date DESC LIMIT 0,30');
  $funkce->mysqli->select_db('vsechno-atdcz_vsechno-atd');
  while($zprava=$zpravy->fetch_assoc()) {
    $c=array_push($json['zpravy'],array())-1;
    $nick=$funkce->mysqli->query('SELECT uz_jmeno FROM ucty WHERE ucetID='.$zprava['ucetID'])->fetch_assoc()['uz_jmeno'];
    $json['zpravy'][$c]['nickT']= ($zprava['ucetID']==$_SESSION['ucetID']) ? 'self' : ($zprava['ucetID']==1) ? 'dev' : '';
    $json['zpravy'][$c]['nick'] =$nick;
    $json['zpravy'][$c]['text'] =$zprava['text'];
    }
  $json['s']=1;

  exit(JSON_encode($json));
