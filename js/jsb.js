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
function vypocitejNovouZnamku(c) {
  var hodnota;
  switch (document.querySelector('#pridejZnamkuInput'+c).value) {
    case '1-': hodnota = 1.5; break;
    case '2-': hodnota = 2.5; break;
    case '3-': hodnota = 3.5; break;
    case '4-': hodnota = 4.5; break;
    case '1': hodnota = 1; break;
    case '2': hodnota = 2; break;
    case '3': hodnota = 3; break;
    case '4': hodnota = 4; break;
    case '5': hodnota = 5; break;
    default: hodnota = false;
  }
  var vaha = parseInt( document.querySelector('#pridejZnamkuVaha'+c).value );
  if (hodnota==false || vaha=<0) document.querySelector('#pridejZnamkuVysledek'+c).innerHTML = '';
  else {
    var section = document.querySelector('#sectionPredmetu'+c);
    var hodnotaTotal = parseInt( section.dataset.gradesTotal ) + hodnota*vaha;
    var vahaTotal = parseInt( section.dataset.gradesAmmount ) + vaha;
    var prumer = ( Math.round (hodnotaTotal/vahaTotal*100) ) / 100;
    document.querySelector('#pridejZnamkuVysledek'+c).innerHTML = prumer;
  }
}
