<?php
//-------------------------------------------------
$usage = 
"github.com/ianlow27/trhelptx (v0.0.2 241021-0750)".
" - CLI usage: trhelptx.php -f{text-file} -v{vocab-file}";
//-------------------------------------------------
file_put_contents("dbg.txt", date("Y:m:d H:i:s")); //<=ENABLE DEBUG
if(file_exists("dbg.txt")){ unlink("dbg.txt"); }   //<=DISABLE DEBUG
//-------------------------------------------------
if(file_exists("dbg.txt")){ define("dbg", True ); 
}else{                      define("dbg", False); }
define("__VOCABFILE__","vocab_CY_2410.txt");       //<=DEFAULT VOCAB FILE
define("__TEXTFILE__","ajxtxt.txt");               //<=DEFAULT INPUT FILE
$txtfile = "";
$vcbfile = "";
if(!isset($_SERVER["SERVER_PORT"])){ //<=CALLED FROM CLI
  if(($txtfile=='')||($vcbfile=='')){
    if(dbg) echo "<li>3";
    for($i=1;$i<3;$i++){
      if      (substr($argv[$i],0,2)=='-f'){
        $txtfile = substr($argv[$i],2);
      }else if(substr($argv[$i],0,2)=='-v'){
        $vcbfile = substr($argv[$i],2);
      }
    }//endfor
  }else {
    if(dbg) echo "<li>7";
    echo $usage; return;
  }//endif
}else { //<=CALLED FROM WEB SERVER OR VIA AJAX
  if((isset($_POST["vcbtxt"]))){  //<=IF NEW VOCAB DATA SENT VIA AJAX
    $vcbfile = __VOCABFILE__;
    file_put_contents($vcbfile. "_new.txt", $_POST["vcbtxt"]);
  } 
  if((isset($_POST["ajxtxt"]))){ //<=IF NEW TEXT DATA SENT VIA AJAX
    $txtfile = __TEXTFILE__;
if(dbg) echo "<li>7A___________".$txtfile."_out.txt___". substr($_POST["ajxtxt"],0,200);
    file_put_contents($txtfile. "_out.txt", $_POST["ajxtxt"]);
  }//endif
  if(($txtfile=='')||($vcbfile=='')){
    if((isset($_GET["f"]))&&(isset($_GET["v"]))){
      if(dbg) echo "<li>1";
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
if(dbg) echo "<li>5";
if(isset($_SERVER["SERVER_PORT"])){
  if(!isset($_POST["ajxtyp"])){ //<=CALLED FROM MAIN WEBPAGE NOT AJAX
    echo $htmlhdr;
    if(dbg) echo "<li>6";
    echo "
    <textarea id='txt1' style='font-size:80%;width:65%;height:200px;'>".
    file_get_contents($txtfile. "_out.txt")
    ."</textarea>
    <textarea id='txt2' style='font-size:80%;width:28%;height:200px;'>".
    file_get_contents($vcbfile. "_new.txt")
    ."</textarea><br/>
    <button onclick='proctxt();' style='margin-bottom:20px;'>Submit</button>
    <div id='resp1' style='font-size:120%;margin:40px;'></div>
    <script>const txt1=document.getElementById('txt1'); txt1.focus();
    function proctxt(){
      //alert(txt1.value);
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
         if (this.readyState == 4 && this.status == 200) {
           const aresp = this.responseText.split(/%%%___%%%/); 
           //document.getElementById('resp1').innerHTML = this.responseText; 
           document.getElementById('resp1').innerHTML = aresp[0];
           document.getElementById('txt1').value = aresp[1];
           document.getElementById('txt2').value = aresp[2];
           //alert(this.responseText);
          }
        };
      xhttp.open('POST', 'trhelptx.php', true);
      xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      var params = 'ajxtxt='+txt1.value+'&vcbtxt='+txt2.value + '&ajxtyp=AJAX'; 
      xhttp.send(params);
    }
    </script>
    ".$htmlftr;
    exit;
  }//endif
}//endif
//----------------------------
if(dbg) echo "<li>8";
$vocab = explode("\n", 
  file_get_contents($vcbfile). "\n". 
  preg_replace("/\//", "\t", file_get_contents($vcbfile. "_new.txt")) 
) ;
//----------------------------
//----------------------------
if(dbg) echo "<li>9";
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
  $line = preg_replace("/([\w\/]{1,1})([\,\.;:]{1,1})/", "$1 $2", $line);
  $words = explode(" ", $line);
  foreach($words as $word){
    if(substr($word, -1)=='/'){
        $listwords = trim(getEntries(substr($word,0,-1)));
        if ($listwords == ""){
if(dbg) echo "<li>8a_". $word;
          $newwords.="?/?/". substr($word,0,-1). "\n"; 
          $paragraph.=" ". $word; 
          $htmlpara .=" ". $word;
        }else if(substr($listwords,0,5) == "^?[?]"){
          if(dbg) echo "<li>8b_". $word;
          $paragraph.=" ". $word;
          $htmlpara .=" <span class='speng'>". $word. "</span>";
        }else {
          if(dbg) echo "<li>8c_". $word;
          $paragraph.=" ". $word. $listwords;
          $htmlpara .=" ". "<span class='speng'>".$word. "</span><span class='splng'>".$listwords."</span>";
        }
    }else {
      if(dbg) echo "<li>8d_". $word;
      $paragraph.=" ". $word;
      if(preg_match("/[\/\^]/", $word)){ 
        $word = preg_replace("/([\/\]]{1,1})/", "$1%%%", $word);
        $word = preg_replace("/([\^\[]{1,1})/", "%%%$1", $word);
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
      }else {
        $htmlpara .=" ". $word;
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
file_put_contents($txtfile."_out.txt", trim($outputstr));
file_put_contents($txtfile."_out.html", $htmlhdr .$htmlstr. $htmlftr);
file_put_contents($vcbfile."_new.txt", $newwords, FILE_APPEND | LOCK_EX );
if(isset($_SERVER["SERVER_PORT"])) 
  echo $htmlstr. $htmlftr.
        "%%%___%%%". $outputstr.
        "%%%___%%%". file_get_contents($vcbfile."_new.txt");
//-------------------------------------------------
function getEntries($engword){
global $vocab;
$retval="";
  foreach($vocab as $vcbline){
    $vcbitems = explode("\t", trim($vcbline));
    if(isset($vcbitems[2])){
      if($vcbitems[2] == $engword){
        //if(($vcbitems[0]!="?")&&($vcbitems[1]!="?")){
          if($retval != "") $retval .= "-";
          $retval.="^". conv2Accent($vcbitems[0]). "[". $vcbitems[1]. "]";
        //}
      }
    }
  }//endforeach
  if($retval=="") return "";
  return "^".sortUniqStr($retval, "^");
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
