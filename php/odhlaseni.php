<?php
  require "funkce.php";
  $funkce=new funkce("../..");
  $result=@$funkce->mysqli->query("SELECT pravo FROM ucty_prava,prava WHERE ucty_prava.ucetID='$_SESSION[ucetID]' AND prava.pravoID=ucty_prava.pravoID");
  while($a=$result->fetch_assoc()) {
    unset($_SESSION["pravo_".$a['pravo']]);
    setcookie(md5("pravo_".$a['pravo']),"no",time()-3600,"/",".vsechno-atd.cz");
    };
  setcookie(md5("ucet_check"),"",1,"/",".vsechno-atd.cz");
  setcookie(md5("ucetID"),"",1,"/",".vsechno-atd.cz");
  if($_COOKIE[trvaleP]) {
    list(,$kod)=explode("-",$_COOKIE['trvaleP'],2);
    @$funkce->mysqli->query("DELETE FROM trvalePrihlaseni WHERE ucetID='".$_SESSION['ucetID']."' AND computerIP='".$_SERVER['REMOTE_ADDR']."' AND kod='$kod'");
    setcookie("trvaleP","",1,"/",".vsechno-atd.cz");
    }
  unset($_SESSION['ucet_check']);
  unset($_SESSION['nick']);
  unset($_SESSION['ucetID']);
  print "completed";
?>