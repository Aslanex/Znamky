<?
  require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $bakaUcetID=(int) $_GET['bakaUcet'];
  if(!$_SESSION['ucetID'] || !$bakaUcetID) {
    header('HTTP/1.0 307 Retarded');
    header('Location: /');
    exit();
  }
  $funkce->zacatek('zobraz','Známky');
  if($funkce->mysqli->query('SELECT COUNT(*) FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'" AND ucetID='.$_SESSION['ucetID'])->fetch_assoc()['COUNT(*)']!=1) exit('Nejsi majitel tohoto účtu.');


  $ucet=$funkce->mysqli->query('SELECT jmeno,skola,zobrazeni FROM bakaUcty WHERE bakaUcetID="'.$bakaUcetID.'"')->fetch_assoc();
  $funkce->mysqli->query('UPDATE bakaUcty SET nove=0 WHERE bakaUcetID="'.$bakaUcetID.'"');
  echo '<div id="hlavicka" onmouseover="showZobrazMenu()" onmouseout="hideZobrazMenu()"><div id="zpet" onclick="if(event.which==1) location.href=\'/\'"></div><div id="skola">'.$ucet['skola'].'</div><div id="jmeno">'.$ucet['jmeno'].'</div><div id="zobrazMenu">skoč na<br><a href="javascript:void(0)" onclick="karta(1)" id="karta1link" style="color:#666">známky</a> - <a href="javascript:void(0)" onclick="karta(2)" id="karta2link">výchovná opatření</a> - <a href="spravuj?bakaUcet='.$bakaUcetID.'">správa</a></div></div><div id="obsahStranky"><div id="karta1" class="znamky karta">';

  $pololeti=(date('n')<1 || date('n')>7) ? 1 : 2;
  $zacatekPololeti=(date('n')==1) ? mktime(0,0,0,9,1,date('Y')-1) : ($pololeti==1) ? mktime(0,0,0,9,1) : mktime(0,0,0,2,1);
  $timelineSize=($pololeti==2) ? (date('n')-1)*150 : (date('n')==1) ? 750 : (date('n')-8)*150;

  $predmety=$funkce->mysqli->query('SELECT predmetID,nazev,prumer,ctvrtleti,pololeti FROM bakaPredmety WHERE bakaUcetID="'.$bakaUcetID.'" ORDER BY nazev');
  if($predmety->num_rows==0) echo 'Vypadá to, že nemáš žádné známky.<br>Uvědom si prosím, že aktualizace známek může trvat až 30 minut.';
  else {
    while($predmet=$predmety->fetch_assoc()) { // předměty

      $vysledna=($predmet['pololeti']) ? $predmet['pololeti'] : ( ($predmet['ctvrtleti']) ? $predmet['ctvrtleti'] : '&nbsp;' ) ;
      $vyslednaClass=($predmet['pololeti']) ? 'pololetniZnamka' : ( ($predmet['ctvrtleti']) ? 'ctvrtletniZnamka' : '' ) ;

      $znamky=$funkce->mysqli->query('SELECT znamka,vaha,datum,popis,nove FROM bakaZnamky WHERE predmetID="'.$predmet['predmetID'].'" ORDER BY datum');
      $funkce->mysqli->query('UPDATE bakaZnamky SET nove=NULL WHERE predmetID="'.$predmet['predmetID'].'"');
      /*if($ucet['zobrazeni']==1) {    // HORIZONTÁLNÍ ZOBRAZENÍ ZNÁMEK - ZATÍM VYPUŠTĚNO
        $poradiMinus=0;
        $lastXpozice=0; $Ypozice=7; $radek=1; $maxRadek=2;
        $znamkyA=null; $popisy=null; $vahy=null; $fdatumy=null; $delky=null; $Xpozice=null; $radky=null;
        for($c=0;$znamka=$znamky->fetch_assoc();$c++) {
          $fontSize=(!$znamka['vaha']) ? 6 : $znamka['vaha'];
          $nova=($znamka['nove']==1) ? ';color:firebrick' : '';
          echo '<article style="font-size:'.(20+$fontSize*2).'px'.$nova.'">'.$znamka['znamka'].'</article>';

          $poradi=$c-$poradiMinus;
          if($lastPopis==$znamka['popis'] && $lastDatum==$znamka['datum'] && $lastVaha==$znamka['vaha']) {
            $poradi--; $poradiMinus++;
            $znamkyA[$poradi].=', '.$znamka['znamka'];
            $delky[$poradi]+=10;
            }
          else {
            $znamkyA[$poradi]=$znamka['znamka'];
            $popisy[$poradi]=$znamka['popis'];
            $vahy[$poradi]=$znamka['vaha'];
            $fdatumy[$poradi]=date('j.n.y',$znamka['datum']);
            $nove[$poradi]=$znamka['nove'];
            $delky[$poradi]=(strlen($popisy[$poradi])*8)+90;
            $lastPopis=$znamka['popis']; $lastDatum=$znamka['datum']; $lastVaha=$znamka['vaha'];
            $Xpozice[$poradi]=($znamka['datum']-$zacatekPololeti)/17280;
            $radky[$poradi]=radekZnamky($delky,$radky,$Xpozice,$Xpozice[$poradi]);
            if($radky[$poradi]>$maxRadek) {
              $maxRadek=$radky[$poradi];
              if((strlen($predmet['nazev'])*12)-130>$Xpozice[$poradi]) $maxRadek++;
              }
            $lastXpozice=$Xpozice[$poradi];
            }
          }

        $ctvrtletniPodrobnost=($predmet['pololeti'] && $predmet['ctvrtleti']) ? '<div class="ctvrtletniPodrobnost" style="bottom:'.(($maxRadek*16)-18).'px">čtvrtletí: '.$predmet['ctvrtleti'].'</div>' : '';
        echo '</div><div class="podrobnostiPredmetu1" id="podrobnostiPredmetu'.$predmet['predmetID'].'" style="margin-top: '.(($maxRadek*16)+8).'px; display: none; opacity: 0;">'.$ctvrtletniPodrobnost.'<img src="/img/blank" class="timeline" style="background-image: url(\'/img/cara'.$pololeti.'\');width: '.$timelineSize.'px">';

        foreach($znamkyA as $c=>$a) {
          $nova=($nove[$c]==1) ? ' style="color:firebrick"' : '';
          $vaha=($vahy[$c]) ? ', váha '.$vahy[$c] : null;
          echo '<article style="margin-left:'.$Xpozice[$c].'px;top:-'.(($radky[$c]*16)-9).'px"><b'.$nova.'>'.$a.'</b> '.$popisy[$c].' <span>'.$fdatumy[$c].$vaha.'</span></article>';
          }
        echo '</div><script>predmetHeight['.$predmet['predmetID'].']='.(($maxRadek*16)+38).'</script></section>';
        }*/
      // vertikální zobrazení známek (aktuálně jediné)
      $znamkyA=array();
      $poradiMinus=0;
      $vypisHtml='';
      $znamkyTotal=0; $vahyTotal=0; $znameVahu=false; $znameZnamky=true;
      for($c=0;$znamka=$znamky->fetch_assoc();$c++) {
        $fontSize=(!$znamka['vaha']) ? 6 : $znamka['vaha'];
        $nova=($znamka['nove']==1) ? ';color:firebrick' : '';
        $vypisHtml.='<article style="font-size:'.(20+$fontSize*2).'px'.$nova.'">'.$znamka['znamka'].'</article>';

        switch($znamka['znamka']) {
          case '1-': $hodnotaZnamky = 1.5; break;
          case '2-': $hodnotaZnamky = 2.5; break;
          case '3-': $hodnotaZnamky = 3.5; break;
          case '4-': $hodnotaZnamky = 4.5; break;
          case '1': case '2': case '3': case '4': case '5':
            $hodnotaZnamky = (int) $znamka['znamka']; break;
          default: $hodnotaZnamky = false;
        }
        if($hodnotaZnamky!=false) {
          $znamkyTotal += ($znamka['vaha']) ? $hodnotaZnamky*$znamka['vaha'] : $hodnotaZnamky*10;
          if($znamka['vaha']) {
            $vahyTotal += $znamka['vaha'];
            $znameVahu = true;
          }
          else
            $vahyTotal += 10;
        }
        else
          $znameZnamky = false;

        $poradi=$c-$poradiMinus;
        if($lastPopis==$znamka['popis'] && $lastDatum==$znamka['datum'] && $lastVaha==$znamka['vaha']) {
          $poradi--; $poradiMinus++;
          $znamkyA[$poradi]->znamka.=', '.$znamka['znamka'];
        }
        else {
          $znamkyA[$poradi]=new StdClass;
          $znamkyA[$poradi]->znamka=$znamka['znamka'];
          $znamkyA[$poradi]->popis=$znamka['popis'];
          $znamkyA[$poradi]->vaha=$znamka['vaha'];
          $znamkyA[$poradi]->rok=($znamka['datum']) ? date('Y',$znamka['datum']) : -1;
          $znamkyA[$poradi]->mesic=($znamka['datum']) ? date('n',$znamka['datum']) : -1;
          $znamkyA[$poradi]->fdatum=($znamka['datum']) ? date('j.n.y',$znamka['datum']) : 'bez data';
          $znamkyA[$poradi]->nove=$znamka['nove'];
          $lastPopis=$znamkyA[$poradi]->popis; $lastVaha=$znamkyA[$poradi]->vaha; $lastDatum=$znamka['datum'];
        }
      }

      $ctvrtletniPodrobnost=($predmet['pololeti'] && $predmet['ctvrtleti']) ? '<div class="ctvrtletniPodrobnost">čtvrtletí: '.$predmet['ctvrtleti'].'</div>' : '';
      $podrobnostiHtml = '</div><div class="podrobnostiPredmetu2" id="podrobnostiPredmetu'.$predmet['predmetID'].'">'.$ctvrtletniPodrobnost.'<div class="podrobnostiPredmetu2Znamky" onclick="event.stopPropagation()">';

      if(count($znamkyA)===0) {
        $detailHtml = '<article style="color:#444;opacity:0.5">žádné známky</article>';
        $articleHeight = 61;
      }
      else {
        $mesic=-2; $mesicC=0; $rok=$znamkyA[0]->rok; $detailHtml='';
        foreach($znamkyA as $c=>$a) {
          if($a->rok>$rok) {
            $mesic=-1;
            $rok=$a->rok;
          }
          if($a->mesic>$mesic) {
            $detailHtml.='<div class="znamkyMesic">'.$funkce->month[($a->mesic-1)].'</div>';
            $mesic=$a->mesic; $mesicC++;
            $margin=' style="margin-top:4px"';
          }
          else
            $margin='';
          $nova=($a->nove==1) ? ' style="color:firebrick"' : '';
          $vaha=($a->vaha) ? ', v'.$a->vaha : '';
          $detailHtml.='<article'.$margin.'><b'.$nova.'>'.$a->znamka.'</b> '.$a->popis.' <span>'.$a->fdatum.$vaha.'</span></article>';
        }
        $detailHtml.='</div><div class="pridejZnamku"><div class="znamkyMesic" id="pridejZnamkuMesic'.$predmet['predmetID'].'" style="display:none;opacity:0" onclick="event.stopPropagation();document.querySelector(\'#pridejZnamkuInput'.$predmet['predmetID'].'\').select()">když dostanu</div><article style="margin-top:4px"><a href="javascript:void(1);" onclick="event.stopPropagation();pridejZnamku('.$predmet['predmetID'].')" class="pridejZnamkuA" id="pridejZnamkuA'.$predmet['predmetID'].'"></a><span style="display:none;" id="pridejZnamkuCont'.$predmet['predmetID'].'" onclick="event.stopPropagation();document.querySelector(\'#pridejZnamkuInput'.$predmet['predmetID'].'\').select()"><input type="tel" onchange="vypocitejNovouZnamku('.$predmet['predmetID'].')" onkeyup="vypocitejNovouZnamku('.$predmet['predmetID'].')" onclick="event.stopPropagation();this.select();" id="pridejZnamkuInput'.$predmet['predmetID'].'" class="pridejZnamkuInput" value="5" maxlength="2"> váhy <input type="number" min="1" max="20" onchange="vypocitejNovouZnamku('.$predmet['predmetID'].')" onkeyup="vypocitejNovouZnamku('.$predmet['predmetID'].')" onclick="event.stopPropagation();this.select();" id="pridejZnamkuVaha'.$predmet['predmetID'].'" class="pridejZnamkuInput" value="10">, průměr bude <span class="vypocitanyPrumer" id="pridejZnamkuVysledek'.$predmet['predmetID'].'"></span> </span></div></article>';
        $articleHeight = (count($znamkyA)*17+$mesicC*4+65);
      }
      $dataVahy = ($znameVahu==false) ? 0 : 1;
      $dataZnamky = ($znameZnamky==false) ? 0 : 1;

      echo '<section onclick="podrobnostiPredmetu('.$predmet['predmetID'].')" id="sectionPredmetu'.$predmet['predmetID'].'" data-grades-total='.$znamkyTotal.' data-grades-ammount='.$vahyTotal.' data-grades-isznamky='.$dataZnamky.' data-grades-isvahy='.$dataVahy.' data-article-height='.$articleHeight.'><div class="nazevPredmetu">'.$predmet['nazev'].'</div><div class="vyslednaPredmetu '.$vyslednaClass.'">'.$vysledna.'</div><div class="prumerPredmetu">'.$predmet['prumer'].'</div><div class="znamkyPredmetu" id="znamkyPredmetu'.$predmet['predmetID'].'">'.$vypisHtml.$podrobnostiHtml.$detailHtml.'</div></section>';
    }

  }

    echo '</div><div id="karta2" class="vychOpatreni karta">';

    $vychOpatreni=$funkce->mysqli->query('SELECT opatreniID,druh,datum,text FROM bakaVychOpatreni WHERE bakaUcetID="'.$bakaUcetID.'" ORDER BY datum');  // výchovná opaření
    if($vychOpatreni->num_rows==0) echo 'Vypadá to, že nemáš žádná výchovná opatření.<br>Uvědom si prosím, že aktualizace může trvat až 30 minut.';
    else {
      while($opatreni=$vychOpatreni->fetch_assoc()) {

        $datum=date('j. ',$opatreni['datum']).$funkce->month[(int) date('n',$opatreni['datum'])];
        echo '<section><div class="druh">'.$opatreni['druh'].'</div><div class="datum">'.$datum.'</div><div class="text">'.$opatreni['text'].'</div></section>';

      }
    }
    echo '</div>';

  $funkce->htmlKonec();

  function radekZnamky($delky,$radky,$Xpozice,$Xpoz,$radek=1) {
    foreach($delky as $c=>$a) {
      if($radky[$c]==$radek && $a+$Xpozice[$c]>$Xpoz) {
        $radek++;
        $radek=radekZnamky($delky,$radky,$Xpozice,$Xpoz,$radek);
      }
    }
    return $radek;
  }
?>
