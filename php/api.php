<?php
  /* API
   * znamky.vsechno-atd.cz/php/api
   *  požadavek GET: ?do=*pozadavek*&apiKey=*apiKey*
   *  data POST
   *  vrací JSON: s status, případně další data
   *  příklad: znamky.vsechno-atd.cz/php/api?do=tryUrl  POST bakaUrl=https://alef.arcig.cz/  VRÁTÍ {"s":0,"skola":"..."} */

require_once 'core/main.php'; $MAIN=new MAIN();
require_once 'core/baka.php'; $BAKA=new BAKA();
require_once 'core/acc.php';  $ACC =new ACC();

$canUse = false;
/* apiKey ukázka:
 * znamky:                 e0be9aa01035115a94783fec866f256c
 * znamky apiKey:  zna_b0e0be9aa01035115a94783fec866f256c4c22a */
if (isset($_GET['apiKey'])) {
  $MAIN->mysqli->select_db('vsechno-atdcz_znamky');
  $r=$MAIN->mysqli->query('SELECT userID,apiKey FROM apiUsers');
  while($a=$r->fetch_array()) {
    if("zna_b0".md5($a['apiKey'])."4c22a" == $_GET['apiKey']) {
      $canUse = true;
      break;
    }
  }
}

if ($canUse) {
  switch($_GET['do']) {


    case 'tryUrl':
      /* zkouška bakaUrl
       * INPUT bakaUrl
       * RETURN +s 0 error
       *           1 úspěch
       *           2 nenalezeno
       *        +skola */
      $bakaUrl=trim($_POST['bakaUrl']);
      if(!$bakaUrl) exit(json_encode(array('s'=>0)));
      $r=$BAKA->tryUrl($bakaUrl);
      if($r==false) exit(json_encode(array('s'=>2)));
      exit(json_encode(array('s'=>1,'skola'=>$r[1])));


    case 'tryBakaLogin':
      /* zkouška bakaPrihlUdaje
       * INPUT bakaUrl,bakaJmeno,bakaHeslo
       * RETURN +s 0 error
       *           1 úspěch
       *           2 neúspěch
       *           3 přihlašovací stránka nenalezena
       *        +skola
       *        +jmeno */
      $bakaUrl=trim($_POST['bakaUrl']); $bakaJmeno=trim($_POST['bakaJmeno']); $bakaHeslo=trim($_POST['bakaHeslo']);
      if(!$bakaUrl || !$bakaJmeno || !$bakaHeslo) exit(json_encode(array('s'=>0)));
      $r=$BAKA->tryUrl($bakaUrl);
      if($r==false) exit(json_encode(array('s'=>3)));
      $bakaUrl=$r[0]; $prihlInput=$r[2];
      $r=$BAKA->login($bakaUrl,$bakaJmeno,$bakaHeslo,$prihlInput);
      if($r==false) exit(json_encode(array('s'=>2)));
      exit(json_encode(array('s'=>1,'skola'=>$r[0],'jmeno'=>$r[1])));


    case 'accLogin':
      /* přihlášení do Známek
       * INPUT nick,password,permlogin
       * RETURN +s 0 error
       *           1 úspěch
       *           2 neúspěch
       *        +accID
       *        +nick */
      $nick=trim($_POST['nick']); $password=trim($_POST['password']); $perm=trim($_POST['permlogin']);
      if(!$nick || !$password) exit(json_encode(array('s'=>0)));
      $r=$ACC->checkLogin($nick,$password,$perm);
      if($r==false) exit(json_encode(array('s'=>2)));
      else $ACC->doLogin($r[0],$r[1]);

      if($perm=='true') $ACC->doPermLogin($r[0]);

      $accID=$r[0]; $nick=$r[1];
      exit(json_encode(array('s'=>1,'accID'=>$r[0],'nick'=>$r[1])));


    case 'accLogout':
      /* odhlášení ze Známek
       * INPUT -
       * RETURN +s 0 error
       *           1 úspěch */
      $r=$ACC->logout();
      exit(json_encode(array('s'=>1)));


    case 'accSignup':
      /* registrace do Známek
       * INPUT nick,password,email
       * RETURN +s 0 error
       *           1 úspěch
       *           2 obsazený nick
       *           3 obsazený email
       *           4 error v emailu
       *        +accID
       *        +nick */
      $nick=trim($_POST['nick']); $password=trim($_POST['password']); $email=trim($_POST['email']);
      if(!$nick || !$password) exit(json_encode(array('s'=>0)));
      $r=$ACC->signup($nick,$password); /// signup
      if($r===2) exit(json_encode(array('s'=>2)));
      elseif($r==false) exit(json_encode(array('s'=>0)));
      $accID=$r[0];

      if($email) {
        $r=$ACC->changeEmail($accID,$email);
        if($r===2) exit(json_encode(array('s'=>3)));
        elseif($r==false) exit(json_encode(array('s'=>4)));
      }

      exit(json_encode(array('s'=>1,'accID'=>$accID,'nick'=>$_POST['nick'])));


    case 'accZdroje':
      /* výpis zdrojů daného účtu
       * INPUT -
       * RETURN +s 0 error
       *           1 úspěch
       *           2 neúspěch, žádné zdroje
       *           3 nepřihlášený
       *        +zdroje
       *          +zdrojID
       *          +url
       *          +skola
       *          +jmeno
       *          +nove
       *          +lastSync
       *          +chyba */
      if(!$_SESSION['accID']) exit(json_encode(array('s'=>3)));
      $r=$ACC->getZdroje($_SESSION['accID']);
      if($r==false) exit(json_encode(array('s'=>2)));
      $zdroje=array();
      foreach($r[0] as $a)
        $zdroje[]=array('zdrojID'=>$a[0],'url'=>$a[1],'skola'=>$a[2],'jmeno'=>$a[3],'nove'=>$a[4],'lastSync'=>$a[5],'chyba'=>$a[6]);
      exit(json_encode(array('s'=>1,'zdroje'=>$zdroje)));


  }
}
else exit(json_encode(array('s'=>0,'error'=>"wrongapikey")));
