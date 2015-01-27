<?
  require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->zacatek('znamky','načítám známky...');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $funkce->zpetButton();

  if(!$_POST['jmeno'] || !$_POST['skola'] || !$_POST['ckfile'] || !$_POST['bakaUrl']) exit('Požadavek nelze zpracovat - chybí data.<br><a href="/">domů</a>');

  /* html loader */

  echo '<div id="obsahStranky"><meta name="robots" content="noindex"><div id="preloaderDiv">'.$_POST['skola'].'<br>'.$_POST['jmeno'].'<br><br><div class="lpb"><div id="lpt" style="width:40%"></div></div>zjišťuji známky, počkej prosím...</div>'.str_repeat(' ',1024*64); flush();

  /* inicializace */

  $bodyUvod=new DOMDocument; $bodyZnamky=new DOMDocument(); $bodyPololeti=new DOMDocument(); $json['predmety']=array();

  $ch=curl_init($_POST['bakaUrl'].'uvod.aspx');
  curl_setopt($ch,CURLOPT_COOKIEJAR,'/home/www/vsechno-atd.cz/tmp/'.$_POST['ckfile']);
  curl_setopt($ch,CURLOPT_COOKIEFILE,'/home/www/vsechno-atd.cz/tmp/'.$_POST['ckfile']);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'login.aspx');
  $html=curl_exec($ch);
  if(!strpos($html,'<div class="nazevskoly">')) exit('Požadavek nelze zpracovat - nepřihlášen.');

  @$bodyUvod->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $znamkyURL= $bodyUvod->getElementById('hlavnimenu_DXM2_')->getElementsByTagName('div')->item(0)->getElementsByTagName('ul')->item(0)->getElementsByTagName('li')->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href');


  echo '<script>$("#lpt").css("width","80%")</script>'; echo str_repeat(' ',1024*64); flush();

  /* čtení předmětů */

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'uvod.aspx');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].'prehled.aspx?s=4');
  $html=curl_exec($ch);
  $bodyPololeti->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $tablePololeti=$funkce->getElementsByClassName($bodyPololeti->getElementsByTagName('table'),'tablepolo')[0];
  $predmetyPololeti=$tablePololeti->getElementsByTagName('tr');
  foreach($predmetyPololeti as $predmet) {
    if(!$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]) continue;
    $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'poloznamka');
    $znamkyPololeti[trim($funkce->getElementsByClassName($predmet->getElementsByTagName('td'),'polonazev')[0]->nodeValue)]=$znamky[(sizeof($znamky)-1)]->nodeValue;
    }

  echo '<script>$("#lpt").css("width","100%");</script>'; echo str_repeat(' ',1024*64); flush();

  /* čtení známek */

  curl_setopt($ch,CURLOPT_REFERER,$_POST['bakaUrl'].'prehled.aspx?s=4');
  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].$znamkyURL);
  $html=curl_exec($ch);
  @$bodyZnamky->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $tableZnamky=$funkce->getElementsByClassName($bodyZnamky->getElementsByTagName('table'),'radekznamky')[0];
  $predmety=$tableZnamky->getElementsByTagName('tr');
  if($predmety->length<1) $content='<div class="znamky">Vypadá to, že nemáš žádné známky.</div>';
  else {
    $pololeti=(date('n')<1 || date('n')>7) ? 1 : 2;
    $zacatekPololeti=(date('n')==1) ? mktime(0,0,0,9,1,date('Y')-1) : ($pololeti==1) ? mktime(0,0,0,9,1) : mktime(0,0,0,2,1);
    $currCont='';
    foreach($predmety as $predC=>$predmet) {
      if(sizeof($funkce->getElementsByClassName($predmet->getElementsByTagName('table'),'znmala'))<1) continue;
      $nazev=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('a'),'nazevpr')[0]->nodeValue);
      $prumer=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detprumerdiv')[0]->nodeValue);
      $ctvrtleti=trim($funkce->getElementsByClassName($predmet->getElementsByTagName('div'),'detzn')[0]->nodeValue);
      $pololeti=trim($znamkyPololeti[$nazev]);
      $vysledna=($pololeti) ? $pololeti : ( ($ctvrtleti) ? $ctvrtleti : '&nbsp;' ) ;
      $vyslednaClass=($pololeti) ? 'pololetniZnamka' : ( ($ctvrtleti) ? 'ctvrtletniZnamka' : '' ) ;

      $znamky=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'detznamka')[0]->getElementsByTagName('td');
      $vahy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]) ? $funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'typ')[0]->getElementsByTagName('td') : null;
      $datumy=($funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]) ? $datumy=$funkce->getElementsByClassName($predmet->getElementsByTagName('tr'),'datum')[0]->getElementsByTagName('td') : null;

      $znamkyA=array();
      $poradiMinus=0;
      $vypisHtml='';
      foreach($znamky as $c=>$znamka) {
        $datum=($datumy) ? explode('.',trim($datumy->item($c)->nodeValue)) : null;
        $vaha=($vahy) ? ((trim($vahy->item($c)->nodeValue)=='X') ? 10 : trim($vahy->item($c)->nodeValue)) : null;

        $fontSize=($vahy) ? $vaha : 6;
        $vypisHtml.='<article style="font-size:'.(20+$fontSize*2).'px'.$nova.'">'.$znamka->nodeValue.'</article>';

        $poradi=$c-$poradiMinus;
        if($lastPopis==$znamka->getAttribute('title') && $lastDatum==mktime(0,0,0,$datum[1],$datum[0],$datum[2]) && $lastVaha==$vaha) {
          $poradi--; $poradiMinus++;
          $znamkyA[$poradi]->znamka.=', '.$znamka->nodeValue;
          }
        else {
          $znamkyA[$poradi]=new StdClass;
          $znamkyA[$poradi]->znamka=trim($znamka->nodeValue);
          $znamkyA[$poradi]->popis=trim($znamka->getAttribute('title'));
          $znamkyA[$poradi]->vaha=$vaha;
          $znamkyA[$poradi]->mesic=($datum) ? date('n',mktime(0,0,0,$datum[1],$datum[0],$datum[2])) : -1;
          $znamkyA[$poradi]->fdatum=($datum) ? date('j.n.y',mktime(0,0,0,$datum[1],$datum[0],$datum[2])) : 'bez data';
          $lastPopis=$znamkyA[$poradi]->popis; $lastVaha=$znamkyA[$poradi]->vaha; $lastDatum=mktime(0,0,0,$datum[1],$datum[0],$datum[2]);
          }
        }

      $ctvrtletniPodrobnost=($pololeti && $ctvrtleti) ? '<div class="ctvrtletniPodrobnost">čtvrtletí: '.$ctvrtleti.'</div>' : '';
      $podrobnostiHtml = '</div><div class="podrobnostiPredmetu2" id="podrobnostiPredmetu'.$predC.'">'.$ctvrtletniPodrobnost.'<div class="podrobnostiPredmetu2Znamky" onclick="event.stopPropagation()">';

      $mesic=-2; $mesicC=0; $detailHtml='';
      foreach($znamkyA as $c=>$a) {
        if($a->mesic>$mesic) {
          $detailHtml.='<div class="znamkyMesic">'.$funkce->month[($a->mesic-1)].'</div>';
          $mesic=$a->mesic; $mesicC++;
          $margin=' style="margin-top:4px"';
          }
        else $margin='';
        $vaha=($a->vaha) ? ', v'.$a->vaha : '';
        $detailHtml.='<article'.$margin.'><b>'.$a->znamka.'</b> '.$a->popis.' <span>'.$a->fdatum.$vaha.'</span></article>';
        }
      $articleHeight = (sizeOf($znamkyA)*17+$mesicC*4+44);
      $detailHtml.='</div></div></section>';
      $currCont .= '<section onclick="podrobnostiPredmetu('.$predC.')" id="sectionPredmetu'.$predC.'" data-article-height='.$articleHeight.'><div class="nazevPredmetu">'.$nazev.'</div><div class="vyslednaPredmetu '.$vyslednaClass.'">'.$vysledna.'</div><div class="prumerPredmetu">'.$prumer.'</div><div class="znamkyPredmetu" id="znamkyPredmetu'.$predC.'">'.$vypisHtml.$podrobnostiHtml.$detailHtml;
      }
    $content = '<div id="znamkyPreview" class="znamky">'.$currCont;
    }

  echo $content.'<script>$("#preloaderDiv").html("");document.title="Známky '.$_POST['jmeno'].'";var bakaUrl="'.$_POST['bakaUrl'].'",bakaPrihlJmeno="'.$_POST['bakaPrihlJmeno'].'",bakaHeslo="'.$_POST['bakaHeslo'].'"</script>';
