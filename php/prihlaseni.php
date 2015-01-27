<?php
  require "funkce.php";
  $funkce=new funkce();
  if(isset($_SESSION['ucetID'])) exit('already');
  elseif(!$_POST['nick'] or !$_POST['heslo']) exit('retarded');
  else {
    $a=@$funkce->mysqli->query("SELECT salt FROM ucty WHERE uz_jmeno='".$funkce->mysqli->real_escape_string($_POST['nick'])."'")->fetch_array();
    $s=@$funkce->mysqli->query("SELECT count(*),ucetID FROM ucty WHERE uz_jmeno='".$funkce->mysqli->real_escape_string($_POST['nick'])."' AND ( heslo='".md5($_POST['heslo'])."' OR heslo='".md5(crypt($_POST['heslo'],$_POST['heslo']))."' OR heslo='".hash("sha512",sha1($a["salt"]).$_POST['heslo']."Všechno, atd")."' )")->fetch_array();
    if($s[0]!=1) print "incorrect";
    else {
      session_regenerate_id(true);
      setcookie(md5('ucetID'),md5($s['ucetID']),null,"/",".vsechno-atd.cz");
      $funkce->mysqli->query('UPDATE ucty SET lastLogin="'.time().'" WHERE ucetID='.$s['ucetID']);
      $result=@$funkce->mysqli->query("SELECT pravo FROM prava,ucty_prava WHERE ucty_prava.ucetID='".$funkce->mysqli->real_escape_string($s['ucetID'])."' AND prava.pravoID=ucty_prava.pravoID");
      while($d=$result->fetch_assoc()) $_SESSION['pravo_'.$d['pravo']]=true;
      $check=md5(mt_rand(1,999999));
      $_SESSION['ucet_check']=$check;
      setcookie(md5("ucet_check"),$check,null,'/','.vsechno-atd.cz');
      $_SESSION['ucetID']=$s['ucetID'];
      $_SESSION['nick']=$_POST['nick'];
      if($_POST['trvale']=='true') {
        $kod=md5(uniqid('', true));
        setcookie('trvale',$kod,strtotime('+5 month'),'/','.vsechno-atd.cz');
        @$funkce->mysqli->query('INSERT INTO trvalePrihlaseni (ucetID,kod,expireTime) VALUES ("'.$funkce->mysqli->real_escape_string($_SESSION['ucetID']).'","'.$kod.'","'.strtotime('+5 months').'")');
        }
      print "completed";
      }
    }
?>
