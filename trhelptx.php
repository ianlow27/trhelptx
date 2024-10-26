<?php
//-------------------------------------------------
$usage = 
"github.com/ianlow27/trhelptx (v0.0.2 241021-0750)".
" - CLI usage: trhelptx.php -f{text-file} -v{vocab-file}";
//-------------------------------------------------
define("__SKIPWORDS__", "|this|is|are|were|will|be|soon|a|the|these|those|I|of|in|front|behind|instead|was|in|that|needed|"); 

file_put_contents("dbg.txt", date("Y:m:d H:i:s")); //<=ENABLE DEBUG
if(file_exists("dbg.txt")){ unlink("dbg.txt"); }   //<=DISABLE DEBUG
//-------------------------------------------------
if(file_exists("dbg.txt")){ define("dbg", true ); 
}else{                      define("dbg", false); }
define("__VOCABFILE__","vocab_CY_2410.txt");       //<=DEFAULT VOCAB FILE
define("__TEXTFILE__","ajxtxt.txt");               //<=DEFAULT INPUT FILE
$txtfile = "";
$vcbfile = "";
if(!isset($_SERVER["SERVER_PORT"])){ //<=CALLED FROM CLI
  if(($txtfile=='')||($vcbfile=='')){
    dbge(1);
    for($i=1;$i<3;$i++){
      if      (substr($argv[$i],0,2)=='-f'){
        $txtfile = substr($argv[$i],2);
      }else if(substr($argv[$i],0,2)=='-v'){
        $vcbfile = substr($argv[$i],2);
      }
    }//endfor
  }else {
    dbge(2);
    echo $usage; return;
  }//endif
}else { //<=CALLED FROM WEB SERVER OR VIA AJAX
  if((isset($_POST["txtall"]))){ 
    $txtfile = __TEXTFILE__;
    file_put_contents($txtfile. "_all.txt", $_POST["txtall"]);
  } 
  if((isset($_POST["vcbtxt"]))){  //<=IF NEW VOCAB DATA SENT VIA AJAX
    $vcbfile = __VOCABFILE__;
    file_put_contents($vcbfile. "_new.txt", $_POST["vcbtxt"]);
  } 
  if((isset($_POST["ajxtxt"]))){ //<=IF NEW TEXT DATA SENT VIA AJAX
    $txtfile = __TEXTFILE__;
    dbge("7A", $txtfile."_out.txt___". substr($_POST["ajxtxt"],0,200));
    file_put_contents($txtfile. "_out.txt", $_POST["ajxtxt"]);
  }//endif
  if(($txtfile=='')||($vcbfile=='')){
    if((isset($_GET["f"]))&&(isset($_GET["v"]))){
      dbge(5);
      $txtfile = $_GET['f'];
      $vcbfile = $_GET['v'];
      echo $txtfile."______". $vcbfile;
    }//endif
  }//endif
  if($vcbfile==''){ $vcbfile = __VOCABFILE__; }
  if($txtfile==''){ $txtfile = __TEXTFILE__ ; }
}//endif
//----------------
$count=0;
$outputstr="";
$newwords="";
$paragraph="";
$htmlpara="";
$htmlstr="";
$htmlhdr=
  '<!DOCTYPE html><html><head>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