?>
  <div style="font-size:22px;margin:5px 0">Vypadá to dobře? <a href="javascript:void(ulozPage())">ULOŽIT!</a></div></div>
  <div id="znamkyUloz" style="display:none">
    <div class="obsahStrankyDiv leftStranka">
      <img src="/img/welcome">
      <div class="welcomeNadpis2">
        PROČ SE REGISTROVAT?
      </div>
      <div class="welcomeNadpis3">
        > RYCHLEJŠÍ NAČÍTÁNÍ<br>
        > PODROBNĚJŠÍ VÝPIS<br>
        > UPOZORNĚNÍ<br>
      </div>
    </div>
    <div class="obsahStrankyDiv rightStranka">
    <?php if(!$_SESSION['ucetID']) { ?>
      Zvol si své přihlašovací údaje.<br>
      <form id="saveForm" onsubmit="registruj();return false">
      <input id="regNick" placeholder="nick" maxlength='40' required onfocus="$('#regNick').css('border-color','');" onkeyup="clearTimeout(regNickTimeout);regNickTimeout=setTimeout(function(){zkontrolujNick()},500)" style="margin-top:30px"> <span id="regNickT">4 - 40 znaků</span><br>
      <input id="regHeslo" type="password" maxlength='40' placeholder="heslo" required onkeyup="clearTimeout(regHesloTimeout);regHesloTimeout=setTimeout(function(){zkontrolujHeslo()},500)" onfocus="$('#regHeslo').css('border-color','');"> <span id="regHesloT">6 - 30 znaků</span><br>
      <input id="regKHeslo" type="password" maxlength='40' placeholder="heslo znovu" required onkeyup="clearTimeout(regKHesloTimeout);regKHesloTimeout=setTimeout(function(){porovnejHesla()},500)" onfocus="$('#regKHeslo').css('border-color','');"> <span id="regKHesloT"></span><br>
      <input id="regEmail" type="email" placeholder="email" onkeyup="clearTimeout(regEmailTimeout);regEmailTimeout=setTimeout(function(){zkontrolujEmail()},500)" onfocus="$('#regEmail').css('border-color','');"> <span id="regEmailT">není nutný, jen k obnovení hesla</span><br>
      <div class="input"><script type="text/javascript">
            var RecaptchaOptions = {
              theme : 'custom',
              custom_theme_widget: 'recaptcha_widget'
              };
          </script>
          <div id="recaptcha_widget">
            <div id="recaptcha_image"></div>
            <input type="text" id="recaptcha_response_field" placeholder="kontrola proti robotům ↑" required><br>
            <a href="javascript:Recaptcha.reload()">jiný obrázek</a> -
            <span class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">zvuková kontrola</a> -</span>
            <span class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">normální kontrola</a> -</span>
            <a href="javascript:Recaptcha.showhelp()">nápověda</a> - pomocí reCAPTCHA
          </div>
          <script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=6LcIx9YSAAAAAM-hdFrdMnysl9BzlvU_XJManTDr"></script></div>
      <button id="welcomeButton" type="submit" style="width:300px">uložit známky a zaregistrovat!</button>
      </form>
      <div class="formPreloader" id="registrujPreloader"></div>
      <br><br><a href="http://vsechno-atd.cz/prihlaseni">nebo přihlaš na účet Všeho, atd >></a>
    <?php } else { echo 'Jsi přihlášen jako '.$_SESSION['nick'] ?>.<br>
      Uložit do tohoto účtu?<br>
      <button id="ulozPrihlaseny" style="width:200px" onclick="ulozZdroj();">uložit!</button>
      <div class="formPreloader" id="ulozZdrojPreloader"></div>
      <br><br>
      <a href="javascript:void($('#saveForm').show(100))">nebo se znovu zaregistruj >></a><br>
      <form id="saveForm" onsubmit="registruj();return false" style="display:none">
      <input id="regNick" placeholder="nick" maxlength='40' required onfocus="$('#regNick').css('border-color','');" onkeyup="clearTimeout(regNickTimeout);regNickTimeout=setTimeout(function(){zkontrolujNick()},500)" style="margin-top:30px"> <span id="regNickT">4 - 40 znaků</span><br>
      <input id="regHeslo" type="password" maxlength='40' placeholder="heslo" required onkeyup="clearTimeout(regHesloTimeout);regHesloTimeout=setTimeout(function(){zkontrolujHeslo()},500)" onfocus="$('#regHeslo').css('border-color','');"> <span id="regHesloT">6 - 30 znaků</span><br>
      <input id="regKHeslo" type="password" maxlength='40' placeholder="heslo znovu" required onkeyup="clearTimeout(regKHesloTimeout);regKHesloTimeout=setTimeout(function(){porovnejHesla()},500)" onfocus="$('#regKHeslo').css('border-color','');"> <span id="regKHesloT"></span><br>
      <input id="regEmail" type="email" placeholder="email" onkeyup="clearTimeout(regEmailTimeout);regEmailTimeout=setTimeout(function(){zkontrolujEmail()},500)" onfocus="$('#regEmail').css('border-color','');"> <span id="regEmailT">není nutný, jen k obnovení hesla</span><br>
      <div class="input"><script type="text/javascript">
            var RecaptchaOptions = {
              theme : 'custom',
              custom_theme_widget: 'recaptcha_widget'
              };
          </script>
          <div id="recaptcha_widget">
            <div id="recaptcha_image"></div>
            <input type="text" id="recaptcha_response_field" placeholder="kontrola proti robotům ↑" required><br>
            <a href="javascript:Recaptcha.reload()">jiný obrázek</a> -
            <span class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">zvuková kontrola</a> -</span>
            <span class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">normální kontrola</a> -</span>
            <a href="javascript:Recaptcha.showhelp()">nápověda</a> - pomocí reCAPTCHA
          </div>
          <script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=6LcIx9YSAAAAAM-hdFrdMnysl9BzlvU_XJManTDr"></script></div>
      <button id="welcomeButton" type="submit" style="width:300px">uložit známky a zaregistrovat!</button>
      <div class="formPreloader" id="registrujPreloader"></div>
      </form>
    <?php } ?>
    </div>
  </div>
  <div id="ulozeno" style="display:none">
    Tvůj účet byl uložen, známky budou k dispozici za chvilku na <a href="/">domovské stránce Známek</a>.
  </div>
<?
  $funkce->htmlKonec();
