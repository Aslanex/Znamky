<?
  require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->zacatek('index','Podmínky užití Známek');
  $funkce->mysqli->select_db('vsechno-atdcz_znamky');
  $funkce->zpetButton();
?>
  <b>Podmínky užití webové aplikace Známky</b><br>
  Aplikace může ukládát nešifrovaná data zjistitelná pomocí přihlašovacích údajů, které jsou zadány a jsou tu též uloženy. Tyto informace nebudou sdělovány třetím osobám, ale mohou být anonymně použity pro zajímavé statistiky. Po odstranění účtu jsou dotyčná data nenávratně odstraněna.<br>
  Aplikace neručí za údaje, které zde zobrazuje, protože jsou pouze přebírány z jiných serverů.<br>
  Ačkoli se snažíme jak můžeme, nezaručujeme funkčnost aplikace a vyhrazujeme si některé funkce kdykoli zrušit.<br>
  Aplikace může používat soubory cookies a další webové technologie, které používaný prohlížeč podporuje.<br>
  Tyto podmínky můžeme kdykoli změnit, při větším rozsahu změn bude informace o tomto uvedena alespoň několik dní na domovské stánce aplikace.
<?
  $funkce->htmlKonec();
?>
