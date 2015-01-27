<?php /* ACC
       * znamky.vsechno-atd.cz/php/core/acc.php
       * třída pro správu účtů na Známkách, přistupná jen pro skript
       * POZOR funkce nekontrolují stav přihlášení a vztah k účtu, je třeba volat až po autorizaci!!! */

class ACC {

  public $MAIN;
  function __construct() {
    require_once 'main.php'; $this->MAIN=new MAIN();
    /* kontrola validního přihlášení */
    if($_SESSION['accID']!=$_COOKIE['zai'] || $_SESSION['accToken']!=$_COOKIE['zat']) {
      $this->doLogout();
      }
    /* trvalé přihlášení */
    if($_COOKIE['zpl']) {
      $permToken=$_COOKIE['zpl'];
      $accID=$this->MAIN->mysqli->query('SELECT accID FROM permLogin WHERE token="'.$this->MAIN->mysqli->real_escape_string($permToken).'"')->fetch_row()[0];
      if($accID) {
        session_regenerate_id(true);
        $this->MAIN->mysqli->query('UPDATE permLogin (expires) VALUES ("'.(time()+1000).'") WHERE token="'.$this->MAIN->mysqli->real_escape_string($permToken).'"');
        $this->doLogin($accID);
        $this->doPermLogin($accID);
        }
      }
    }




  function checkLogin($nick,$password) {
    /* kontrola hesla a jména, zda souhlasí
     * INPUT zadaný nick, zadané heslo
     * RETURN [accID,nick] nebo false když nepovedlo  */

    $this->MAIN->mysqli->select_db('vsechno-atdcz_znamky');
    $a=$this->MAIN->mysqli->query('SELECT passSalt FROM acc WHERE nick="'.$this->MAIN->mysqli->real_escape_string($nick).'"')->fetch_row();
    $s=$this->MAIN->mysqli->query('SELECT count(*),accID,nick FROM acc WHERE nick="'.$this->MAIN->mysqli->real_escape_string($nick).'" AND password="'.hash('sha512',sha1($a[0]).$password.'ZNÁMKY-salt').'"')->fetch_row();
    if($s[0]!=1) return false;
    else return array($s[1],$s[2]);

    }
  
  
  function doLogin($accID,$nick) {
    /* login - zapsání do relace atd
     * INPUT accID, nick
     * RETURN true nebo false když nepovedlo  */

    session_regenerate_id(true);
    setcookie('zai',$accID,null,"/","znamky.vsechno-atd.cz");
    $this->MAIN->mysqli->select_db('vsechno-atdcz_znamky');
    $this->MAIN->mysqli->query('UPDATE acc SET lastLogin='.time().' WHERE accID='.$accID);
    $accToken=md5(mt_rand(1,999999));
    $_SESSION['accToken']=$accToken;
    setcookie('zat',$accToken,null,'/','znamky.vsechno-atd.cz');
    $_SESSION['accID']=$accID;
    $_SESSION['nick']=$nick;
    return true;
    }
  
  
  function doPermLogin($accID) {
    /* permLogin - zapsání do cookies atd
     * INPUT accID
     * RETURN true nebo false když nepovedlo  */

	  $permToken=md5(uniqid('', true));
	  $expires=strtotime('+5 month');
	  setcookie('zpl',$permToken,$expires,'/','znamky.vsechno-atd.cz');
	  $this->MAIN->mysqli->query('INSERT INTO permLogin (accD,token,expires) VALUES ('.$r[0].',"'.$permToken.'",'.$expires.')');
    return true;
    }
  
  
  function doLogout($accID) {
    /* logout - odstranění z relace a permLogout
     * INPUT accID, nick
     * RETURN true nebo false když nepovedlo  */

    session_regenerate_id(true);
    setcookie('zai',null,1,"/","znamky.vsechno-atd.cz");
    setcookie('zat',null,1,'/','znamky.vsechno-atd.cz');
	  setcookie('zpl',null,1,'/','znamky.vsechno-atd.cz');
	  $permToken=$_COOKIE['zpl'];
	  $this->MAIN->mysqli->query('DELETE FROM permLogin WHERE token="'.$this->MAIN->mysqli->real_escape_string($permToken).'"');
    $accToken=md5(mt_rand(1,999999));
    unset($_SESSION['accToken'],$_SESSION['accID'],$_SESSION['nick']);
    return true;
    }


