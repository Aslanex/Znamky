<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  if(!$_POST['zprava'] || !$_SESSION['ucetID']) exit(JSON_encode(array('s'=>0)));

  $json['s']=2;
  $funkce->mysqli->query('INSERT INTO chat (text,ucetID,date) VALUES ("'.$funkce->mysqli->real_escape_string(htmlspecialchars($_POST['zprava'])).'",'.$_SESSION['ucetID'].','.time().')');
  if($funkce->mysqli->affected_rows==1) $json['s']=1;

  exit(JSON_encode($json));
