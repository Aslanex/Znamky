<?php
  require_once 'core/main.php'; $MAIN=new MAIN();
  require_once 'core/acc.php';  $ACC =new ACC();

  if(isset($_GET['hash'])) {
    if($ACC->verifyEmail($_GET['hash'])) {
      exit('Díky, tvůj email byl ověřen.<br><a href="/">zpět na Známky</a>');
      }
    else exit('Omlouvám se, ale tento email neznám.<br><a href="/">zpět na Známky</a>');
    }

  else {
    http_response_code(400);
    exit('Něco je špatně.<br><a href="/">zpět na Známky</a>');
    }
?>
