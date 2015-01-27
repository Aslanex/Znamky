<?
  require_once 'funkce.php';
  if(!$funkce) $funkce=new funkce();
  if(!$_POST['bakawebUrl']) exit('retarded');
  $json['state']='success';
  $_POST['bakawebUrl']=trim($_POST['bakawebUrl']);
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
  $body=new DOMDocument();
  
  if(stripos($_POST['bakawebUrl'],'/login.aspx')) { // s loginem
    curl_setopt($ch,CURLOPT_URL,str_replace('login.aspx','uvod.aspx',$_POST['bakawebUrl']));
    curl_exec($ch);
    if(curl_getinfo($ch,CURLINFO_HTTP_CODE)==302) {
      curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl']);
      $html=curl_exec($ch);
      @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
      $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
      if($json['skola'] && str_replace('login.aspx','',$_POST['bakawebUrl'])) {
        $json['bakawebUrl']=str_replace('login.aspx','',$_POST['bakawebUrl']);
        exit(json_encode($json));
        }
      }
    }

  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'login.aspx'); // s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']=$_POST['bakawebUrl'];
    exit(json_encode($json));
    }

  if(stripos($_POST['bakawebUrl'],'/login.aspx')) { // s loginem
    curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl']);
    $html=curl_exec($ch);
    @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
    $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
    if($json['skola'] && str_replace('login.aspx','',$_POST['bakawebUrl'])) {
      $json['bakawebUrl']=str_replace('login.aspx','',$_POST['bakawebUrl']);
      exit(json_encode($json));
      }
    }

  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'/login.aspx'); // bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']=$_POST['bakawebUrl'].'/';
    exit(json_encode($json));
    }

  curl_setopt($ch,CURLOPT_URL,'http://'.$_POST['bakawebUrl'].'login.aspx'); // bez http s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']='http://'.$_POST['bakawebUrl'];
    exit(json_encode($json));
    }

  curl_setopt($ch,CURLOPT_URL,'https://'.$_POST['bakawebUrl'].'login.aspx'); // bez https s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']='https://'.$_POST['bakawebUrl'];
    exit(json_encode($json));
    }

  curl_setopt($ch,CURLOPT_URL,'http://'.$_POST['bakawebUrl'].'/login.aspx'); // bez http bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']='http://'.$_POST['bakawebUrl'].'/';
    exit(json_encode($json));
    }

  curl_setopt($ch,CURLOPT_URL,'https://'.$_POST['bakawebUrl'].'/login.aspx'); // bez https bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']='https://'.$_POST['bakawebUrl'].'/';
    exit(json_encode($json));
    }

  $_POST['bakawebUrl']=str_replace('http://','https://',$_POST['bakawebUrl']);
  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'login.aspx'); // https místo http s lomítkem
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']=$_POST['bakawebUrl'];
    exit(json_encode($json));
    }

  curl_setopt($ch,CURLOPT_URL,$_POST['bakawebUrl'].'/login.aspx'); // https místo http bez lomítka
  $html=curl_exec($ch);
  @$body->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
  $json['skola']=trim($funkce->getElementsByClassName($body->getElementsByTagName('div'),'nazevskoly')[0]->firstChild->nodeValue);
  if($json['skola']) {
    $json['bakawebUrl']=$_POST['bakawebUrl'].'/';
    exit(json_encode($json));
    }

  $json['state']='not found';
  exit(json_encode($json));
?>
