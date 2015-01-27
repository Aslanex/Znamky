<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  /*  s 0 error
        1 hotovo
      e 0 retarded
        1 url nenalezena
        2 nepřihlášen  */
  if(!$_POST['bakaUrl'] || !$_POST['bakaPrihlJmeno'] || !$_POST['bakaHeslo']) exit(json_encode(array('s'=>0,'e'=>0)));
  
  $_POST['bakawebUrl']=trim($_POST['bakawebUrl']);
  $ckfile=tempnam('/home/www/vsechno-atd.cz/tmp','ZWCC');
  $json['ckfile']=call_user_func('end',explode('/',$ckfile));
  $ch=curl_init($_POST['bakawebUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_COOKIEJAR,$ckfile); curl_setopt($ch,CURLOPT_COOKIEFILE,$ckfile);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0');
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  $body=new DOMDocument();
  libxml_use_internal_errors(true);

  /* bakaUrl */

  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].'login.aspx'); // s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
  if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  if($json['skola']) {
    $json['bakaUrl']=$_POST['bakaUrl'];
    goto urlGet;
    }
  
  if(strpos($_POST['bakaUrl'],'/login.aspx')) {
    curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx',$_POST['bakaUrl']));
    curl_exec($ch);
    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
      curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl']);  // s loginem
      $html=curl_exec($ch);
      @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
      $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
      $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
      if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
      }
    if($json['skola']) {
      $json['bakaUrl']=str_replace('login.aspx','',$_POST['bakaUrl']);
      goto urlGet;
      }
    }
  
  curl_setopt($ch,CURLOPT_URL,$_POST['bakaUrl'].'/login.aspx'); // bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
  if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  if($json['skola']) {
    $json['bakaUrl']=$_POST['bakaUrl'].'/';
    goto urlGet;
    }
  
  curl_setopt($ch,CURLOPT_URL,'http://'.$_POST['bakaUrl'].'login.aspx'); // bez http s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
  if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  if($json['skola']) {
    $json['bakaUrl']='http://'.$_POST['bakaUrl'];
    goto urlGet;
    }
  
  curl_setopt($ch,CURLOPT_URL,'http://'.$_POST['bakaUrl'].'/login.aspx'); // bez http bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
  if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  if($json['skola']) {
    $json['bakaUrl']='http://'.$_POST['bakaUrl'].'/';
    goto urlGet;
    }

  curl_setopt($ch,CURLOPT_URL,'https://'.$_POST['bakaUrl'].'login.aspx'); // bez https s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
  if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  if($json['skola']) {
    $json['bakaUrl']='https://'.$_POST['bakaUrl'];
    goto urlGet;
    }

  curl_setopt($ch,CURLOPT_URL,'https://'.$_POST['bakaUrl'].'/login.aspx'); // bez https bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
  if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
  if($json['skola']) {
    $json['bakaUrl']='https://'.$_POST['bakaUrl'].'/';
    goto urlGet;
    }
  
  if(strpos($_POST['bakaUrl'],'/login.aspx')) {
    curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx','http://'.$_POST['bakaUrl']));
    curl_exec($ch);
    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
      curl_setopt($ch,CURLOPT_URL,'http://'.$_POST['bakaUrl']);  // bez http s loginem
      $html=curl_exec($ch);
      @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
      $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
      $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
      if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
      }
    if($json['skola']) {
      $json['bakaUrl']=str_replace('login.aspx','','http://'.$_POST['bakaUrl']);
      goto urlGet;
      }
    }
  
  if(strpos($_POST['bakaUrl'],'/login.aspx')) {
    curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx','https://'.$_POST['bakaUrl']));
    curl_exec($ch);
    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
      curl_setopt($ch,CURLOPT_URL,'https://'.$_POST['bakaUrl']);  // bez https s loginem
      $html=curl_exec($ch);
      @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
      $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
      $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
      if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
      }
    if($json['skola']) {
      $json['bakaUrl']=str_replace('login.aspx','','https://'.$_POST['bakaUrl']);
      goto urlGet;
      }
    }
  
  if(strpos($_POST['bakaUrl'],'http://')) {
    curl_setopt($ch,CURLOPT_URL,str_replace('http://','https://',$_POST['bakawebUrl'].'login.aspx')); // http místo https s lomítkem
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($json['skola']) {
      $json['bakawebUrl']=str_replace('http://','https://',$_POST['bakawebUrl']);
      exit(json_encode($json));
      }
    curl_setopt($ch,CURLOPT_URL,str_replace('http://','https://',$_POST['bakawebUrl'].'/login.aspx')); // http místo https bez lomítka
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    $table=$funkce->getElementsByClassName($body->getElementsByTagName('table'),'logintable')[0];
    if($table) $prihlInput=$table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->getElementsByTagName('table')->item(0)->getElementsByTagName('input')->item(0)->getAttribute('name');
    if($json['skola']) {
      $json['bakawebUrl']=str_replace('http://','https://',$_POST['bakawebUrl'].'/');
      exit(json_encode($json));
      }
    }

  exit(json_encode(array('s'=>0,'e'=>1)));
  
  /* přihlášení */

urlGet:

  curl_setopt($ch,CURLOPT_REFERER,$json['bakaUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_POST,true);
  $postdata='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&'.urlencode($prihlInput).'='.$_POST['bakaPrihlJmeno'].'&ctl00%24cphmain%24TextBoxHeslo='.$_POST['bakaHeslo'].'&ctl00%24cphmain%24ButtonPrihlas=&DXScript=1_44%2C1_76%2C2_27';
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
  $html=curl_exec($ch);
  if(curl_getinfo($ch,CURLINFO_HTTP_CODE)!=302) {
    $json['s']=0;
    $json['e']= (strpos($html,'<span id="cphmain_LabelChyba" class="lberr">Přihlášení neproběhlo v pořádku.</span>')) ? 2 : 0;
    exit(JSON_encode($json));
    }
  
  /* načtení úvodu a zjištění jména */
  
  curl_setopt($ch,CURLOPT_POST,false);
  curl_setopt($ch,CURLOPT_POSTFIELDS,null);
  curl_setopt($ch,CURLOPT_REFERER,$json['bakaUrl'].'login.aspx');
  curl_setopt($ch,CURLOPT_URL,$json['bakaUrl'].'uvod.aspx');
  $html=curl_exec($ch);
  $body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['jmeno']=trim($funkce->getElementsByClassName($body->getElementsByTagName('td'),'logjmeno')[0]->firstChild->nodeValue);
  if($json['skola'] && $json['jmeno']) $json['s']=1;
  
  exit(JSON_encode($json));
