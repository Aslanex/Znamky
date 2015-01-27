<?php /* BAKA
       * znamky.vsechno-atd.cz/php/core/baka.php
       * základní třída s baka enginem, přistupná jen pro skript */

class BAKA {
  
  public $MAIN;
  function __construct() {
    require_once 'main.php'; $this->MAIN=new MAIN();
    }

  
  public $ch;
  function getCurl() {                           
    /* začátek curl, nebo vrací existující spojení
     * RETURN ch */
    if(isset($this->ch) && curl_setopt($this->ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0')) return $this->ch;
    else {
      $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','ZWCC');
      $json['ckfile']=call_user_func('end',explode('/',$ckfile));
      $ch=curl_init($_POST['bakawebUrl'].'login.aspx');
      curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
      curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
      libxml_use_internal_errors(true);
      $this->ch=$ch;
      return $ch;
      }
    }




  function tryUrl($bakaUrl) {
    /* zkouška bakaUrl
     * INPUT zadaná url
     * RETURN [bakaUrl,skola,prihlInput] nebo false když nenajde  */

    $ch=$this->getCurl();
    $body=new DOMDocument();

    curl_setopt($ch,CURLOPT_URL,$bakaUrl.'login.aspx');             /// s lomítkem
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue); // název školy
    $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0]; // tabulka s login políčky
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name'); // jméno přihlašovacího políčka (bezpečnostní prvek, použije se v POST při loginu)
    if($skola) return array($bakaUrl,$skola,$prihlInput);
    
    if(strpos($bakaUrl,'/login.aspx')) {                            /// s login.aspx
      curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx',$bakaUrl));
      curl_exec($ch);
      if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
        curl_setopt($ch,CURLOPT_URL,$bakaUrl);
        $html=curl_exec($ch);
        @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
        $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
        $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
        if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
        if($skola) return array(str_replace('login.aspx','',$bakaUrl),$skola,$prihlInput);
        }
      }
    
    curl_setopt($ch,CURLOPT_URL,$bakaUrl.'/login.aspx');            /// bez lomítka
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($skola) return array($bakaUrl.'/',$skola,$prihlInput);
    
    curl_setopt($ch,CURLOPT_URL,'http://'.$bakaUrl.'login.aspx');   /// bez http s lomítkem
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($skola) return array('http://'.$bakaUrl,$skola,$prihlInput);
    
    curl_setopt($ch,CURLOPT_URL,'http://'.$bakaUrl.'/login.aspx');  /// bez http bez lomítka
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($skola) return array('http://'.$bakaUrl.'/',$skola,$prihlInput);
  
    curl_setopt($ch,CURLOPT_URL,'https://'.$bakaUrl.'login.aspx');  /// bez https s lomítkem
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($skola) return array('https://'.$bakaUrl,$skola,$prihlInput);
  
    curl_setopt($ch,CURLOPT_URL,'https://'.$bakaUrl.'/login.aspx'); /// bez https bez lomítka
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($skola) return array('https://'.$bakaUrl.'/',$skola,$prihlInput);
    
    if(strpos($bakaUrl,'/login.aspx')) {                            /// bez http s login.aspx
      curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx','http://'.$bakaUrl));
      curl_exec($ch);
      if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
        curl_setopt($ch,CURLOPT_URL,'http://'.$bakaUrl);
        $html=curl_exec($ch);
        @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
        $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
        $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
        if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
        if($skola) return array(str_replace('login.aspx','','http://'.$bakaUrl),$skola,$prihlInput);
        }
      }
    
    if(strpos($bakaUrl,'/login.aspx')) {                            /// bez https s login.aspx
      curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx','https://'.$bakaUrl));
      curl_exec($ch);
      if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
        curl_setopt($ch,CURLOPT_URL,'https://'.$bakaUrl);
        $html=curl_exec($ch);
        @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
        $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
        $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
        if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
        if($skola) return array(str_replace('login.aspx','','https://'.$bakaUrl),$skola,$prihlInput);
        }
      }
    
    if(strpos($bakaUrl,'http://')) {                                /// http místo https s lomítkem
      curl_setopt($ch,CURLOPT_URL,str_replace('http://','https://',$_POST['bakawebUrl'].'login.aspx'));
      $html=curl_exec($ch);
      @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
      $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
      $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
      if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
      if($skola) return array(str_replace('http://','https://',$_POST['bakawebUrl']),$skola,$prihlInput);
      curl_setopt($ch,CURLOPT_URL,str_replace('http://','https://',$_POST['bakawebUrl'].'/login.aspx')); /// http místo https bez lomítka
      $html=curl_exec($ch);
      @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
      $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
      $table=$this->MAIN->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
      if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
      if($skola) return array(str_replace('http://','https://',$_POST['bakawebUrl']).'/',$skola,$prihlInput);
      }
    
    return false; // vracím false, pokud se nenašlo
    }
  
  
  
  
  function login($bakaUrl,$bakaJmeno,$bakaHeslo,$prihlInput) {                        
    /* přihlášení uživatele do bakalářů
     * INPUT bakaUrl, uživatelské jméno, heslo, kód přihlašovacího inputu
     * RETURN [skola,jmeno] nebo false když nenajde  */
    
    $ch=$this->getCurl();
    $body=new DOMDocument();
    
    curl_setopt($ch,CURLOPT_REFERER,$bakaUrl.'login.aspx'); /// přihlášení
    curl_setopt($ch,CURLOPT_POST,true);
    $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&'.urlencode($prihlInput).'='.$bakaJmeno.'&ctl00%24cphmain%24TextBoxHeslo='.$bakaHeslo.'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
    curl_exec($ch);
    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=302) return false; // vracím false, když nepřihlášen
  
    curl_setopt($ch,CURLOPT_POST,false);                    /// načtení úvodu a zjištění školy,jména
    curl_setopt($ch,CURLOPT_POSTFIELDS,null);
    curl_setopt($ch,CURLOPT_REFERER,$bakaUrl.'login.aspx');
    curl_setopt($ch,CURLOPT_URL,$bakaUrl.'uvod.aspx');
    $html=curl_exec($ch);
    $body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $skola=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $jmeno=trim($this->MAIN->getElementsByClassName($body->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);
    return array($skola,$jmeno);
    
    }
  
  
  
  }