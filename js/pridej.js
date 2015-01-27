var bakawebUrl, oldContent;
function restart() {
  $('#obsahStranky').html(oldContent);
  $('#bakawebUrlPreloader').css('background-image','none');
  $('#bakawebUrl').attr('readonly',false);
  $('#bakawebUrlButton').attr('disabled',false);
  $('#bakawebUrlButton').css('color','darkgoldenrod');
  }
function pripravBakawebUrl() {
  $('#obsahStranky').html('Napiš adresu přihlašovací stránky (<a href="javascript:void(0)" onclick="restart()">zpět</a>): <form id="loginForm" onsubmit="zkusBakaweb();return false;"><input type="text" class="formInput" id="bakawebUrl" placeholder="https://example.cz/login.aspx" required autofocus><button type="submit" id="bakawebUrlButton">najít</button> <div class="formPreloader" id="bakawebUrlPreloader"></div></form>');
  $('#bakawebUrlPreloader').css('background-image','none');
  $('#bakawebUrl').attr('readonly',false);
  $('#bakawebUrlButton').attr('disabled',false);
  $('#bakawebUrlButton').css('color','darkgoldenrod');
  }
function zkusBakaweb() {               // zkouška bakawebu
  $('#bakawebUrlButton').attr('disabled',true);
  $('#bakawebUrlButton').css('color','#777');
  $('#bakawebUrlPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $('#bakawebUrl').attr('readonly',true);
  $.post('/php/zkusBakaweb','bakawebUrl='+$('#bakawebUrl').val(),function(data) {
    var r=JSON.parse(data);
    if(r.state=='success') {
      pripravBakawebLogin(r.bakawebUrl,r.skola);
      }
    else {
      $('#bakawebUrlPreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
      $('#bakawebUrl').attr('readonly',false);
      $('#bakawebUrlButton').attr('disabled',false);
      $('#bakawebUrlButton').css('color','darkgoldenrod');
      $('#bakawebUrl').focus();
      }
    });
  }
function pripravBakawebLogin(url,skola) {  // zkouška bakawebu úspěšná, přihlašovací údaje
  bakawebUrl=url;
  $('#obsahStranky').html('Nalezená škola: <b>'+skola+'</b> (<a href="javascript:restart()">zpět</a>)<br>Nyní prosím zadej své přihlašovací údaje do Bakalářů. Údaje nebudou nikam zaznamenány. <form onsubmit="zkusLogin();return false"><input type="text" class="formInput" id="bakawebPrihlJmeno" placeholder="přihlašovací jméno" required autofocus> <input type="password" class="formInput" id="bakawebHeslo" placeholder="heslo" required> <button type="submit" id="bakawebLoginButton">Načíst Bakaláře</button> <div class="formPreloader" id="bakawebLoginPreloader"></div></form>');
  }
function zkusLogin() {                // zkouška přihlašovacích údajů
  $('#bakawebLoginButton').attr('disabled',true);
  $('#bakawebLoginButton').css('color','#777');
  $('#bakawebLoginPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $('#bakawebPrihlJmeno').attr('readonly',true);
  $('#bakawebHeslo').attr('readonly',true);
  jmeno=$('#bakawebPrihlJmeno').val(); heslo=$('#bakawebHeslo').val();
  $.post('/php/zkusPrihlasovaciUdaje','bakawebUrl='+bakawebUrl+'&bakawebPrihlJmeno='+jmeno+'&bakawebHeslo='+heslo,function(data) {
    var r=JSON.parse(data);
    if(r.state=='success') pripravUlozeni(r.skola,r.jmeno);
    else if(r.state=='tooManyUserAttempts') $('#obsahStranky').html('Kvůli bezpečnostním algoritmům Bakalářů ti bohužel aktuálně nemůžeme dovolit další pokusy o přihlášení.<br>Zkus to prosím za chvíli.');
    else if(r.state=='tooManyAttempts') $('#obsahStranky').html('Bezpečnostní algoritmy Bakalářů bohužel aktuálně nedovolují přihlášení.<br>Zkus to prosím za chvíli.');
    else {
      $('#bakawebLoginButton').attr('disabled',false);
      $('#bakawebLoginButton').css('color','darkgoldenrod');
      $('#bakawebLoginPreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
      $('#bakawebPrihlJmeno').attr('readonly',false);
      $('#bakawebHeslo').attr('readonly',false);
      $('#bakawebHeslo').val('');
      }
    });
  }
function pripravUlozeni(skola,jmeno) {
  $('#obsahStranky').html('Nalezená škola: <b>'+skola+'</b><br>Nalezené jméno: <b>'+jmeno+'</b> (<a href="javascript:pripravBakawebLogin(\''+bakawebUrl+'\',\''+skola+'\')">zpět</a>)<br>Pokud vše souhlasí, pokračuj.<br>Přihlašovací údaje a některé další údaje zjistitelné pomocí těchto přihlašovacích údajů budou uloženy v databázi serveru Známky. Kliknutím na Uložit s tímto souhlasíš.<br><form onsubmit="uloz();return false"><button type="submit" id="ulozeniButton">Uložit</button> <div class="formPreloader" id="ulozeniPreloader"></div></form>');
  }
function uloz() {
  $('#ulozeniButton').attr('disabled',true);
  $('#ulozeniButton').css('color','#777');
  $('#ulozeniPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $.post('/php/ulozUcet','bakawebUrl='+bakawebUrl+'&bakawebPrihlJmeno='+jmeno+'&bakawebHeslo='+heslo,function(data) {
    var r=JSON.parse(data);
    if(r.state=='success') $('#obsahStranky').html('Hotovo.<br>Pokud známky nevypadají, jak by měly, počkej prosím na automatickou aktualizaci (do 30 minut).<br><a href="/">domů</a>');
    else if(r.state=='already') $('#obsahStranky').html('Tento účet již máš přidaný.<br><a href="javascript:void(0)" onclick="location.reload()">znovu</a>, <a href="/">domů</a>');
    else {
      $('#ulozeniButton').attr('disabled',false);
      $('#ulozeniButton').css('color','darkgoldenrod');
      $('#ulozeniPreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
      }
    });
  }
