
function ulozPage() {
  $('#znamkyPreview').hide(100);
  $('#znamkyUloz').show(100);
  }

/* uložení do aktuálního účtu */
function ulozZdroj() {
  $('#ulozPrihlaseny').attr('disabled',true);
  $('#ulozZdrojPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")').html('');
  $.post('/php/ulozZdroj','bakaUrl='+bakaUrl+'&bakaPrihlJmeno='+bakaPrihlJmeno+'&bakaHeslo='+bakaHeslo,function(data) {
    r=JSON.parse(data);
    switch(r.s) {
      default: switch(r.e) {
        default: $('#ulozZdrojPreloader').css('background-image','').html('neznámá chyba, zkus to znovu'); break;
        case 3: $('#ulozZdrojPreloader').css('background-image','').html('nezdařilo se přidat zdroj známek, přidej ho prosím <a href="/pridej">tady</a>'); break;
        case 4: $('#ulozZdrojPreloader').css('background-image','').html('tenhle zdroj už máš, a dvakrát ho nechceš ;) <a href="/">domů</a>'); break;
        } $('#ulozPrihlaseny').attr('disabled',false); break;
      case 1: 
        $('#znamkyUloz').hide(100);
        $('#ulozeno').show(100);
      }
    });
  }

/* registrace */
var regNickTimeout, regHesloTimeout, regKHesloTimeout, regEmailTimeout;
function zkontrolujNick() {
  if(!$('#regNick').val() || $('#regNick').val()=='') {
    $('#regNickT').html('4 - 40 znaků');
    $('#regNickT').css('color','#666');
    }
  else if($('#regNick').val().length<4) {
    $('#regNickT').html('příliš krátký');
    $('#regNickT').css('color','red');
    }
  else if($('#regNick').val().length>40) {
    $('#regNickT').html('příliš dlouhý');
    $('#regNickT').css('color','red');
    }
  else {
    $.post('php/registruj?zkontrolujNick','nick='+$('#regNick').val(),function(text) {
      if(text=='occuppied') {
        $('#regNickT').html('již obsazen, vyber jiný (<a href="">nemáš už účet?</a>)');
        $('#regNickT').css('color','red');
        }
      else {
        $('#regNickT').html('4 - 40 znaků');
        $('#regNickT').css('color','#666');
        }
      });
    }
  }

function zkontrolujHeslo() {
  if(!$('#regHeslo').val() || $('#regHeslo').val()=='') {
    $('#regHesloT').html('6 - 40 znaků');
    $('#regHesloT').css('color','#666');
    }
  else if($('#regHeslo').val().length<6) {
    $('#regHesloT').html('příliš krátký');
    $('#regHesloT').css('color','red');
    }
  else if($('#regHeslo').val()==$('#regNick').val()) {
    $('#regHesloT').html('nesmí se shodovat s nickem');
    $('#regHesloT').css('color','red');
    }
  else {
    $('#regHesloT').html('6 - 40 znaků');
    $('#regHesloT').css('color','#666');
    porovnejHesla();
    }
  }

function porovnejHesla() {
  if(!$('#regKHeslo').val() || $('#regKHeslo').val()=='' || !$('#regHeslo').val() || $('#regHeslo').val()=='') {
    $('#regKHesloT').html('');
    $('#regKHesloT').css('color','#666');
    }
  else if($('#regHeslo').val()!=$('#regKHeslo').val()) {
    $('#regKHesloT').html('hesla se neshodují');
    $('#regKHesloT').css('color','red');
    } 
  else {
    $('#regKHesloT').html('');
    $('#regKHesloT').css('color','#666');
    }
  }

function zkontrolujEmail() {
  if(!$('#regEmail').val() || $('#regEmail').val()=='') {
    $('#regEmailT').html('není nutný, jen k obnovení hesla');
    $('#regEmailT').css('color','#666');
    }
  else if($('#regEmail').val().length<5 || !/.+@.+/.test($('#regEmail').val())) {
    $('#regEmailT').html('neplatný email');
    $('#regEmailT').css('color','red');
    }
  else {
    $.post('php/registruj?zkontrolujEmail','email='+$('#regEmail').val(),function(text) {
      if(text=='occuppied') {
        $('#regEmailT').html('již použit (<a href="">nemáš už účet?</a>)');
        $('#regEmailT').css('color','red');
        }
      else {
        $('#regEmailT').html('není nutný, jen k obnovení hesla');
        $('#regEmailT').css('color','#666');
        }
      });
    }
  }

function registruj() {
  if($('#regNick').val().length<4) {
    $('#registrujPreloader').html('zvolený nick je příliš krátký');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else if($('#regNick').val().length>40) {
    $('#registrujPreloader').html('zvolený nick je příliš dlouhý');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else if($('#regHeslo').val().length<6) {
    $('#registrujPreloader').html('zvolené heslo je příliš krátké');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else if($('#regHeslo').val().length>60) {
    $('#registrujPreloader').html('zvolené heslo je příliš dlouhé');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else if($('#regHeslo').val()==$('#regNick').val()) {
    $('#registrujPreloader').html('heslo se nesmí shodovat s nickem');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else if($('#regHeslo').val()!=$('#regKHeslo').val()) {
    $('#registrujPreloader').html('heslo znovu se neshoduje s původním heslem (musí být stejná)');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else if(!$('#regNick').val() || !$('#regHeslo').val() || !$('#regKHeslo').val() || !$('#recaptcha_response_field').val()) {
    $('#registrujPreloader').html('vyplň všechny potřebné údaje (nick, 2× heslo, kontrola)');
    setTimeout(function() { $('#registrujPreloader').html(''); },5000);
    }
  else {
    $('#welcomeButton').attr('disabled',true);
    $('#registrujPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")').html('');
    $.post('/php/registruj?zaregistruj','nick='+$('#regNick').val()+'&heslo='+$('#regHeslo').val()+'&captchaResp='+$('#recaptcha_response_field').val()+'&captchaChal='+$('#recaptcha_challenge_field').val()+'&email='+$('#regEmail').val()+'&bakaUrl='+bakaUrl+'&bakaPrihlJmeno='+bakaPrihlJmeno+'&bakaHeslo='+bakaHeslo,function(data) {
      r=JSON.parse(data);
      switch(r.s) {
        default: $('#registrujPreloader').css('background-image','').html('neznámá chyba, zkus to znovu'); break;
        case 0: switch(r.e) {
          default: $('#registrujPreloader').css('background-image','').html('neznámá chyba, zkus to znovu'); break;
          case 1: $('#registrujPreloader').css('background-image','').html('chyba v údajích, překontroluj je'); break;
          case 2: $('#registrujPreloader').css('background-image','').html('kontrola proti robotům nesouhlasí, zkus to znovu'); Recaptcha.reload(); break;
          case 3: $('#registrujPreloader').css('background-image','').html('nezdařilo se přidat zdroj známek, přidej ho prosím <a href="/pridej">tady</a>'); break;
          } $('#welcomeButton').attr('disabled',false); break;
        case 1: 
          $('#znamkyUloz').hide(100);
          $('#ulozeno').show(100);
        }
    });
    }
  }
