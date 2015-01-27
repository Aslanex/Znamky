var apiKey='zna_b0e0be9aa01035115a94783fec866f256c4c22a';

function odhlaseni() {
  $.get('/php/odhlaseni',function(data) {
    if(data==='completed') location.href='/';
  });
}

function login() {                    // login
  if(!$('#accNick').val()) {
    $('#accNick').css('border-color','red');
    var error=true;
  }
  if(!$('#accPassword').val()) {
    $('#accPassword').css('border-color','red');
    var error=true;
  }
  if(error===true) {
    $('#loginPreloader').css('background-image','').html('vyplň přihlašovací údaje');
  }
  else {
    $('#loginButton').attr('disabled',true);
    $('#loginPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")').html('');
    $.post('/php/api?do=accLogin&apiKey='+apiKey,{nick: $('#accNick').val(),password: $('#accPassword').val(),permLogin: $('#accPerm').val()},function(data) {
      r=JSON.parse(data);
      switch(r.s) {
        default:$('#loginPreloader').css('background-image','').html('neznámá chyba, zkus to znovu'); break;
        case 2: $('#loginPreloader').css('background-image','').html('přihlašovací údaje nesouhlasí'); break;
        case 1: $('#loginPreloader').css('background-image','').html('přihlášen - počkej prosím'); location.reload();
      }
      $('#loginButton').attr('disabled',false);
    });
  }
}


var zobrazMenuOpened=false;
function showZobrazMenu() {                 // přeskakování zobrazení známek
  $('#zobrazMenu').show(100);
  $('#skola, #jmeno').hide(100);
  if(zobrazMenuOpened) $('#zobrazMenu, #skola, #jmeno').stop(true,true);
  else zobrazMenuOpened=true;
}
function hideZobrazMenu() {
  $('#zobrazMenu').delay(300).hide(100,function() { zobrazMenuOpened=false } );
  $('#skola, #jmeno').delay(300).show(100);
}


var kartaN=1;
function karta(n) {
  $('.karta').hide(100);
  $('#karta'+n).show(100);
  $('#zobrazMenu a').css('color','rgb(0,0,238)');
  $('#karta'+n+'link').css('color','#666');
}


var podrobnostiOtevreno=0, otevrenoC=-1, predmetHeight=new Array();
function podrobnostiPredmetu(c) {      // otevření/zavření podrobností předmětu
  if(podrobnostiOtevreno==0) {
    podrobnostiOtevreno=1; otevrenoC=c;
    $('#znamkyPredmetu'+c).css('opacity','0');
    setTimeout(function() {
      $('#znamkyPredmetu'+c).css('display','none');
      $('#sectionPredmetu'+c).css('height',document.querySelector('#sectionPredmetu'+c).dataset.articleHeight);
      $('#podrobnostiPredmetu'+c).css('display','block');
    },200);
    setTimeout(function() {
      $('#podrobnostiPredmetu'+c).css('opacity','1');
    },201);
  }
  else {
    if(c!=otevrenoC) {
      podrobnostiPredmetu(otevrenoC);
      podrobnostiPredmetu(c);
    }
    else {
      podrobnostiOtevreno=0; otevrenoC=-1;
      $('#podrobnostiPredmetu'+c).css('opacity','0');
      setTimeout(function() {
        $('#podrobnostiPredmetu'+c).css('display','none');
        $('#sectionPredmetu'+c).css('height',41);
        $('#znamkyPredmetu'+c).css('display','block');
      },200);
      setTimeout(function() {
        $('#znamkyPredmetu'+c).css('opacity','1');
      },201);
    }
  }
}


function pridejZnamku(c) {             // když dostanu
  $('#pridejZnamkuMesic'+c).css('display','block');
  $('#pridejZnamkuA'+c).css('display','none');
  $('#pridejZnamkuCont'+c).css('display','block');
  setTimeout(function() {
    $('#pridejZnamkuMesic'+c).css('opacity','1');
    $('#pridejZnamkuCont'+c).css('opacity','1');
  },1);
  vypocitejNovouZnamku(c);
}
var znamkyHodnoty = { '5': 5, '4-': 4.5, '4': 4, '3-': 3.5, '3': 3, '2-': 2.5, '2': 2, '1-': 1.5, '1': 1 };
function vypocitejNovouZnamku(c) {
  var hodnota = document.querySelector('#pridejZnamkuInput'+c).value;
  var parsedHodnota = parseInt(hodnota);
  hodnota = hodnota in znamkyHodnoty ? znamkyHodnoty[hodnota] : parsedHodnota in znamkyHodnoty ? znamkyHodnoty[parsedHodnota] : false ;
  var vaha = parseInt( document.querySelector('#pridejZnamkuVaha'+c).value );
  if (hodnota==false || vaha<=0 || isNaN(vaha)) document.querySelector('#pridejZnamkuVysledek'+c).innerHTML = '';
  else {
    var section = document.querySelector('#sectionPredmetu'+c);
    var hodnotaTotal = parseFloat( section.dataset.gradesTotal ) + hodnota*vaha;
console.log(hodnotaTotal);
    var vahaTotal = parseInt( section.dataset.gradesAmmount ) + vaha;
    var prumer = ( Math.round (hodnotaTotal/vahaTotal*100) ) / 100;
    document.querySelector('#pridejZnamkuVysledek'+c).innerHTML = prumer;
  }
}

var aktualizing=false;
function aktualizujZdroj(bakaUcetID) {      // manuální aktualizace zdroje
  if(aktualizing===false) {
    $.get('/php/aktualizujZdroj?bakaUcetID='+bakaUcetID,function(data) {
      try { r=JSON.parse(data); } catch(e) { aktualizaceNezdarila(bakaUcetID); }
      if(r.state=='success') {
        var nove = r.nove==0 ? 'žádné nové známky' : r.nove==1 ? '<span style="color:firebrick">nová známka</span>' : r.nove<5 ? '<span style="color:firebrick">'+r.nove+' nové známky</span>' : '<span style="color:firebrick">'+r.nove+' nových známek!</span>';
        $('#zdroj'+bakaUcetID+'Data').html(nove);
        $('#aktualizujZdroj'+bakaUcetID).removeAttr('pozor');
        $('#aktualizujZdroj'+bakaUcetID+'Span').removeAttr('pozor');
        $('#aktualizujZdroj'+bakaUcetID+'Span').html('aktuální');
        aktualizing=false;
      }
      else aktualizaceNezdarila(bakaUcetID);
    }).error(function () { aktualizaceNezdarila(bakaUcetID); });
    $('#aktualizujZdroj'+bakaUcetID+'Span').html('aktualizuji...');
    aktualizing=true;
  }
}
function aktualizaceNezdarila(bakaUcetID) {
  $('#aktualizujZdroj'+bakaUcetID+'Span').html('aktualizace se nezdařila');
  $('#aktualizujZdroj'+bakaUcetID+'Span').attr('pozor',true);
  $('#aktualizujZdroj'+bakaUcetID).attr('pozor',true);
  aktualizing=false;
}
