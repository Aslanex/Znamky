<?
  require 'php/funkce.php';
  $funkce=new funkce();
  $funkce->zacatek('faq','FAQ Známek');
  $funkce->zpetButton();
?>
  <div id="obsahStranky">
  <h2>Obecné dotazy</h2>
  <p><b>K čemu Známky slouží?</b><br>
  Je to speciální webová aplikace, která za pomocí tvých přihlašovacích údajů zjistí známky z <a href='http://bakalari.cz'>Bakalářů</a> a přehledně je zobrazí. Navíc si můžeš přihlašovací údaje uložit a zobrazování známek je pak znatelně rychlejší.</p>
  <p><b>Neohrozí to mé osobní údaje?</b><br>
  Pokud použiješ Anonymní prohlížení, náš server si žádné údaje neukládá. V opačném případě, kdy prohlížíš s uloženými přihlašovacími údaji, jsou tyto údaje i známky uloženy v naší databázi, odkud se nikam nedostanou. A ani v případě, že by se ti někdo dostal na účet, se daná osoba nedozví tvé přihlašovací údaje k Bakalářům.</p>
  <p><b>Zjistím u známek i váhu, datum a popis?</b><br>
  Určitě. Navíc jsou jednotlivé známky podle váhy různě veliké a v podrobném zobrazení jsou přehledně rozděleny do jednotlivých měsíců. Zkus si to a uvidíš :)</p>
  <p><b>Zjistím i průměr, čtvrtletní nebo pololetní známku předmětů?</b><br>
  Jasně. Vše je přehledně zobrazeno, průměr hnědě a pololetní známka zeleně, čtvrtletní se zobrazí po rozkliknutí nebo oranžově místo pololetní, pokud ještě pololetní není. No zkus si to :)</p>
  <p><b>Poznám nějak, které známky přibyly a které jsou staré?</b><br>
  Ano! Systém ti vždy napíše počet ještě nezobrazených známek a když je zobrazíš, budou mít červenou barvu. Je to propracovanější než na normálních Bakalářích.</p>
  <p><b>Zjistím taky rozvrh nebo výchovná opatření?</b><br>
  Na tom se zatím pracuje, aktuálně můžeš zobrazit výchovná opatření, když najedeš myší na název školy vpravo nahoře (je to experimentální funkce, nezaručujeme její správnou práci).</p>
  <p><b>Je to zadarmo?</b><br>
  Ano. Je možné, že se tu časem objeví nějaká nenápadná reklama, nebo zavedu nějaký prémiový účet.</p>
  <h2>Pokročilejší dotazy</h2>
  <p><b>Jak se můžu přihlásit nebo zaregistrovat?</b><br>
  Přihlášení probíhá přes účet Všechno, atd, což je můj portál, kde jsou Známky umístěny. Ale je to jednoduché, po zobrazení známek pomocí formuláře na <a href="/">hlavní straně</a> najeď dolů na odkaz <i>uložit známky</i> a systém už tě dovede.</p>
  <p><b>Kde najdu adresu přihlašovací stránky?</b><br>
  Najeď na stránku kde se přihlašuješ do Bakalářů a zkopíruj celý obsah adresního řádku (ten bílý řádek nahoře). Celá adresa by měla začínat <i>https://</i> nebo <i>http://</i> a končit <i>/login.aspx</i>. Pokud tomu tak není, zkus na konec adresy přidat <i>uvod.aspx</i> a stiskni enter. Pokud to ani tak nejde, můžeš <a href='mailto:aslanex@vsechno-atd.cz'>mě kontaktovat</a> :)</p>
  <p><b>Ve výpisu známek se mi zobrazují i předměty, které už na Bakalářích nejsou. Co s tím?</b><br>
  Systém zatím nedokáže odstranit prázdné předměty. Pokud se to stane, použij možnost <i>promazat data</i> po kliknutí na klíč na <a href="/">hlavní straně</a>.</p>
  <p><b>Jaktože průměr neodpovídá známkám?</b><br>
  Průměr zobrazený ve výpisu známek je vážený průměr zobrazený na Bakalářích. To znamená, že při výpočtu jsou zohledněny váhy známek. Také se může stát, že Bakaláři vypočtou průměr špatně, ale i v tomto případě je tu kvůli případným neshodám s učitelem ponechán.</p>
  <p><b>To podrobné zobrazení předmětu zabírá moc místa. Není jiná možnost?</b><br>
  Dřívější horizontální úsporné zobrazení je od verze 5.0 vypuštěno. Časem připravíme mobilní verzi Známek.</p>
  <p><b>Nové známky se mi tady ještě nezobrazily, přestože na Bakalářích už jsou. Kde je problém?</b><br>
  Náš server kontroluje známky jednou za půl hodiny a většinou nestihne projet všechny bakaláře, takže bude třeba chvíli počkat. Čas poslední kontroly můžeš najít po kliknutí na klíč na <a href="/">hlavní straně</a>, průměrná frekvence kontroly je asi 40 minut.</p>
  <p><b>Můžu účet odstranit, když ho už nepotřebuju?</b><br>
  Ano, po kliknutí na klíč na <a href="/">hlavní straně</a> je odkaz <i>odstranit zdroj</i>. Tím se odstraní úplně vše spojené s daným účtem na Bakalářích, tady přihlašovací údaje, URL adresa a všechny známky. Ostatní zdroje a tvůj účet Všechno, atd zůstanou beze změny. Všechno, atd účet zatím není možné odstranit.</p>
  <p><b>Na všech stránkách chybí odkaz na hlavní stránku. To musím vždy zadávat adresu ručně?</b><br>
  Nemusíš :) Na každé stránce vpravo nad logem je šedá šipka, která tě dovede na hlavní stránku. Jednoduché, že?</p>
  <p><b>Našel jsem chybu nebo mám nápad, jak Známky vylepšit. Můžu o tom dát vědět?</b><br>
  Rozhodně, pozitivní i negativní odezvu s radostí přijmu. Neboj se mi napsat na email <a href='mailto:aslanex@vsechno-atd.cz'>aslanex@vsechno-atd.cz</a>. Do předmětu uveď něco jako Připomínka ke Známkám, Chyba ve Známkách nebo podobně.</p>
  <p><b>Můžu nějak pomoci s vývojem?</b><br>
  Můžeš to zkusit, ale je to fakt těžký. Jestli máš zájem, <a href='mailto:aslanex@vsechno-atd.cz'>neboj se mě kontaktovat</a>.</p>
  <p><b>Kdo jsi?</b><br>
  Aslanex, mladý kluk a vývojář portálu Všechno, atd a všeho na něm. Taky dělám YouTube reportáže z veřejné dopravy. Víc informací snad najdeš na <a href='http://vsechno-atd.cz'>hlavní stránce Všeho, atd</a> a nebo někde jinde v internetu.</p>
<?
  $funkce->htmlKonec();
?>
