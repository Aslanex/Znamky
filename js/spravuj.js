function ulozZobrazeni(bakaUcetID) {
  $('#zobrazeniPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $.post('/php/zmenZobrazeniUctu','bakaUcetID='+bakaUcetID+'&zobrazeni='+$('#zobrazeniSelect').val(),function(data) {
    var r=JSON.parse(data);
    if(r.state=='success') $('#zobrazeniPreloader').css('background-image','none');
    else $('#zobrazeniPreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
    });
  }

function pripravOdstraneni(bakaUcetID) {
  $('#obsahStranky').html('Opravdu chceš odstranit tento zdroj?<br>Tímto budou smazány přihlašovací údaje a všechny předměty, známky a výchovná opatření tohoto zdroje, ostatní zůstane zachováno.<br><form onsubmit="odstran('+bakaUcetID+');return false"><button type="submit" id="odstraneniButton">Odstranit!</button> <div class="formPreloader" id="odstraneniPreloader"></div></form>');
  }
function odstran(bakaUcetID) {
  $('#odstraneniButton').attr('disabled',true);
  $('#odstraneniButton').css('color','#777');
  $('#odstraneniPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
  $.post('/php/odstranUcet','bakaUcetID='+bakaUcetID,function(data) {
    var r=JSON.parse(data);
    if(r.state=='success') $('#obsahStranky').html('Hotovo.<br><a href="/">domů</a>');
    else {
      $('#odstraneniButton').attr('disabled',false);
      $('#odstraneniButton').css('color','darkgoldenrod');
      $('#odstraneniPreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
      }
    });
  }

var promazavam=false;
function promaz(bakaUcetID) {
  if(promazavam===false) {
    promazavam=true;
    $('#promazPreloader').css('background-image','url("http://vsechno-atd.cz/img/preloader.gif")');
    $.post('/php/promazUcet','bakaUcetID='+bakaUcetID,function(data) {
      var r=JSON.parse(data);
      if(r.state=='success') $('#obsahStranky').html('Hotovo. Známky se znovu objeví do 30 minut.<br><a href="/">domů</a>');
      else {
        $('#odstraneniPreloader').css('background-image','url("http://vsechno-atd.cz/img/cross.png")');
        }
      });
    }
  }