.speng {color:black; font-weight:bold;text-decoration:underline;}
.splng {color:blue; font-weight:bold;}
.sptyp {color:blue; font-weight:normal;}
body  {margin:0px; padding:0px; } 
</style>
</head><body><b>'.$usage.'</b><br/>';
$htmlftr= '</body></html>';
//----------------
dbge(5);
if(isset($_SERVER["SERVER_PORT"])){
  if(!isset($_POST["ajxtyp"])){ //<=CALLED FROM MAIN WEBPAGE NOT AJAX
    echo $htmlhdr;
    dbge(6);
    echo "
    <table style='width:95%;'>
    <tr><td valign='top' style='width:80%;'>
    <textarea id='txtall' style='font-size:80%;width:98%;height:70px;'>". 
    (file_exists($txtfile."_all.txt") ? file_get_contents($txtfile. "_all.txt") : "" )
    ."</textarea>
    
    </td>
    <td valign='top' style='width:15%;'>
    <script>
    function proctxt(){
      const txt1=document.getElementById('txt1'); 
      const txt2=document.getElementById('txt2');
      const txtall=document.getElementById('txtall');
      //alert(txt1.value);
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
         if (this.readyState == 4 && this.status == 200) {
           const aresp = this.responseText.split(/%%%___%%%/); 
           //document.getElementById('resp1').innerHTML = this.responseText; 
           document.getElementById('resp1').innerHTML = aresp[0];
           document.getElementById('txt1').value = aresp[1];
           document.getElementById('txt2').value = aresp[2];
           document.getElementById('txtall').value = aresp[3];
           //alert(this.responseText);
          }
        };
      xhttp.open('POST', 'trhelptx.php', true);
      xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      const params = 'ajxtxt='+txt1.value +
                     '&vcbtxt='+txt2.value + 
                     '&ajxtyp=AJAX' +
                     '&txtall='+txtall.value;  
      xhttp.send(params);
    }
    </script>

    <a href='./README.md'>Help</a><br/>

    <button onclick='proctxt();' style='width:98%;margin-top:20px;margin-bottom:20px;'>Submit</button>
    
    </td></tr></table>
    ".
    "

    <textarea id='txt1' style='font-size:80%;width:65%;height:200px;'>".
    file_get_contents($txtfile. "_out.txt")
    ."</textarea>
    <textarea id='txt2' style='font-size:80%;width:28%;height:200px;'>".
    file_get_contents($vcbfile. "_new.txt")

    ."</textarea><br/>
    <div id='resp1' style='font-size:120%;margin:40px;'></div>
    <script>
      document.getElementById('txt1').focus(); 
    </script>
    ".$htmlftr;
    exit;
  }//endif
}//endif
//----------------------------
dbge(8);
$vocab = explode("\n", 
  file_get_contents($vcbfile). "\n". 
  preg_replace("/^\*/m", "",
    preg_replace("/\//", "\t", file_get_contents($vcbfile. "_new.txt")) 
  )
) ;
//----------------------------
//----------------------------
dbge(9);
$lines = explode("\n", 
  ( (isset($_SERVER["SERVER_PORT"]))  
     ?                                //<=IF CALLED FROM SERVER
     file_get_contents($txtfile."_out.txt")
     :                                //<=IF CALLED FROM CLI
     file_get_contents($txtfile) )
  . "\n\n");
