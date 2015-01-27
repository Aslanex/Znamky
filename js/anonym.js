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
  $('#obsahStranky').html('Nalezená škola: <b>'+skola+'</b> (<a href="javascript:restart()">zpět</a>)<br>Nyní prosím zadej své přihlašovací údaje do Bakalářů. Údaje nebudou nikam zaznamenány. <form onsubmit="dostanBakaweb();return false"><input type="text" class="formInput" id="bakawebPrihlJmeno" placeholder="přihlašovací jméno" required autofocus> <input type="password" class="formInput" id="bakawebHeslo" placeholder="heslo" required> <button type="submit" id="bakawebLoginButton">Načíst Bakaláře</button> <div class="formPreloader" id="bakawebLoginPreloader"></div></form>');
  $('#skola').html(skola);
  }
var predmetHeight=new Array();
function dostanBakaweb() {             // zobrazení známek
  $('#bakawebLoginButton').attr('disabled',true);
  $('#bakawebLoginButton').css('color','#777');
  $('#bakawebLoginPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $('#bakawebPrihlJmeno').attr('readonly',true);
  $('#bakawebHeslo').attr('readonly',true);
  jmeno=$('#bakawebPrihlJmeno').val(); heslo=$('#bakawebHeslo').val();
  $.post('/php/dostanBakaweb','bakawebUrl='+bakawebUrl+'&bakawebPrihlJmeno='+jmeno+'&bakawebHeslo='+heslo,function(data) {
    var r=JSON.parse(data);
    if(r.state=='success') {
      $('#skola').html(r.skola);
      $('#jmeno').html(r.jmeno);
      $('#obsahStranky').html(r.content);/*
      var pololeti=(new Date().getMonth()<1 || new Date().getMonth()>7) ? 1 : 2;
      var html='<div id="znamky">';
      r.predmety.forEach(function(predmet,c) {
        predmetHeight[c]=(predmet.maxRadek*16)+38;
        html+='<section onclick="podrobnostiPredmetu('+c+')" id="sectionPredmetu'+c+'"><div class="nazevPredmetu">'+predmet.nazev+'</div>';
        html+=(predmet.pololetni) ? '<div class="pololetniPredmetu">'+predmet.pololetni+'</div>' : (predmet.ctvrtletni) ? '<div class="ctvrtletniPredmetu">'+predmet.ctvrtletni+'</div>' : '';
        html+=(predmet.prumer) ? '<div class="prumerPredmetu">'+predmet.prumer+'</div>' : '';
        html+='<div class="znamkyPredmetu" id="znamkyPredmetu'+c+'">';
        predmet.znamky.forEach(function(znamky) {
          znamky.znamky.forEach(function(znamka) {
            html+='<article style="font-size:'+(20+(parseInt(znamky.vaha)*2))+'px">'+znamka+'</article>';
            });
          });
        html+='</div><div class="podrobnostiPredmetu1" id="podrobnostiPredmetu'+c+'" style="margin-top:'+((predmet.maxRadek*16)+8)+'px">';
        html+=(predmet.ctvrtletni && predmet.pololetni) ? '<div class="ctvrtletniPodrobnost" style="bottom:'+((predmet.maxRadek*16)-18)+'px">čtvrtletí: '+predmet.ctvrtletni+'</div>' : '';
        html+='<img src="/img/blank" class="timeline" style="background-image: url(\'/img/cara'+pololeti+'\');width: '+r.timelineSize+'px">';
        predmet.znamky.forEach(function(znamky) {
          var znamkyHtml='';
          znamky.znamky.forEach(function(znamka) {
            znamkyHtml+=znamka+', ';
            });
          znamkyHtml=znamkyHtml.slice(0,-2);
          html+='<article style="margin-left:'+znamky.Xpozice+'px;top:-'+((znamky.radek*16)-9)+'px"><b>'+znamkyHtml+'</b> '+znamky.nazev+' <span>'+znamky.fdatum+', váha '+znamky.vaha+'</span></article>';
          });
        html+='</div></section>';
        });
      $('#obsahStranky').html(html);*/
      }
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
