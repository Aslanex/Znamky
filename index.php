<?
  require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <script type="text/javascript" src="http://vsechno-atd.cz/js/jquery"></script>
    <script type="text/javascript" src="/js/js"></script>
    <link rel="stylesheet" type="text/css" href="/css/css">
    <script type="text/javascript" src="/js/index"></script>
    <link rel="stylesheet" type="text/css" href="/css/index">
    <title>Známky - jednoduchý správce známek z bakalářů</title>
    <link rel="icon" type="image/png" href="/img/favicon<? echo mt_rand(1,18) ?>">
  </head>
  <body>
  <div id="obalStranky">
<?

  $ucty=$funkce->mysqli->query('SELECT bakaUcetID,jmeno,skola,nove,posledniKontrola,chyba FROM bakaUcty WHERE ucetID='.$_SESSION['ucetID']);
  if($ucty->num_rows>0) { ?>
<div id="obsahStranky">
  <div class="obsahStrankyDiv leftStranka prihlasenWelcome">
    <img src="/img/welcome2" alt="ZNÁMKY">
    <div class="welcomeNadpis4">
      <span class="nickNadpis"><? echo $_SESSION['nick'] ?></span><br>
      <a href="javascript:void(odhlaseni())">odhlásit</a> - <a href="//vsechno-atd.cz/settings">nastavení</a><br><br>
      Vítej ve Známkách. Vše by mělo fungovat, jak má.<br>
      Vyzkoušej novinku Když dostanu - klikni na plus pod detailem předmětu.<br>
      Ber prosím na vědomí, že naše aplikace pololetní známky nezobrazuje. Promiň.
      <br><br>
      Ovlivni prosím vývoj Známek <a href="http://bit.ly/1lhlyVh">vyplněním dotazníku</a>!
    </div>
  </div>
  <div class="obsahStrankyDiv rightStranka vyberZdroje">
    <div id="section1" class="vyberZdroje">
      <? while($a=$ucty->fetch_assoc()) {
        $nove=($a['nove']==0)
          ? 'žádné nové známky' : ( ($a['nove']==1)
            ? '<span style="color:firebrick">nová známka</span>' : ( ($a['nove']<5)
              ? '<span style="color:firebrick">'.$a['nove'].' nové známky</span>' : '<span style="color:firebrick">'.$a['nove'].' nových známek!</span>' ) );
        $lc=date_diff(date_create('@'.$a['posledniKontrola']),date_create())->h;
        $lct= ($a['chyba']>1) ? $lc.' hod (nelze se spojit s bakaláři)' : $lc.' hod';
        echo '<section onclick="if(event.which==1) location.href=\'/zobraz?bakaUcet='.$a['bakaUcetID'].'\'"><header><div class="jmeno">'.$a['jmeno'].'</div><div class="skola">'.$a['skola'].'</div><a href="/spravuj?bakaUcet='.$a['bakaUcetID'].'" class="spravujZdroj"></a>';
        echo ($lc>=3)
          ? '<a href="javascript:void(aktualizujZdroj('.$a['bakaUcetID'].'));" onclick="event.stopPropagation()" class="aktualizujZdroj" pozor id="aktualizujZdroj'.$a['bakaUcetID'].'"></a><div class="aktualizujZdroj" pozor id="aktualizujZdroj'.$a['bakaUcetID'].'Span">'.$lct.'</div>'
          : '<a href="javascript:void(aktualizujZdroj('.$a['bakaUcetID'].'));" onclick="event.stopPropagation()" class="aktualizujZdroj" id="aktualizujZdroj'.$a['bakaUcetID'].'"></a><div class="aktualizujZdroj" id="aktualizujZdroj'.$a['bakaUcetID'].'Span"></div>';
        echo '</header><div class="data" id="zdroj'.$a['bakaUcetID'].'Data">'.$nove.'</div></section>';
        }
      ?>
      <a href="javascript:void(section2())" class="pridat">přidat zdroj / prohlížet anonymně</a>
    </div>
    <div id="section2">
      <form id="welcomeForm" action="/znamky" method="post" onsubmit="zkusZnamky();return false" class="welcomeForm">
        <select id="bakaUrlSelect" style="margin:30px 0 0 2px;" required onchange="changeBakaUrl()" onfocus="$('#bakaUrl').css('border-color','');$('#bakaUrlSelect').css('border-color','');">
          <option selected disabled>vyber školu</option>
          <?
          $funkce->mysqli->select_db('vsechno-atdcz_znamky');
          $r=$funkce->mysqli->query('SELECT nazev,adresa FROM skoly');
          while($a=$r->fetch_assoc()) echo '<option value="'.htmlspecialchars($a['adresa']).'">'.htmlspecialchars($a['nazev']).'</option>';
          ?>
        </select>
        <input id="bakaUrl" name="bakaUrl" placeholder="URL adresa přihlašovací stránky do bakalářů" style="display:none;margin:30px 0 0 2px;height:21px;" onfocus="$('#bakaUrl').css('border-color','');$('#bakaUrlSelect').css('border-color','');"><br>
        <a id="bakaUrlSwitch" href="javascript:void(bakaUrlText())" class="formComment">není tu moje škola / zadat adresu přihlašovací stránky</a><br>
        <input id="bakaPrihlJmeno" name="bakaPrihlJmeno" placeholder="přihlašovací jméno do bakalářů" required onfocus="$('#bakaPrihlJmeno').css('border-color','');"><br>
        <input id="bakaHeslo" name="bakaHeslo" type="password" placeholder="heslo do bakalářů" required onfocus="$('#bakaHeslo').css('border-color','');"><br>
        <button id="welcomeButton" type="submit" style="width:250px">ukázat známky!</button>
        <input id="bakaJmeno" name="jmeno" type="hidden">
        <input id="bakaSkola" name="skola" type="hidden">
        <input id="bakaCkfile" name="ckfile" type="hidden">
      </form>
      <div class="formPreloader" id="welcomePreloader"></div>
      <br><a href="javascript:void(section1())" class="pridat">zpět na přehled zdrojů</a>
    </div>
  </div>
</div>
<?  }
  else { ?>
<div id="obsahStranky">
  <div class="obsahStrankyDiv leftStranka">
    <img src="/img/welcome" alt="ZNÁMKY">
    <div class="welcomeNadpis2">
      JEDNODUCHÝ SPRÁVCE<br>
      ZNÁMEK Z BAKALÁŘŮ
    </div>
    <div class="welcomeNadpis3">
      > rychlý přístup<br>
      > intuitivní design<br>
      > šikovné nástroje
    </div>
  </div>
  <div class="obsahStrankyDiv rightStranka">
    <div id="section1">
      Je to jednoduché a zdarma, zkus hned teď.<br>
      <form id="welcomeForm" action="/znamky" method="post" onsubmit="zkusZnamky();return false" class="welcomeForm">
        <select id="bakaUrlSelect" style="margin:30px 0 0 2px;" required onchange="changeBakaUrl()" onfocus="$('#bakaUrl').css('border-color','');$('#bakaUrlSelect').css('border-color','');">
          <option selected disabled>vyber školu</option>
          <?
          $funkce->mysqli->select_db('vsechno-atdcz_znamky');
          $r=$funkce->mysqli->query('SELECT nazev,adresa FROM skoly');
          while($a=$r->fetch_assoc()) echo '<option value="'.htmlspecialchars($a['adresa']).'">'.htmlspecialchars($a['nazev']).'</option>';
          ?>
        </select>
        <input id="bakaUrl" name="bakaUrl" placeholder="URL adresa přihlašovací stránky do bakalářů" style="display:none;margin:30px 0 0 2px;height:21px;" onfocus="$('#bakaUrl').css('border-color','');$('#bakaUrlSelect').css('border-color','');"><br>
        <a id="bakaUrlSwitch" href="javascript:void(bakaUrlText())" class="formComment">není tu moje škola / zadat adresu přihlašovací stránky</a><br>
        <input id="bakaPrihlJmeno" name="bakaPrihlJmeno" placeholder="přihlašovací jméno do bakalářů" required onfocus="$('#bakaPrihlJmeno').css('border-color','');"><br>
        <input id="bakaHeslo" name="bakaHeslo" type="password" placeholder="heslo do bakalářů" required onfocus="$('#bakaHeslo').css('border-color','');"><br>
        <button id="welcomeButton" type="submit" style="width:250px">ukázat známky!</button>
        <input id="bakaJmeno" name="jmeno" type="hidden">
        <input id="bakaSkola" name="skola" type="hidden">
        <input id="bakaCkfile" name="ckfile" type="hidden">
      </form>
      <div class="formPreloader" id="welcomePreloader"></div>
      <br><br>
      <?
        if(!$_SESSION['ucetID']) echo '<a href="javascript:void(section2())">nebo přihlásit >></a>';
        else echo '<span style="color:#888">přihlášen jako '.$_SESSION['nick'].'</span>';
      ?>

    </div>
    <div id="section2">
      Přihlaš se, abys viděl své známky:<br>
      <form id="loginForm" onsubmit="login();return false" class="loginForm">
        <input id="accNick" placeholder="nick" required onfocus="$('#bakaPrihlJmeno').css('border-color','');"><br>
        <input id="accPassword" type="password" placeholder="heslo" required onfocus="$('#bakaHeslo').css('border-color','');"><br>
        <span class="formComment"><input id="accPerm" type="checkbox"> neodhlašovat po zavření prohlížeče<br></span>
        <button id="loginButton" type="submit" style="width:250px">přihlásit!</button>
      </form>
      <div class="formPreloader" id="loginPreloader"></div>
      <br><br>
      <a href="http://vsechno-atd.cz/prihlaseni" style="color:red;text-decoration:underline">V nedávné době proběhly změny v přihlášení.<br>Pokud ti toto přihlášení nefunguje, klikni prosím sem.</a>
      <br><br>
      <a href="javascript:void(section1())" class="pridat">zpět na anonymní prohlížení</a>
    </div>
</div>
<? }


   $funkce->htmlKonec();