foreach($lines as $line){
  $count++;
  //if($count > 10) break;
  $line = 
    preg_replace("/([—\‘\“\'\"\(\)]{1,1})([a-zA-Z0-9\-]+)([\/]{0,1})/", "$1 $2$3",  
      //preg_replace("/(\w\/)([—\’\”\,\.;:\=\-\(\)'\"\/\\!\?\w\d]{1,1})/", "$1 $2", 
        preg_replace("/(\w)([—\’\”\,\.;:\=\-\(\)'\"\/\\!\?]{1,1})/", "$1 $2", 
        $line
        )
      //)
    ); 
  
  file_put_contents("dbg.txt", $line. "\n\n", FILE_APPEND);

  $words = explode(" ", $line);
  foreach($words as $word){

    if(!skipword($word)){
      $word .= "/";
      //dbge(178, $word . "__" . skipword($word));
    }  

    if(substr($word,-1)=="/"){
        $listwords = trim(getEntries(substr($word,0,-1)));
        if ($listwords == ""){
          dbge( "8a",  $word);
          $newwords.="*?/?/". substr($word,0,-1). "\n"; 
          $paragraph.=" ". $word;
          $htmlpara .=" ". $word;
        }else if(substr($listwords,0,5) == "^?[?]"){
          if(dbg) echo "<li>8b_". $word;
          $paragraph.=" ". $word;
          $htmlpara .=" <span class='speng'>". $word. "</span>";
        }else {
          if(dbg) echo "<li>8c_". $word;
          $paragraph.=" ". $word. "". $listwords;
          $htmlpara .=" ". "<span class='speng'>".$word. "". "</span><span class='splng'>".$listwords."</span>";
        }
    }else {
      if(dbg) echo "<li>8d_[". $word. "]";

      if(!preg_match("/[\^\[\]]/", $word)){
        $paragraph.=" ". $word;

        if(preg_match("/[\/\^]/", $word)){ //If word contains earlier found wordlist:
          $word = preg_replace("/([\/\]]{1,1})/", "$1%%%", $word); //:format wordlist
          $word = preg_replace("/([\^\[]{1,1})/", "%%%$1", $word); //:format wordlist
          $tmpword = explode("%%%", $word);
          //print_r($tmpword); 
          $newword="";
          foreach($tmpword as $tmpwrd){
            if      (substr($tmpwrd,-1)=="/"){
              $newword .= "<span class='speng'>".$tmpwrd."</span>";
            }else if(substr($tmpwrd,0,1)=="^"){
              $newword .= "<span class='splng'>".$tmpwrd."</span>";
            }else if(substr($tmpwrd,0,1)=="["){
              $newword .= "<span class='sptyp'>".$tmpwrd."</span>";
            }else {
              $newword .= $tmpwrd;
            }
          }
          $htmlpara .=" <b>". $newword. "</b>";
        }
      }else {
        if(!preg_match("/[\^\[\]]/", $word)){
          $htmlpara .=" ". $word;
        }
      }
    }
  }//endforeach
  if(trim($line) == ""){
    $outputstr.=trim($paragraph)."\n\n";
    //$htmlpara = preg_replace("/\[/", "<span class='sptyp'>[", $htmlpara);
    //$htmlpara = preg_replace("/\]/", "]</span>", $htmlpara);
    $htmlstr  .=trim($htmlpara)."<br/><br/>\n\n";
    $paragraph = "";
    $htmlpara= "";
  }
}//endforeach
//----------------------------
//----------------------------
if(dbg) echo "<li>a";

$outputstr=trim($outputstr);
file_put_contents($txtfile."_out.txt", trim($outputstr));
file_put_contents($txtfile."_out.html", $htmlhdr .$htmlstr. $htmlftr);
file_put_contents($vcbfile."_new.txt", $newwords, FILE_APPEND | LOCK_EX );
if(isset($_SERVER["SERVER_PORT"])) 
  echo $htmlstr. $htmlftr.
        "%%%___%%%". $outputstr.
        "%%%___%%%". file_get_contents($vcbfile."_new.txt").
        "%%%___%%%". file_get_contents($txtfile."_all.txt")
        ;
