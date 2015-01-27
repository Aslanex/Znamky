<?php /* MAIN
       * znamky.vsechno-atd.cz/php/core/main.php
       * základní funkce a mysqli, přistupná jen pro skript */

class MAIN {
  public $mysqli;
  function __construct($autologin=true) {
    session_set_cookie_params(null,'/','.vsechno-atd.cz');
    session_start();
    require_once '/home/www/vsechno-atd.cz/secret/pripojeni_k_databazim.php';  // soubor, který připojuje k databázi funkcí pripojse = new mysqli(...)
    $this->mysqli=pripojse("vsechno-atd");
    if(isset($_SESSION['ucetID'])) {
      if(md5($_SESSION['ucetID'])!=$_COOKIE[md5(ucetID)] or $_SESSION[ucet_check]!=$_COOKIE[md5(ucet_check)]) {
        setcookie(md5("ucet_check"),"",1,"/",".vsechno-atd.cz");
        setcookie(md5("ucetID"),"",1,"/",".vsechno-atd.cz");
        setcookie("trvale","",1,"/",".vsechno-atd.cz");
        session_destroy();
        header("Location: http://znamky.vsechno-atd.cz");
        die("Nastala chyba, počkejte prosím.<br>An error occured, please wait.");
        }
      }
    elseif($_COOKIE['trvale'] && $autologin===true) {
      $kod=$_COOKIE['trvale'];
      $ucetID=$this->mysqli->query('SELECT ucetID FROM trvalePrihlaseni WHERE kod="'.$kod.'"')->fetch_assoc()['ucetID'];
      if($ucetID) {
        session_regenerate_id(true);
        $nkod=md5(uniqid('', true));
        setcookie('trvale',$nkod,strtotime('+5 month'),'/','.vsechno-atd.cz');
        $this->mysqli->query('INSERT INTO trvalePrihlaseni (ucetID,kod,expireTime) VALUES ("'.$this->mysqli->real_escape_string($ucetID).'","'.$nkod.'","'.strtotime('+5 months').'")');
        $this->mysqli->query('UPDATE trvalePrihlaseni (expireTime) VALUES ("'.(time()+1000).'") WHERE kod="'.$kod.'"');
        setcookie(md5("ucetID"),md5($ucetID),null,"/",".vsechno-atd.cz");
        $check=md5(mt_rand(1,999999));
        $_SESSION['ucet_check']=$check;
        setcookie(md5('ucet_check'),$check,null,'/','.vsechno-atd.cz');
        $a=$this->mysqli->query('SELECT uz_jmeno FROM ucty WHERE ucetID='.$ucetID)->fetch_assoc();
        $_SESSION['nick']=$a['uz_jmeno'];
        $_SESSION['ucetID']=$ucetID;
        $this->mysqli->query('UPDATE ucty SET lastLogin="'.time().'" WHERE ucetID='.$ucetID);
        }
      }
    }
  function zacatek($adresa,$hLista) {
?>
  <!DOCTYPE html>
  <html>
    <head>
      <meta charset="UTF-8">
      <script type="text/javascript" src="http://vsechno-atd.cz/js/jquery"></script>
      <script type="text/javascript" src="/js/js"></script>
      <link rel="stylesheet" type="text/css" href="/css/css">
      <script type="text/javascript" src="/js/<? echo $adresa ?>"></script>
      <link rel="stylesheet" type="text/css" href="/css/<? echo $adresa ?>">
      <title><? echo $hLista ?></title>
      <link rel="icon" type="image/png" href="/img/favicon">
    </head>
    <body>
    <div id="obalStranky">
<?
    }
  function kontrola_prava($prava=false,$zprava="Nemáte dostatečná práva na zobrazení této stránky nebo nejste přihlášen.",$zacatek=false) {
    if($prava==false) {
      if($_SESSION[ucetID]!=true) {
        echo "<p>$zprava</p>";
        $this->konec();
        }
      }
    else {
      foreach($prava as $pravo) {
        if($pravo==$_SESSION["pravo_".$pravo]) $ok=true;
        }
      if($ok!=true) {
        if($zacatek==true) zacatek();
        echo "<p class='chyba'>$zprava</p>";
        $this->konec();
        }
      }
    }
  function zpetButton() {
    echo '<div id="hlavicka"><div id="zpet" onclick="if(event.which==1) location.href=\'/\'"></div></div>';
    }

  function htmlKonec() {
    echo '</div><div id="about">používáním souhlasíš s <a href="/p">podmíkami užití</a><br>Známky <pre style="display:inline">4.7</pre>, AslanexingCompany 2014 | <a href="mailto:aslanex@vsechno-atd.cz" title="aslanex@vsechno-atd.cz">email</a> | <a href="http://youtu.be/7GoMfttUzD4">úvodní video</a> | <a href="/faq">FAQ</a> | <a href="/changelog.txt">changelog</a></div></div></body>';
    }

  public $day=array(1=>"pondělí",2=>"úterý",3=>"středa",4=>"čtvrtek",5=>"pátek");
  public $month=array(0=>"leden",1=>"únor",2=>"březen",3=>"duben",4=>"květen",5=>'červen',6=>'červenec',7=>'srpen',8=>'září',9=>'říjen',10=>'listopad',11=>'prosinec');
  function den($y) {
    $day=date("N",$y);
    if($y>mktime(0,0,0) and $y<strtotime("today 23:00")) $x="Dnes";
    elseif($y>strtotime("today 23:00") and $y<strtotime("tomorrow 23:00")) $x="Zítra";
    elseif($y>strtotime("tomorrow 23:00") and $y<strtotime("+7 days 23:00")) {
      $x=($day==3 or $day==4) ? "Ve " : "V ";
      $x.=$this->day[$day];
      }
    elseif($y>strtotime("+7 days 23:00") and $y<strtotime("+14 days 23:00")) $x="Příští ".$this->day[$day];
    elseif($y>strtotime("+14 days 23:00") and $y<strtotime("+21 days 23:00")) $x="Přespříští ".$this->day[$day];
    else $x=date("j.n.",$y);
    return $x;
    }
  public $lesson_start=array(0=>26700,1=>29700,2=>33000,3=>36900,4=>40200,5=>43500,6=>46800,7=>49800,8=>52800,9=>55800);
  public $lesson_end  =array(0=>29400,1=>32400,2=>35700,3=>39600,4=>42900,5=>46200,6=>49500,7=>52500,8=>55500,9=>58500);
  function getElementsByClassName($elements,$className) {
    $matched=array();
    foreach($elements as $node) {
      if(!$node->hasAttributes()) continue;
      $classAttribute=$node->attributes->getNamedItem('class');
      if(!$classAttribute) continue;
      $classes=explode(' ',$classAttribute->nodeValue);
      if(in_array($className,$classes)) $matched[]=$node;
      }
    return $matched;
    }
  }
?>