  function signup($nick,$password) {
    /* normální signup
     * INPUT zadaný nick, zadané heslo
     * RETURN [accID] nebo 2 když nick obsazen nebo false když nepovedlo  */

    $this->MAIN->mysqli->select_db('vsechno-atdcz_znamky');
    $a=$this->MAIN->mysqli->query('SELECT count(*) FROM acc WHERE nick="'.$this->MAIN->mysqli->real_escape_string($nick).'"')->fetch_row();
    if($a[0]!=0) return 2;
    $passSalt=mt_rand(1000000,9999999);
    $this->MAIN->mysqli->query('INSERT INTO acc (nick,password,passSalt,lastLogin,signup) VALUES ("'.$this->MAIN->mysqli->real_escape_string($nick).'", "'.hash('sha512',sha1($passSalt).$password.'ZNÁMKY-salt').'",'.$passSalt.','.time().','.time().')');
    if($this->MAIN->mysqli->affected_rows!==1) return false;
    return array($this->MAIN->mysqli->insert_id);

    }


  function changeEmail($accID,$email) {
    /* změna emailu účtu
     * INPUT accID, zadaný email
     * RETURN true nebo 2 když email obsazen nebo false když nepovedlo  */

    $this->MAIN->mysqli->select_db('vsechno-atdcz_znamky');
    $a=@$this->MAIN->mysqli->query('SELECT count(*) FROM acc WHERE email="'.$this->MAIN->mysqli->real_escape_string($email).'"')->fetch_row();
    if($a[0]!=0) return 2;
    $hash=substr(md5(mt_rand(1000000,9999999)),0,20);
    $this->MAIN->mysqli->query('INSERT INTO emailVerify (accID,email,hash,time) VALUES ('.$accID.', "'.$this->MAIN->mysqli->real_escape_string($email).'","'.$hash.'",'.time().')');
    if($this->MAIN->mysqli->affected_rows!==1) return false;
    $to     =$email;
    $fromNam='=?UTF-8?B?'.base64_encode('Známky').'?=';
    $subject='=?UTF-8?B?'.base64_encode('Ověření emailu').'?=';
    $headers="From: $fromNam <znamky@vsechno-atd.cz>\r\n"."MIME-Version: 1.0"."\r\n"."Content-type: text/html; charset=UTF-8"."\r\n";
    $message='<meta charset="UTF-8">Ahoj!<br>Vypadá to, že jsi se zaregistroval do Známek.<br>Ověř prosím svůj email kliknutím na odkaz níže:<br><a href="http://znamky.vsechno-atd.cz/php/emailVerify.php?hash='.$hash.'">http://znamky.vsechno-atd.cz/php/emailVerify.php?hash='.$hash.'</a><br>Pokud jsi to nebyl ty, nic nemusíš řešit a email se brzy automaticky odstraní.<br><br>Doufáme, že budeš s naší aplikací spokojen :)<br><b>Známky - jednoduchý správce známek z bakalářů</b><br><a href="http://znamky.vsechno-atd.cz">znamky.vsechno-atd.cz</a>';
    $message=wordwrap($message,70,"\r\n");
    if(!mail($to,$subject,$message,$headers)) return false;
    return true;

    }


  function verifyEmail($hash) {
    /* ověření emailu
     * INPUT ověřovací hash
     * RETURN true nebo false když nepovedlo  */

    $this->MAIN->mysqli->select_db('vsechno-atdcz_znamky');
    $a=$this->MAIN->mysqli->query('SELECT count(*),accID,email FROM emailVerify WHERE hash="'.$this->MAIN->mysqli->real_escape_string($hash).'"')->fetch_row();
    if($a[0]!=1) return false;
    $this->MAIN->mysqli->query('UPDATE acc SET email="'.$a[2].'" WHERE accID='.$a[1]);
    $this->MAIN->mysqli->query('DELETE FROM emailVerify WHERE accID='.$a[1]);
    return true;

    }


  function getZdroje($accID) {
    /* výpis zdrojů z databáze Známek
     * INPUT accID
     * RETURN [zdroje[zdrojID,url,skola,jmeno,nove,posledniKontrola,chyba]] nebo false když žádné nalezené zdroje  */

    $this->MAIN->mysqli->select_db('vsechno-atdcz_znamky');
    $r=@$this->MAIN->mysqli->query('SELECT bakaUcetID,url,skola,jmeno,nove,posledniKontrola,chyba FROM bakaUcty WHERE accID='.$accID);
    if($r->num_rows<1) return false;
    $zdroje=array();
    while($a=$r->fetch_array()) {
      $zdroje[]=array($a['bakaUcetID'],$a['url'],$a['skola'],$a['jmeno'],$a['nove'],$a['posledniKontrola'],$a['chyba']);
      }
    return array($zdroje);

    }



  }