//-------------------------------------------------
function getEntries($engword){
global $vocab;
$retval="";
  foreach($vocab as $vcbline){
    $vcbitems = explode("\t", trim($vcbline));
    if(isset($vcbitems[2])){
      $engword = strtolower($engword);
      if($vcbitems[2] == $engword){
        if($retval != "") $retval .= "-";
        $retval.="^". conv2Accent($vcbitems[0]). "[". $vcbitems[1]. "]";
      }else if($vcbitems[2] == en_adv2adj(  $engword)){
        if($retval != "") $retval .= "-";
        $retval.="^". conv2Accent(cy_adj2adv($vcbitems[0])). "[adv]";
      }else if($vcbitems[2] == "".en_plur2sing($engword)){
        if($retval != "") $retval .= "-";
        $retval.="^". conv2Accent(cy_sing2plur($vcbitems[0])). "[". $vcbitems[1]. "s]";
      }
    }
  }//endforeach
  if($retval=="") return "";
  return "^".sortUniqStr($retval, "^");
}//endfunc
//-------------------------------------------------
function skipword($pengwd){
  $engwd = strtolower($pengwd);
  dbge(271, $engwd);

  if(strlen($engwd) <= 3){
    dbge(275, $engwd);
    return true;
  }else if(preg_match("/^([^a-z]+)/", $engwd)){
    dbge(278, $engwd);
    return true;
  }else if(preg_match("/[\^\[\]]/", $engwd)){
    dbge(280, $engwd);
    return true;
  }else if(!preg_match("/[a-z]/", $pengwd)){
    dbge(284, $engwd);
    return true;
  }else if(preg_match("/^(xxx|".  __SKIPWORDS__ ."|xxx)$/", trim($engwd))){
    dbge(287, $engwd);
    return true;
  }else if(substr($engwd, -1)=="/"){
    dbge(290, $engwd);
    return true;
  }
  dbge(293, $engwd);

  return false;

}//endfunc
//-------------------------------------------------
function dbge($ref, $str=""){
  //global dbg;
  if (dbg) echo "<li>".$ref."_____[".$str."]";
}//endfunc
//-------------------------------------------------
function cy_sing2plur($str){
  return $str;
}//endfunc
//-------------------------------------------------
function cy_adj2adv($str){
  $str=cy_mut($str,"soft");
  return "yn_".$str;
}//endfunc
//-------------------------------------------------
function cy_mut($str, $mut){
  $char1 = substr($str,0,1);
  $char2 = substr($str,0,2);
  //echo $char1."__".$char2."\n";
  if($mut == "soft"){
    if(($char1=="b")||($char1=="m")){
      $str = "f". substr($str,1);
    }else if($char1=="c"){
      $str = "g". substr($str,1);
    }else if($char1=="d"){
      $str = "dd". substr($str,1);
    }else if($char1=="g"){
      $str = "". substr($str,1);
    }else if($char1=="p"){
      $str = "b". substr($str,1);
    }else if($char1=="t"){
      $str = "d". substr($str,1);
    }else if($char2=="ll"){
      $str = "l". substr($str,2);
    }else if($char2=="rh"){
      $str = "r". substr($str,2);
    }
  }
  return $str;
}//endfunc
//-------------------------------------------------
function en_adv2adj($str){
  $retstr= preg_replace("/ly$/", "", $str);
  return $retstr;
}//endfunc
//-------------------------------------------------
function en_plur2sing($str){
  $retstr=$str;
  if      (preg_match("/ies$/", $str)){
    $retstr = preg_replace("/ies$/", "y", $str);
  }else if(preg_match("/s$/", $str)){
    $retstr = preg_replace("/s$/", "", $str);
  }
  return $retstr;
}//endfunc
//-------------------------------------------------
function sortUniqStr($str, $sep){
  $str = preg_replace("/\-\^/", "^", $str);
  $retval="";
  $sortarr = explode($sep, $str);
  sort($sortarr);
  foreach(array_unique($sortarr) as $entry){
    if($retval != "") $retval .= "-".$sep; 
    $retval.=$entry;
  }//endforeach
  return $retval;
}//endfunc
//-------------------------------------------------
function conv2Accent($str){
if(preg_match("/\^/", $str)){
  $str = preg_replace("/a\^/", "â", $str);
  $str = preg_replace("/e\^/", "ê", $str);
  $str = preg_replace("/i\^/", "î", $str);
  $str = preg_replace("/o\^/", "ô", $str);
  $str = preg_replace("/u\^/", "û", $str);
  $str = preg_replace("/w\^/", "ŵ", $str);
  $str = preg_replace("/y\^/", "ŷ", $str);
}
if(preg_match("/%/", $str)){
  $str = preg_replace("/a%/", "ä", $str);
  $str = preg_replace("/e%/", "ë", $str);
  $str = preg_replace("/i%/", "ï", $str);
  $str = preg_replace("/o%/", "ö", $str);
  $str = preg_replace("/u%/", "ü", $str);
  $str = preg_replace("/y%/", "ÿ", $str);
}
if(preg_match("/´/", $str)){
  $str = preg_replace("/a´/", "á", $str);
  $str = preg_replace("/e´/", "é", $str);
  $str = preg_replace("/i´/", "í", $str);
  $str = preg_replace("/o´/", "ó", $str);
  $str = preg_replace("/u´/", "ú", $str);
  $str = preg_replace("/y´/", "ý", $str);
}
if(preg_match("/`/", $str)){
  $str = preg_replace("/a`/", "à", $str);
  $str = preg_replace("/e`/", "è", $str);
  $str = preg_replace("/i`/", "ì", $str);
  $str = preg_replace("/o`/", "ò", $str);
  $str = preg_replace("/u`/", "ù", $str);
  $str = preg_replace("/y`/", "ỳ", $str);
}
return $str;
}//endfunc
//-------------------------------------------------
?>
