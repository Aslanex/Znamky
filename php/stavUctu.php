<?php
  require "funkce.php";
  $funkce=new funkce();
  if(!$_SESSION['ucetID']) $json['state']='not logged';
  else {
    $json['state']='success';
    $json['nick']=$_SESSION['nick'];
    }
  exit(JSON_encode($json));
?>
