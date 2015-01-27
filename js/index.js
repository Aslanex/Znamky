var bakaUrlBlinkInterval, bakaPrihlJmenoBlinkInterval, bakaHesloBlinkInterval, formBlinkColor='';
function bakaUrlText() {
  $('#bakaUrlSelect').css('display','none');
  $('#bakaUrl').css('display','inline');
  $('#bakaUrlSwitch').html('vybrat školu ze seznamu');
  $('#bakaUrlSwitch').attr('href','javascript:void(bakaUrlSelect())');
  }
function bakaUrlSelect() {
  $('#bakaUrlSelect').css('display','inline');
  $('#bakaUrl').css('display','none');
  $('#bakaUrlSwitch').html('není tu moje škola / zadat adresu přihlašovací stránky');
  $('#bakaUrlSwitch').attr('href','javascript:void(bakaUrlText())');
  }
function changeBakaUrl() {
  $('#bakaUrl').val($('#bakaUrlSelect').val());
  }
function zkusZnamky() {
  $('#welcomePreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
  if(!$('#bakaUrl').val()) {
    $('#bakaUrlSelect').css('border-color','red');
    $('#bakaUrl').css('border-color','red');
    var error=true;
    }
  if(!$('#bakaPrihlJmeno').val()) {
    $('#bakaPrihlJmeno').css('border-color','red');
    var error=true;
    }
  if(!$('#bakaHeslo').val()) {
    $('#bakaHeslo').css('border-color','red');
    var error=true;
    }
  if(error===true) {
    $('#welcomePreloader').css('background-image','').html('vyplň přihlašovací údaje');
    }
  if(error!==true) {
    $('#welcomeButton').attr('disabled',true);
    $('#welcomePreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")').html('');
    $.post('/php/zkusPrihlUdaje','bakaUrl='+$.trim($('#bakaUrl').val())+'&bakaPrihlJmeno='+$.trim($('#bakaPrihlJmeno').val())+'&bakaHeslo='+$.trim($('#bakaHeslo').val()),function(data) {
      r=JSON.parse(data);
      switch(r.s) {
        default: switch(r.e) {
          default: $('#welcomePreloader').css('background-image','').html('neznámá chyba, zkus to znovu'); break;
          case 1: $('#welcomePreloader').css('background-image','').html('server bakalářů nenalezen, překontroluj url adresu'); break;
          case 2: $('#welcomePreloader').css('background-image','').html('nepodařilo se přihlásit, překontroluj přihlašovací údaje'); break;
          } $('#welcomeButton').attr('disabled',false); break;
        case 1: $('#bakaUrl').val(r.bakaUrl);
                $('#bakaJmeno').val(r.jmeno);
                $('#bakaSkola').val(r.skola);
                $('#bakaCkfile').val(r.ckfile);
                $('#welcomeForm').attr('onsubmit','').submit(); break;
        }
    });
    }
  }


/* manipulace s přehledem */
function section2() {
  $('#section1').hide(100);
  $('#section2').show(100);
  }
function section1() {
  $('#section2').hide(100);
  $('#section1').show(100);
  }


/* chat */
var chating=false, chatLoaded=false;
function chatOdesli() {
  if(!$.trim($('#chatI').val())) chatAktualizuj();
  else {
    chating=true;
    $('#chatP').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
    $.post('/php/posliChatZpravu','zprava='+$.trim($('#chatI').val()),function(data) {
        r=JSON.parse(data);
        switch(r.s) {
          default: $('#chatP').css('background-image','url("http://vsechno-atd.cz/img/cross.png")'); chating=false; break;
          case 1: $('#chatI').val(''); chatAktualizuj();
          }
      });
    }
  }
function chatAktualizuj() {
  chating=true;
  $('#chatP').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $.get('/php/vypisChat',function(data) {
      r=JSON.parse(data);
      switch(r.s) {
        case 1: var chatZ='';
                r.zpravy.forEach(function(val) { chatZ+='<article><header '+val.nickT+'>'+val.nick+'</header> <p>'+val.text+'</p></article>'; });
                $('#chatZ').html(chatZ);
                chatLoaded=true;
        }
      $('#chatP').css('background-image','');
      chating=false;
    });
  }
