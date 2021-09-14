<?php //First line has your configuration:
$hideControlElements=true; $lang=auto; $bgc=g; $imgsperline=5; $imgspercolumn=3; $allowZip=false; $autoplay=true; $initialDelay=5; $startFit=true; $stopOnMouseOver=true; $showTitle=false; $showPicNum=false; $showPicSize=false; $dateOrder=false; $randomOrder=true; $useFading=true; $fadeMode=3; $fadeSteps=6; $fadeTime=70; $bgcolor="#000000";  ?><?PHP

//put this file in the same directory as your pictures and call it - works!
//mixed functionality file in order to have everything in one file.
//Details available at http://phpshow.panmental.de
//
//IN CASE OF PROBLEMS RUN "phpshow.php?diag" TO GET SOME DIAGNOSIS
//
//DO NOT PUT ANYTHING IN FRONT OF THE OPENING "<?PHP" TAG;
//IF YOU DO YOU IT ANYWAY YOU HAVE TO SET $GD_WORKAROUND="oldGD"; AND $allowZip=false;
//
//first parameter (e.g. "phpshow.php?image&...") is the mode
//available modes are 
//image:  returns the picture resized
//browse: overview of all pics
//slides: slideshow
//opaque: semi-transparent png images for fading
//css:    stylesheet
//
//author: Johannes Knabe, July 2004 - July 2008, jknabe@panmental.de

//note that all the "if(!isset($..." allow for easy overwriting of the defaults, e.g. you can have a script
//that pre-defines some of these values (say:  $browsertitle="South America trip" ) and then refers to the
//main script location (for example:  include '../scripts/phpshow.php'; )
if((!isset($SCRIPT_NAME))||($SCRIPT_NAME==""))
  $SCRIPT_NAME=$_SERVER["SCRIPT_NAME"];
if((!isset($SERVER_NAME))||($SERVER_NAME==""))
  $SERVER_NAME=$_SERVER["SERVER_NAME"];
if(!isset($stylesheet))
  $stylesheet= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$SCRIPT_NAME?css\">"; //$stylesheet="<link rel=\"stylesheet\" type=\"text/css\" href=\"http://yourdomain.com/phpshow.css\">"; 
if(!isset($browsertitle))
  $browsertitle="Image browser";
if(!isset($slidetitle))
  $slidetitle=  "Slideshow";
if(!isset($description))
  $description=  ""; //leave blank for no search engine indexing
if(!isset($keywords))
  $keywords=  "";   //leave blank for no search engine indexing
if(!isset($autoplay)){
  if((isset($hideControlElements))&&($hideControlElements))
    $autoplay=true;  //if there are no controls then we have to start automatically of course
  else
    $autoplay=true; //Shall presentation start automatically when in slideshow mode?
  }
if(!isset($stopOnMouseOver)) 
  $stopOnMouseOver=true;//Shall playing be interrupted while the mouse is over a photo (slideshow mode)?
if(!isset($openLinksIn))
  $openLinksIn="_blank"; //if there is a link associated with the clicked image, open link in this window (can also be "_self" or "_parent" or some name).
if(!isset($openLinkOpts))
  $openLinkOpts=""; //optionally you can set options for the new window (height, width, menubar, resizable ...)

//with value "auto" the script will use the preferred language of the browser, or, if not present, English.
if(!isset($lang))
  $lang="auto";   //can also be set to fixed "English" or "German" or "Swedish" or "Dutch" or "Norwegian" or "Portuguese" or "French" or "Russian" or "Italian"

//$hideControlElements=true;//useful for embedded frames

//$basePath="./"; //use "." for browsing current directory 
                  //or for example "./photos" to browse subdir photos

//browser settings
if(!isset($imgsperline))
  $imgsperline=5;             //5 columns
if(!isset($imgspercolumn))
  $imgspercolumn=3;           //default 3 rows
$maxperpage=$imgsperline*$imgspercolumn;
//IF BROWSE MODE DOES NOT WORK PROPERLY CHANGE $GD_WORKAROUND=""; TO $GD_WORKAROUND="oldGD";

//thumbnail settings
if(!isset($GD_WORKAROUND))
  $GD_WORKAROUND="";          //set this to "oldGD" or "newGD" to switch off automatic GD version detection
                            //set to "oldGD" if you experience any trouble with the browse mode!
if(!isset($scrwidth))
  $scrwidth=1020;             //important _only_ for resulution of preview thumbs in browser mode
if(!isset($thumbQuality))
  $thumbQuality=90;           //jpeg compression of thumbs (0 worst, 100 best quality but also largest size)
if(!isset($resample))
  $resample=true;             //resample images when creating thumbs - set to true if you have a fast server as quality is a lot better, but takes a bit of processing
$imgwidth=$scrwidth/$imgsperline; // - if possible make $scrwidth be divisible by both columns and rows without remainder
$realimgwidth=$imgwidth+5;  //plus 4 for cellspacing and 1 for imageborder
if(!isset($allowZip))
  $allowZip=true;             //do you want to give people the opportunity to download all photos in one zip-file?

//slideshow settings
if(!isset($initialDelay))
  $initialDelay=5;//how long shall the overall display time (in seconds) of a picture be initially
if(!isset($preloadForward))
  $preloadForward=3;//how many pictures do you want to preload ahead?
if(!isset($useFading))
  $useFading =true;//do you want to blend between images? (does not work in oldGD mode!)
if(!isset($fadeMode))
  $fadeMode  =rand(0,4);//0:classic, 1:right-to-left, 2:left-to-right, 3:square fading type
if(!isset($fadeColor))
  $fadeColor =255; //fade to this grey tone [e.g. 255 is #FFFFFF, 0 equals #000000]
if(!isset($bgcolor))
  $bgcolor="#fff";//*#aaa;/*this #aaa and the border #aaa's to #000*/
if(!isset($fadeSteps))
  $fadeSteps =6;   //how many shade levels for fadeing
if(!isset($fadeTime))
  $fadeTime  =70;  //milliseconds for one shade level 
                 //(i.e. overall fade time = $fadeSteps*$fadeTime; 
                 // overall fade time must not be bigger than 1200!)
if(!isset($startFit))
  $startFit  =true;//shall the slideshow initially fit images to screen or show the actual size
if(!isset($showTitle))
  $showTitle =true;//when the mouse is moved over the current image shall it's title be shown as a tooltip?
if(!isset($dateOrder))
  $dateOrder=false;//sort images by date rather than by their filename
if(!isset($randomOrder))
  $randomOrder=false;//display in random order; this only makes sense for slideshows and not browsing!
if(!isset($showPicSize))
  $showPicSize=false;//show or hide the size of the picture, e.g. (640x480)
if(!isset($showPicNum))
  $showPicNum=true; //show or hide the number of the current image and total number, e.g. 5/11

//select language:
if($lang=="auto"){
 if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $_SERVER['HTTP_ACCEPT_LANGUAGE']=strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="de")
   $lang="German"; //some kind of German set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="sv")
   $lang="Swedish"; //some kind of Swedish set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="nl")
   $lang="Dutch"; //some kind of Dutch set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="no")
   $lang="Norwegian"; //some kind of Norwegian set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="nb")
   $lang="Norwegian"; //some kind of Norwegian set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="nn")
   $lang="Norwegian"; //some kind of Norwegian set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="pt")
   $lang="Portuguese"; //some kind of Portuguese set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="fr")
   $lang="French"; //some kind of French set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="ru")
   $lang="Russian"; //some kind of Russian set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="es")
   $lang="Spanish"; //some kind of Spanish set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="it")
   $lang="Italian"; //some kind of Italian set as preferred language
  if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)=="da")
   $lang="Danish"; //some kind of Italian set as preferred language
  if($lang=="auto") //OK, no language found we have available, settle for default
   $lang="English";
  }else
 $lang="English";//default if preferred language is not transmitted
 }

//begin language specific strings for ENGLISH
$pauseString="pause";
$playString="play";
$actualString="actual size";
$fitString="fit on screen";
$browseString="browse pictures";
$slidesString="start slideshow";
$nextString="next";
$backString="back";
$nextPageString="next page";
$priorPageString="prior page";
$secString="sec";
$delayString="delay";
$notLoadedString="Image not fully loaded yet, please be patient...";
$downloadString="Click OK if you want to download all #NUM# photos in one single zip archive with a size of approximately #SIZE#.";
$downloadButton="download all";
$aftertext ="<div class=\"smallprint\" align=\"center\">If you are shown on a picture and don't want this let me know and I will remove it.<br/>
             <i>This page is autogenerated, <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">get the script</a></i>.</div>";
//$aftertext comes after the rest of the page at the bottom
//Some kind of disclaimer is surely useful and I would really
//very much appreciate it if you let people where they can
//get this script, but it's up to you if and where to do it.
//end language specific strings for ENGLISH


if($lang=="German"){ //begin language specific strings for GERMAN
$pauseString="Pause";
$playString="Start";
$actualString="tatsächliche Größe";
$fitString="Größe anpassen";
$browseString="Browser-Modus";
$slidesString="Slideshow-Modus";
$nextString="weiter";
$backString="zurück";
$nextPageString="nächste Seite";
$priorPageString="vorige Seite";
$secString="Sek";
$delayString="zeigen";
$notLoadedString="Bild ist noch nicht ganz geladen, bitte gedulde Dich einen Moment...";
$downloadString="Klicke OK wenn Du alle #NUM# Photos in einem einzigen zip-Archiv mit einer Grösse von ca. #SIZE# herunterladen willst.";
$downloadButton="Alle herunterladen";
$aftertext ="<div class=\"smallprint\" align=\"center\">Wenn Du auf einem Photo abgebildet bist und das nicht möchtest kontaktiere mich bitte und ich werde es sofort entfernen.<br/><i>Diese Seite ist automatisch erzeugt, <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">hol Dir das Skript</a></i>.</div>"; 
//$aftertext kommt nach dem Rest der Seite ganz unten
//Irgendeine Klausel sit sicherlich angebracht und ich wuerde
//es sehr begruessen wenn Du Leute wissen laesst wo sie dieses
//Skript bekommen koennen, aber ob und wie ist Deine Sache.
}//end language specific strings for GERMAN


//Swedish translation credits to Robert Eriksson, www.hellre.de
if($lang=="Swedish"){ //begin language specific strings for SWEDISH
$pauseString="Pause";
$playString="Start";
$actualString="Verklig storlek";
$fitString="Anpassa storlek";
$browseString="Bläddra";
$slidesString="Bildspel";
$nextString="Nästa";
$backString="Bakåt";
$nextPageString="Nästa sida";
$priorPageString="Föregående sida";
$secString="Sek";
$delayString="fördröjning";
$notLoadedString="Bilden laddas...vänta...";
$downloadString="Klicka OK ifall du vill ladda ner alla bilder i en enda
zip-fil med en storlek på ca. #SIZE# .";
$downloadButton="Ladda ner alla";
$aftertext ="<div class=\"smallprint\" align=\"center\">Syns du på ett foto
och inte vill vara med? Kontakta oss så åtgärdar vi detta.<br/><i>Denna
sida är automatiskt genererad, <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">ladda ner scriptet</a></i>.</div>";
//$aftertext visas längst ned på sidan
//Lite kredit för jobbet är uppskattat och jag skulle uppskatta
//ifall du talar om var folk kan ladda ner det här scriptet ifrån
//men det är upp till dig om och eventuellt vars du skriver detta.
}//end language specific strings for SWEDISH


//Dutch translation credits to Hans Gradussen, www.bizzibi.nl
if($lang=="Dutch"){ //begin language specific strings for DUTCH
$pauseString="Pause";
$playString="Start";
$actualString="Origineel formaat";
$fitString="Afmeting aanpassen";
$browseString="Overzicht";
$slidesString="Slideshow";
$nextString="verder";
$backString="terug";
$nextPageString="volgende pagina";
$priorPageString="vorige pagina";
$secString="Sec";
$delayString="tonen";
$notLoadedString="Afbeelding wordt geladen...";
$downloadString="Klik op OK om alle #NUM# foto's in een ZIP-bestand (ca.
#SIZE#) te downloaden.";
$downloadButton="Alles downloaden";
$aftertext ="<div class=\"smallprint\" align=\"center\"><A HREF=\"http://phpshow.panmental.de\" TARGET=\"_blank\">Credits</A></div>";
//Some kind of disclaimer is surely useful and I would really
//very much appreciate it if you let people where they can
//get this script, but it's up to you if and where to do it.
}//end language specific strings for Dutch


//Norwegian translation credits to Erik Retvedt, www.retvedt.as
if($lang=="Norwegian"){ //begin language specific strings for NORWEGIAN
$pauseString="Pause";
$playString="Spill";
$actualString="Aktuell størrelse";
$fitString="Tilpass skjerm";
$browseString="Klikk for å søke på bilder";
$slidesString="Klikk for å se lysbildeshow";
$nextString="Neste";
$backString="Tilbake";
$nextPageString="Neste side";
$priorPageString="Forrige side";
$secString="sek";
$delayString="mellom bilder";
$notLoadedString=" Bildene er ikke lastet opp, vennligst vær tålmodig...";
$downloadString="Klikk OK om du vil laste ned alle bildene i en ZIP fil. 
Størrelsen vil være på ca  #SIZE#.";
$downloadButton="Last ned alle";
$aftertext ="<div class=\"smallprint\" align=\"center\"><font 
size=\"2\">Hvis du er med på et bilde, men ikke vil være det så kan du 
sende meg en mail.<br/>
<i>Denne side er laget automatisk. Vil du teste scriptet trykk <a target=\"_blank\" 
href=\"http://phpshow.panmental.de/\">her</a></i>.</div>"; 
//comes after the rest of the page at the bottom
}//end language specific strings for NORWEGIAN

//Portuguese translation credits to Lígia Moreira
if($lang=="Portuguese"){//begin language specific strings for PORTUGUESE
$pauseString="pausa";
$playString="começar";
$actualString="tamanho actual";
$fitString="ajustar ao ecrã";
$browseString="clique para navegar";
$slidesString="clique para ver o slideshow";
$nextString="seguinte";
$backString="anterior";
$nextPageString="página seguinte";
$priorPageString="página anterior";
$secString="seg";
$delayString="demora";
$notLoadedString="Imagem ainda não completamente carregada; espere, por favor...";
$downloadString="Clique OK se quiser fazer o download de todas as #NUM# fotos num único arquivo zipado com o tamanho aproximado de #SIZE#.";
$downloadButton="fazer o download de todas";
$aftertext ="<div class=\"smallprint\" align=\"center\">Se aparecer numa imagem e não quiser que isso aconteça, dê-me a conhecer o facto e removê-la-ei.<br/>
             <i>Esta página é autogerada, obtenha o script <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">aqui</a></i>.</div>";
//$aftertext comes after the rest of the page at the bottom
//Some kind of disclaimer is surely useful and I would really
//very much appreciate it if you let people where they can
//get this script, but it's up to you if and where to do it.
}//end language specific strings for PORTUGUESE

//French translation credits to Lígia Moreira
if($lang=="French"){//begin language specific strings for FRENCH
$pauseString="pause";
$playString="jouer";
$actualString="taille actuelle";
$fitString="mode écran";
$browseString="cliquer pour naviguer";
$slidesString="cliquer pour voir le slideshow";
$nextString="suivante";
$backString="antérieure";
$nextPageString="page suivante";
$priorPageString="page antérieure";
$secString="sec";
$delayString="attente";
$notLoadedString="Image pas encore complètement téléchargée; attendez, s'il vous plaît...";
$downloadString="Cliquez OK si vous voulez télécharger toutes les #NUM# photos dans un seul archive zippé avec la taille, à peu près de #SIZE#.";
$downloadButton="télécharger toutes";
$aftertext ="<div class=\"smallprint\" align=\"center\">Si vous êtes dans une image et vous ne le voulez pas, faites- moi en savoir et je l'éffacerai.<br/>
             <i>Cette page est autogénérée, obtenez le script <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">ici</a></i>.</div>";
//$aftertext comes after the rest of the page at the bottom
//Some kind of disclaimer is surely useful and I would really
//very much appreciate it if you let people where they can
//get this script, but it's up to you if and where to do it.
}//end language specific strings for FRENCH

//Russian translation credits to Slavik Fursov
if($lang=="Russian"){ //begin language specific strings for Russian
$pauseString="Пауза";
$playString="старт";
$actualString="настоящий размер";
$fitString="по размерам экрана";
$browseString="показать все";
$slidesString="смотреть, как слайды";
$nextString="следующая";
$backString="назад";
$nextPageString="след. страница";
$priorPageString="пред. страница";
$secString="сек";
$delayString="задержка";
$notLoadedString="Фото загружается, подожтите чуть...";
$downloadString="Вы хотите загрузить #NUM# фотографий в одном архивированном файле размером около #SIZE#?";
$downloadButton="скачать всё";
$aftertext ="<div class=\"smallprint\" align=\"center\">Если вы на этой фото и хотите, чтобы этого не было - дайте нам знать.<br/>
             <i>Страница сгенерирована автоматически, скопируйте скрипт <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">here</a></i>.</div>";
//$aftertext comes after the rest of the page at the bottom
//Some kind of disclaimer is surely useful and I would really
//very much appreciate it if you let people where they can
//get this script, but it's up to you if and where to do it.
}//end language specific strings for Russian

//Spanish translation credits to Dieter Zilant
if($lang=="Spanish"){ //begin language specific strings for Spanish
$pauseString="pausa";
$playString="play";   //- tocar ("play" is commonly used)
$actualString="tamaño actual";
$fitString="acomodar en pantalla";
$browseString="haz clic para revisar";
$slidesString="haga clic para ver diapositivas";
$nextString="próximo";
$backString="regresar";
$nextPageString="próxima pagina";
$priorPageString="pagina anterior";
$secString="sec";
$delayString="espera";
$notLoadedString="No totalmente cargada, paciencia por favor...";
$downloadString="Haz clic en OK si quiere bajar todas las #NUM# fotos en un solo archivo (Zip) con tamaño aproximado de: #SIZE#.";
$downloadButton="bajar todas"; 
$aftertext ="<div class=\"smallprint\" align=\"center\">Si usted aparece en una foto y no quiere estar, avíseme y la quito.<br/> <i>Esta página es producido automáticamente, el guión está <a target=\"_blank\"_href=\"http://phpshow.panmental.de/\">aquí</a></i>.</div>";
}//end language specific strings for Spanish

//Italian translation credits to Roberto (fantasyl)
if($lang=="Italian"){ //begin language specific strings for ITALIAN
$pauseString="PAUSA";
$playString="RIPRODUCI";
$actualString="DIMENSIONI REALI";
$fitString="ADATTA ALLA PAGINA";
$browseString="SFOGLIA LA GALLERIA";
$slidesString="GUARDA LA GALLERIA";
$nextString="AVANTI";
$backString="INDIETRO";
$nextPageString="PROSSIMA PAGINA";
$priorPageString="PAGINA PRECEDENTE";
$secString="SECONDI";
$delayString="RITARDO";
$notLoadedString="IMMAGINE NON CARICATA COMPLETAMENTE. PER FAVORE ATTENDERE...";
$downloadString="SCEGLI OK SE VUOI SCARICARE TUTTE LE #NUM# FOTO IN UN SINGOLO ARCHIVIO COMPRESSO (ZIP) DI DIMENSIONE PARI A CIRCA #SIZE#.";
$downloadButton="SCARICA TUTTO";
$aftertext ="<div class=\"smallprint\" align=\"center\"><i><a target=\"_blank\" href=\"http://phpshow.panmental.de/\">Un grazie a phpshow per il fantastico script PHP</a></i></div>";
}
//$aftertext comes after the rest of the page at the bottom
//Some kind of disclaimer is surely useful and I would really
//very much appreciate it if you let people where they can
//get this script, but it's up to you if and where to do it.
//end language specific strings for ITALIAN

//Danish translation credits to Jens Knudsen.�
if($lang=="Danish"){ //begin language specific strings for Danish
$pauseString="Pause";
$playString="Kør";
$actualString="Aktuel størrelse";
$fitString="Tilpas skærm";
$browseString="Oversigt";
$slidesString="Start slideshow";
$nextString="Næste";
$backString="Tilbage";
$nextPageString="Næste side";
$priorPageString="Forrige side";
$secString="sek";
$delayString="mellem billeder";
$notLoadedString=" Billedene er ikke indlæst, vær tålmodig...";
$downloadString="Klik OK hvis du vil downloade alle #NUM# billeder i en ZIP fil. Størrelsen vil være på ca. #SIZE# ";
$downloadButton="Download alle";
$aftertext ="<div class=\"smallprint\" align=\"center\"><fontsize=\"2\">Hvis du er med på et billede, men ikke vil være det så kan du sende mig en mail.<br/>
<i>Denne side er lavet automatisk. Vil du teste scriptet tryk <a target=\"_blank\" href=\"http://phpshow.panmental.de/\">her</a></i>.</div>";
//comes after the rest of the page at the bottom
}
//end language specific strings for Danish


$outputForm=0;

$doctype="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"
       \"http://www.w3.org/TR/html4/loose.dtd\">\r\n";
$doctype="";//for strange reasons Firefox does not allow "?" in the stylesheet link when HTML 4.01 is used!
$encoding="<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\">\r\n";

$QUERY_STRING=$_SERVER['QUERY_STRING'];
if(!isset($SCRNAME))
  $SCRNAME=$SCRIPT_NAME; //for calling ourselves lateron
$sep="&"; //seperating string for the options in QUERY_STRING
if((isset($hideControlElements))&&($hideControlElements)){
$wiwiMargin=36;
$wiheiMargin=36;
}
else{
$wiwiMargin=50;
$wiheiMargin=132;
}

if(!isset($basePath))
  $basePath="./";
else
  if(substr($basePath,strlen($basePath)-1,1)!="/")
    $basePath.="/"; //always attach the final slash

//begin workaround for older PHP/GD versions
//helper function
function gd_version() {
   static $gd_version_number = null;
   if ($gd_version_number === null) {
       // Use output buffering to get results from phpinfo()
       // without disturbing the page we're in.  Output
       // buffering is "stackable" so we don't even have to
       // worry about previous or encompassing buffering.
       ob_start();
       phpinfo(8);
       $module_info = ob_get_contents();
       ob_end_clean();
       if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i",
               $module_info,$matches)) {
           $gd_version_number = $matches[1];
       } else {
           $gd_version_number = 0;
       }
   }
   return $gd_version_number;
}

$args=explode($sep,$QUERY_STRING);
if($GD_WORKAROUND==""){
 if($args[0]=="oldGD"){
  array_shift($args);
  $newGD=false;
  }
 else{
  if($args[0]=="newGD"){
    array_shift($args);
    $newGD=true;
    }
  else{
    if(gd_version() >= 2)
      $newGD=true;
    else
      $newGD=false;
    }
  }
if($newGD)
  $GD_WORKAROUND="newGD";
else
  $GD_WORKAROUND="oldGD";
}else
  if(($args[0]=="oldGD")||($args[0]=="newGD"))
   array_shift($args); //just get rid of the parameter
if($GD_WORKAROUND=="oldGD")
  $useFading=false; //Fading needs proper GD library
//end workaround for older PHP/GD versions

if((sizeof($args)>0) && $args[0]=="opaque"){
if(sizeof($args)>2)
  $faMo=$args[3];
else
  $faMo=0;
$scaleQ=20; //how high shell the resolution be; important for gradients
if($faMo==0){
 $size = 1;
 $image=imagecreatetruecolor($size, $size);
 imagealphablending($image,false);
 imagesavealpha($image,true);
 imagefilledrectangle($image, 0, 0, $size, $size, imagecolortransparent($image));
 $opaque=$args[1];
 if($opaque>127)
  $opaque=127;
 if($opaque<0)
  $opaque=0;
 $back2 = imagecolorallocatealpha($image, $fadeColor, $fadeColor, $fadeColor, $opaque);
 imagefilledrectangle($image, 0, 0, $size, $size, $back2);
}
if(($faMo==1)||($faMo==2)){
 $size=$fadeSteps*$scaleQ;
 $image=imagecreatetruecolor($size, 1);
 imagealphablending($image,false);
 imagesavealpha($image,true);
 imagefilledrectangle($image, 0, 0, $size, 1, imagecolortransparent($image));
 $step=($args[2])*$scaleQ;
 for($i=$size;$i>0;$i--){ 
  $opaque=127.0-round((127.0/$size)*($step+($size/2.0)-$i));
  if($opaque>127)
   $opaque=127;
  if($opaque<0)
   $opaque=0;
  $back2 = imagecolorallocatealpha($image, $fadeColor, $fadeColor, $fadeColor, $opaque);
  if($faMo==1)
   imagefilledrectangle($image, $size-$i-1, 0, $size-$i, 1, $back2);
  if($faMo==2)
   imagefilledrectangle($image, $i-1, 0, $i, 1, $back2);
 }
}
if($faMo==3){
 $size=$fadeSteps*$scaleQ;
 $image=imagecreatetruecolor($size*2, $size*2);
 imagealphablending($image,false);
 imagesavealpha($image,true);
 imagefilledrectangle($image, 0, 0, $size*2, $size*2, imagecolortransparent($image));
 $step=($args[2])*$scaleQ;
 for($i=$size;$i>0;$i--){ 
  $opaque=127.0-round((127.0/$size)*($step+($size/2.0)-$i));
  if($opaque>127)
   $opaque=127;
  if($opaque<0)
   $opaque=0;
  $back2 = imagecolorallocatealpha($image, $fadeColor, $fadeColor, $fadeColor, $opaque);
  imagerectangle($image, $i-1, $i-1, $size*2-$i, $size*2-$i, $back2);
 }
}
if($faMo==4){
 $size=$fadeSteps*$scaleQ;
 $image=imagecreatetruecolor($size*2, 1);
 imagealphablending($image,false);
 imagesavealpha($image,true);
 imagefilledrectangle($image, 0, 0, $size, 1, imagecolortransparent($image));
 $step=($args[2])*$scaleQ;
 for($i=$size;$i>0;$i--){ 
  $opaque=127.0-round((127.0/$size)*($step+($size/2.0)-$i));
  if($opaque>127)
   $opaque=127;
  if($opaque<0)
   $opaque=0;
  $back2 = imagecolorallocatealpha($image, $fadeColor, $fadeColor, $fadeColor, $opaque);
  imagefilledrectangle($image, $size*2-$i-1, 0, $size*2-$i, 1, $back2);
  imagefilledrectangle($image, $i-1, 0, $i, 1, $back2);
 }
}
header('Content-type: image/png');
// and finally, output the result
imagepng($image);
imagedestroy($image);
die();
}

//begin part for giving out resized images:

//Input: image_filename / sizeX / sizeY
//Output:the image reduced to sizeX*sizeY
//Use: normally images are resized after loading on client
//     - this will do the resize on server, saving loading time

if((sizeof($args)>0) && $args[0]=="image"){
 array_shift($args);
 if((sizeof($args)==3 || sizeof($args)==2)&&file_exists($basePath.rawurldecode($args[0]))){
  $args[0]=$basePath.rawurldecode($args[0]);
  $info=getimagesize("$args[0]");
  if($info[2]==1)
    $test=imagecreatefromgif($args[0]);
  if($info[2]==2)
    $test=imagecreatefromjpeg($args[0]);
  if($info[2]==3)
    $test=imagecreatefrompng($args[0]);
  if($info[2]==4)
    $test=imagecreatefromwbmp($args[0]);
  if(sizeof($args)==2){  //keep ratio
    $width=$args[1];
    $height=$info[1]*($args[1]/$info[0]);
    }
  else{                  //resize image
    $width=$args[1];
    $height=$args[2];
    }
   if($newGD) {
    $image = ImageCreateTrueColor($width, $height);
   } else {
    $image = ImageCreate($width, $height);
   }
  if($resample)
    imagecopyresampled($image,$test,0,0,0,0,$args[1],$height,$info[0],$info[1]);
  else
    imagecopyresized($image,$test,0,0,0,0,$args[1],$height,$info[0],$info[1]);
  imageinterlace($image,1);
  header('Content-type: image/jpeg');
  imagejpeg($image,"",$thumbQuality);
  imagedestroy($image);
  imagedestroy($test);
  }
 else
  if($QUERY_STRING!="" && sizeof($args)>0 && !file_exists(rawurldecode($args[0])))
   echo "File ".rawurldecode($args[0])." not found!";
  else
   echo "Parameters expected: SCRIPTNAME?filename$sep"."sizeX$sep"."sizeY or SCRIPTNAME?filename$sep"."sizeX. In the latter case original ratio will be kept.";
 die;
}
//end part for giving out resized images

//begin part for diagnosis
if((sizeof($args)>0) && $args[0]=="diag"){
 array_shift($args);
//phpinfo();
 error_reporting(E_ALL);
 echo "<html><head><title>Problem diagnosis for PHPShow script</title></head><body><H1>Problem diagnosis for PHPShow script</H1><H3>Contact the author, Johannes Knabe, with this output if you have problems with the script!</H3><br/>GD graphics library version (should be 2.0 or higher): <b>";
 $gdi=gd_info();
 echo $gdi["GD Version"];
 $testimg="http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]."?opaque".$sep."64".$sep."3".$sep."0";
 echo "</b><br/>Testing script image generation (<a href=\"$testimg\">$testimg</a>)... ";
 $ist=getimagesize($testimg);
 var_dump($ist);
 if($ist[0]>0)
   echo "<b>seems to be fine!</b>";
 else
   echo "^<b>ERROR ".$ist[0].". Please make sure there is nothing (really really nothing, not even whitespace) around the PHP tags!!!</b>";
 echo "<br/><br/>Finished. Enjoy the script!</body></html>";
 die();
}

//begin part for giving out stylesheet
if((sizeof($args)>0) && $args[0]=="css"){
 array_shift($args);
header('Content-type: text/css');
echo "/*
Contains formatting information for the automatic photo slideshow script.
Johannes Knabe, Bonn / Germany, 2005-07
            and St Albans / UK, 2006-08
*/

/*
ADD \"display:none\" IF YOU WANT TO HIDE CERTAIN ELEMENTS, 
E.G. CONTROL TO SWITCH BETWEEN SLIDESHOW AND BROWSER MODES
*/

/*hide everything but the picture:*/
";
if((isset($hideControlElements))&&($hideControlElements))
  echo "
  input,#picInfo,div.smallprint,.controlTable,a.goLink,select {
    display:none;
    }";
else
  echo "
  /*
  input,#picInfo,div.smallprint,.controlTable,a.goLink,select {
    display:none;
    }
  */";
echo "
/*hide elements one by one:*/
  input.slidebrowse { /*change modes button*/
    /*display:none;*/
    }
  input.sizebutton {  /*actual size / fit on screen button*/
    /*display:none;*/
    }
  select {            /*select delay between slides*/
    /*display:none;*/
  }
  a.goLink {          /*next / last links to speed up browsing*/
    /*display:none;*/
    }
  #picInfo {          /*info on the picture text; e.g. name and size*/
    /*display:none;*/
    }
  div.smallprint {    /*text under the picture, usually disclaimer*/
    font-size:smaller
    white-space:nowrap; 
    /*display:none;*/
    }
  .controlTable {    /*all control buttons*/
    /*display:none;*/
    }
  @media print {      /*hide stuff for printing*/
    a { text-decoration:none;
        color:black;}
    td.paddingBug { padding-top:6px }
    span.hideForPrint { display:none;}
    input { display:none;}
    select { display:none;}
    div.smallprint { display:none;}
    a.goLink { display:none;}
    }
  @media screen {     /*link definitions*/
    a {color: blue}
    a:link { text-decoration:none; }
    a:visited { text-decoration:none; }
    a:hover { text-decoration:underline; }
    a:active { text-decoration:underline; }
    a:focus { text-decoration:underline; }
    }

  body,h1,h2,h3,h4,p,ul,ol,li,dl,dt,dd,div,td,th,address,blockquote,span { font-family:Arial,sans-serif;    background-color: transparent; }

  p,ul,ol,li,dl,dt,dd,div,td,th,address,blockquote,span { font-size:14px; }

  body {
    color:            #000000;
    margin:           3px;
    padding:          0px;
  }  
  span.info {         /*user defined picture texts*/
    font-style:       italic;
  }
  td.currpic {        /*current picture frame*/
    border:           1px solid #aaa;
    background-color: #fff; /*should equal the fade-to color from the script*/
    padding:          5px;  /*some space needed as browsers are different*/
  }

  td.browsetd {
    padding-right:    1px;
  }

  div.tdpic{          /*browse-mode thumbnails*/
    border:           1px solid #aaa;
    background-color: #fff;
    padding:          3px;
  }

  table {
    vertical-align:   middle;
    background-color: transparent;
  }";
die;}
//end part for giving out stylesheet




//begin get image files
 Function get_Extension($m_FileName){
 	$path_parts = pathinfo($m_FileName);
 		if ($path_parts["extension"]) {
 			$m_Extension = strtolower($path_parts["extension"]);
 			return(strtoupper($m_Extension));
 			}
 		else { return "unbekannt"; }
 }
 
 function check_image($filename){
 $temp=strtoupper(get_Extension($filename));
 if(($temp=="JPG")||($temp=="JPEG")||($temp=="GIF"))
   return (true); //true
 else
   return (false); //false
 }
 
 Function get_Files($m_Dir) {
 	if ($handle = opendir($m_Dir)) {	
 		while (false !== ($file = readdir($handle))) { 
     			if(!is_dir($file) && substr($file,0,1) != "."){				
 					$m_Files[]=$file;
 				}
 		}
     closedir($handle); 
 	}
 if(sizeof($m_Files)>1)
   asort($m_Files);
 return $m_Files;
 }
 
 $files=get_Files($basePath); //get files from directory
 $filter_files=array_filter($files,"check_image");
 $maxnr=sizeof($filter_files)-1;
 sort($filter_files);
 if($randomOrder){
  $order = array();
  for($i=0;$i<$maxnr+1;$i++)
   array_push($order,rand());
  array_multisort($order,$filter_files);
 }
 else{
  if($dateOrder){
   $order = array(filemtime($filter_files[0]));
   for($i=1;$i<$maxnr+1;$i++){
    array_push($order,filemtime($filter_files[$i]));
    }
  array_multisort($order,SORT_ASC,SORT_NUMERIC,$filter_files,SORT_ASC,SORT_NUMERIC);
  }
 }
//end get image files


//begin xml parser module
$filename="picdata.xml";
$current="";
$filenameData="";
$textData="";
$linkData=""; 
$xmldataArray=array();

 function startElement($parser, $name, $attribs) //opening, i.e. <COMMENT>
 {
	global $filenameData;
	global $textData;
	global $linkData;
	global $browsertitle;
	global $slidetitle;
	global $keywords;
	global $description;
    if($name=="IMAGE"){
	$textData="";
	$linkData="";
	$filenameData="";
	return;
    }
    if(($name=="BROWSERTITLE")||($name=="SLIDETITLE")||($name=="DESCRIPTION")||($name=="KEYWORDS")){	global $current;
	$$current=trim($$current);
	$current=strtolower($name);
	$$current="";
	return;
    }
    if(($name=="FILENAME")||($name=="TEXT")||($name=="LINK")){
	global $current;
	$$current=trim($$current);
	$current=strtolower($name)."Data";
	return;
    }
/*    if($name=="BR") //otherwise we get double newlines from <br/> -> <br> </br>
      return;
    $att="";
    if (sizeof($attribs)) {
       while (list($k, $v) = each($attribs)) {
           $att=$att." ".$k."=\"".$v."\"";
       }
    }
    characterData($parser, "<".$name.$att.">"); //html tag, like <a href=...>*/
 }

 function endElement($parser, $name) //closing, i.e. </COMMENT>
 {  global $filenameData;
    if(($name=="IMAGE")&&(isset($filenameData))&&($filenameData!="")){
	global $textData;
	global $linkData;
	global $current;
	global $xmldataArray;
	$xmldataArray[$filenameData]=array( //create entry in our array
			'Text'       => trim($textData),
			'Link'       => trim($linkData)
			);
	return;
    }
    if(($name=="TEXT")||($name=="LINK")||($name=="FILENAME"))
      return;
/*    if($name=="BR") //otherwise we get double newlines from <br/> -> <br> </br>
      characterData($parser, "<".$name." />");
    else
      characterData($parser, "</".$name.">"); //html tag, like <a href=...>*/
 }

 function characterData($parser, $data) //<COMMENT>plain text like this</COMMENT>
 {
    global $textData;
    global $linkData;
    global $filenameData;
    global $current;
    global $browsertitle;
    global $slidetitle;
    global $description;    
    global $keywords;
    $$current=$$current.$data;
 }

 function PIHandler($parser, $target, $data)
 {
    switch (strtolower($target)) {
        case "php":
            global $parser_file;
            printf("Untrusted PHP code: <i>%s</i>", htmlspecialchars($data));
            break;
    }
 }

 function defaultHandler($parser, $data)
 {
    return;
 }

 function externalEntityRefHandler($parser, $openEntityNames, $base, $systemId,
                                  $publicId) {
    if ($systemId) {
        if (!list($parser, $fp) = new_xml_parser($systemId)) {
            printf("Could not open entity %s at %s\n", $openEntityNames,
                    $systemId);
            return false;
        }
        while ($data = fread($fp, 4096)) {
            if (!xml_parse($parser, $data, feof($fp))) {
                printf("XML error: %s at line %d while parsing entity %s\n",
                        xml_error_string(xml_get_error_code($parser)),
                        xml_get_current_line_number($parser), $openEntityNames);
                xml_parser_free($parser);
                return false;
            }
        }
        xml_parser_free($parser);
        return true;
    }
    return false;
 }

 function new_xml_parser($file)
 {
    global $parser_file;

    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 1);
    xml_set_element_handler($xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($xml_parser, "characterData");
    xml_set_processing_instruction_handler($xml_parser, "PIHandler");
    xml_set_default_handler($xml_parser, "defaultHandler");
    xml_set_external_entity_ref_handler($xml_parser, "externalEntityRefHandler");

    if (!($fp = @fopen($file, "r"))) {
        return false;
    }
    if (!is_array($parser_file)) {
        settype($parser_file, "array");
    }
    $parser_file[$xml_parser] = $file;
    return array($xml_parser, $fp);
 }
//end xml parser module

$picTexts=array();

function browsePicMetaData(){
global $filename;
global $filter_files;
global $xmldataArray;
global $picTexts;
global $outputForm;
global $basePath;

if (!(list($xml_parser, $fp) = new_xml_parser($filename))) {
 //NO XML FILE, TRYING TO READ FROM SINGLE FILES (for pic.jpg there must be pic.txt)
 for($i=0;$i<sizeof($filter_files);$i++){
  $nt=$basePath.substr($filter_files[$i],0,strrpos($filter_files[$i],".")).".txt";
  if(file_exists($nt)){
   $fp=fopen($nt, 'rb');
   $picText=trim(fgets($fp));
   $picLink=trim(fgets($fp));
   if($outputForm==1){
    echo "<input type=\"hidden\" name=\"picTexts[".$i."]\" value=\"".$picTexts[$i]."\">\r\n";
    echo "<input type=\"hidden\" name=\"picLinks[".$i."]\" value=\"".$picLinks[$i]."\">\r\n";
    }else{
    echo "  picTexts[$i]=\"$picText\";\r\n";
    echo "  picLinks[$i]=\"$picLink\";\r\n";
    }
   }
  }
 }else
 {//READ FROM XML FILE
 while ($data = fread($fp, 4096)) {
    if (!xml_parse($xml_parser, $data, feof($fp))) {
        die(sprintf("</script></head><body>XML error: %s at line %d<br/>\r\nPlease make sure that you use the unchanged xml file generated by the listing assistant. <i>Do not copy &amp; paste the xml file from a browser as this will produce invalid xml - use the source instead.</i><br/>An indicator that this has happened is a lack of DOCTYPE information in your xml file; which should look like this: 
<pre>&lt;!DOCTYPE Pics [
	  &lt;!ELEMENT Data  (Slidetitle?,Browsertitle?,Description?,Keywords?,Image*)&gt;
	  &lt;!ELEMENT Slidetitle (#PCDATA)&gt;
	  &lt;!ELEMENT Browsertitle (#PCDATA)&gt;
	  &lt;!ELEMENT Description (#PCDATA)&gt;
	  &lt;!ELEMENT Keywords (#PCDATA)&gt;
	  &lt;!ELEMENT Image (Filename, Text?, Link?)&gt;
	  &lt;!ELEMENT Filename (#PCDATA)&gt;
	  &lt;!ELEMENT Text     (#PCDATA)&gt;
	  &lt;!ELEMENT Link     (#PCDATA)&gt;
	  ]&gt;</pre>
</body></html>",
                    xml_error_string(xml_get_error_code($xml_parser)),
                    xml_get_current_line_number($xml_parser)));
    }
   }
 xml_parser_free($xml_parser);
 for($i=0;$i<sizeof($filter_files);$i++){
   if(isset($xmldataArray[$filter_files[$i]])&&isset($xmldataArray[$filter_files[$i]]["Text"])&&($xmldataArray[$filter_files[$i]]["Text"]!="")){
     $picTexts[$i]=$xmldataArray[$filter_files[$i]]["Text"];
     if($outputForm==1)
       echo "<input type=\"hidden\" name=\"picTexts[".$i."]\" value=\"".$picTexts[$i]."\">\r\n";
      else
       echo "  picTexts[$i]=\"".$xmldataArray[$filter_files[$i]]["Text"]."\";\r\n";
     }
   if(isset($xmldataArray[$filter_files[$i]])&&isset($xmldataArray[$filter_files[$i]]["Link"])&&($xmldataArray[$filter_files[$i]]["Link"]!="")){
     $picLinks[$i]=$xmldataArray[$filter_files[$i]]["Link"];
     if($outputForm==1)
       echo "<input type=\"hidden\" name=\"picLinks[".$i."]\" value=\"".$picLinks[$i]."\">\r\n";
      else
       echo "  picLinks[$i]=\"".$xmldataArray[$filter_files[$i]]["Link"]."\";\r\n";
    }
   }
 }
}

//THE FOLLOWING SECTION NO LONGER IN USE!!!
//extended directory listing
if((sizeof($args)>0) && $args[0]=="dirdata"){
header('Content-type: text/plain');
for($i=0;$i<sizeof($filter_files);$i++)
 echo "  picNames[$i]=\"$filter_files[$i]\";\r\n";
if(!file_exists($filename)){
  echo "<b>XML data file <pre>".$filename."</pre> not found in current directory <pre>".realpath('.')."</pre></b>";
  }
else if(!is_readable($filename)){
  echo "<b>XML data file <pre>".$filename."</pre> found but permissions do not allow reading in current directory <pre>".realpath('.')."</pre></b>";
  }
else browsePicMetaData();
echo "\r\n";
echo "browsertitle=\"$browsertitle\"\r\n";
echo "slidetitle=\"$slidetitle\"\r\n";
die;
}

//THE FOLLOWING SECTION NO LONGER IN USE!!!
//call assistant for building an xml file
if((sizeof($args)>0) && (($args[0]=="oldlister"))){
if((!isset($SCRIPT_NAME))||($SCRIPT_NAME==""))
  $SCRIPT_NAME=$_SERVER["SCRIPT_NAME"];
if((!isset($SERVER_NAME))||($SERVER_NAME==""))
  $SERVER_NAME=$_SERVER["SERVER_NAME"];
$assilink="http://panmental.de/public/programming_projects/Slideshow script in PHP/assistant.php?$SERVER_NAME$SCRIPT_NAME$sep$GD_WORKAROUND";
$pathKeywords=str_replace("/"," ",realpath('.'));
echo "<html>
     <head>
     <title>Assistant for building phpshow xml files</title>
     <META NAME=\"Keywords\" CONTENT=\"".$pathKeywords." photo description assistant phpshow.panmental.de\">
     <META NAME=\"Description\" CONTENT=\"Assitant for editing xml descriptions for ".$pathKeywords." phpshow.panmental.de\">
     </head>
     <frameset cols=\"100%,*\" rows=\"*\" frameborder=0 border=0> 
     <frame src=\"$assilink\" name=\"assistant\">
     </frameset>
     <noframes>
     <body>
     <h2>".$pathKeywords."</h2>
     <h1>Assistant for building an phpshow xml file</h1>
     Your browser does not support frames, <a href=\"$assilink\">go to assistant directly</a>.
     </body>
     </noframes>
     </html>";
die;
}

if((sizeof($args)>0) && (($args[0]=="lister")||($args[0]=="assist")||($args[0]=="assistant"))){
echo "<html><head><meta http-equiv=\"expires\" content=\"0\"><title>Please wait, loading...</title></head><body onload=\"document.dataHolder.submit();\"><form  name=\"dataHolder\" action=\"http://panmental.de/public/programming_projects/Slideshow script in PHP/assist.php\" method=\"post\" target=\"_self\" >";
for($i=0;$i<sizeof($filter_files);$i++)
 echo "<input type=\"hidden\" name=\"picNames[".$i."]\" value=\"".$filter_files[$i]."\">\r\n";
$outputForm=1;
browsePicMetaData();
echo "<input type=\"hidden\" name=\"browsertitle\" value=\"$browsertitle\">\r\n";
echo "<input type=\"hidden\" name=\"slidetitle\" value=\"$slidetitle\">\r\n";
echo "<input type=\"hidden\" name=\"description\" value=\"$description\">\r\n";
echo "<input type=\"hidden\" name=\"keywords\" value=\"$keywords\">\r\n";
echo "<input type=\"hidden\" name=\"editxml\" value=\"editxml\">\r\n";
echo "<input type=\"hidden\" name=\"pathdata\" value=\"$SERVER_NAME$SCRIPT_NAME\">\r\n";
echo "<a href=\"javascript:document.dataHolder.submit();\">Click here if you are not forwarded automatically!</a></form></body></html>";
die;
}

//begin part for showing a slideshow:
if((sizeof($args)>0) && $args[0]=="slides"){
$MODE=$args[0].$sep;
array_shift($args);
$aktnr=0;
$aktnr=$args[0];
if(!(($aktnr>=0)&&($aktnr<sizeof($filter_files))))
  $aktnr=0;
//new version; one web page and images are dynamically changed
if($useFading)
  $timeOutFunc="doFade()";
else
  $timeOutFunc="gonext()";
echo "$doctype<html><head>$encoding<meta name=\"info\" content=\"Generated using PHPshow, available at phpshow.panmental.de\">
$stylesheet
";
if($useFading){ echo "<!--[if lt IE 7]>
<script language=\"JavaScript\">
function correctPNG() // correctly handle PNG transparency in Win IE 5.5 & 6.
{
   var arVersion = navigator.appVersion.split(\"MSIE\")
   var version = parseFloat(arVersion[1])
   if ((version >= 5.5) && (document.body.filters)) 
   {
      for(var i=0; i<document.images.length; i++)
      {
         var img = document.images[i]
         var imgID = (img.id) ? \"id='Z\" + img.id + \"' \" : \"\"
         if (imgID.indexOf(\"opi\")>=0)
         {
            var imgClass = (img.className) ? \"class='\" + img.className + \"' \" : \"\"
            var imgTitle = (img.title) ? \"title='\" + img.title + \"' \" : \"title='\" + img.alt + \"' \"
            var imgStyle = \"display:inline-block;\" + img.style.cssText 
            if (img.align == \"left\") imgStyle = \"float:left;\" + imgStyle
            if (img.align == \"right\") imgStyle = \"float:right;\" + imgStyle
            if (img.parentElement.href) imgStyle = \"cursor:hand;\" + imgStyle
            var strNewHTML = \"<span \" + imgID + imgClass + imgTitle
            + \" style=\\\"\" + \"width:\" + img.width + \"px; height:\" + img.height + \"px;\" + imgStyle + \";\"
            + \"filter:progid:DXImageTransform.Microsoft.AlphaImageLoader\"
            + \"(src=\\'\" + img.src + \"\\', sizingMethod='scale');\\\"></span>\" 
            img.outerHTML = strNewHTML
            i = i-1
         }
      }
";
   for($i=1;$i<=$fadeSteps;$i++)
     echo "opaq$i=document.getElementById(\"Zopi$i\");\r\n";
echo "   }    
}
window.attachEvent(\"onload\", correctPNG);
</script>
<![endif]-->
";
}//end "if($useFading)"
echo "<script type=\"text/javascript\"><!--\r\n";
if($useFading) 
 for($i=1;$i<=$fadeSteps;$i++)
  echo "var opaq$i;\r\n";
echo " 
 var counting;
 var currentPic;
 var nodi;
 var noda;
 var nodu;
 var nodj;
 allPics=new Array(".sizeof($filter_files).");
 picNames=new Array(".sizeof($filter_files).");
 picTexts=new Array(".sizeof($filter_files).");
 picLinks=new Array(".sizeof($filter_files).");
 picWidths=new Array(".sizeof($filter_files).");
 picHeights=new Array(".sizeof($filter_files).");\n";
 for($i=0;$i<sizeof($filter_files);$i++){
  $info = getimagesize("$basePath$filter_files[$i]");
  echo "  picWidths[$i]=$info[0];\r\n  picHeights[$i]=$info[1];\r\n";
 }
 for($i=0;$i<sizeof($filter_files);$i++)
  echo "  picNames[$i]=\"$filter_files[$i]\";\r\n";


browsePicMetaData();
echo "function preload(){
  waitOne = document.getElementById(\"wait1\");
  waitTwo = document.getElementById(\"wait2\");
  waitThree = document.getElementById(\"wait3\");
  ";
if($useFading)
 for($i=1;$i<=$fadeSteps;$i++)
  echo "opaq$i=document.getElementById(\"opi$i\");\r\n";
echo "currentPic=document.getElementById(\"currpici\");
 nodi = document.getElementById(\"picLoaded\");
 noda = document.getElementById(\"picInfo\");
 nodu = document.getElementById(\"picLoaded\");
 nodj = document.getElementById(\"resize\");
 ratioX=(wiwi()-$wiwiMargin)/picWidths[current];
 ratioY=(wihei()-$wiheiMargin)/picHeights[current];
 if(ratioX<ratioY){
   currentPic.height=picHeights[current]*(wiwi()-$wiwiMargin)/picWidths[current];
   currentPic.width=wiwi()-$wiwiMargin;
   }
 else{
   currentPic.width=picWidths[current]*(wihei()-$wiheiMargin)/picHeights[current];
   currentPic.height=wihei()-$wiheiMargin;
   }";
if($useFading) echo "
 fitOpi();
";
echo "if(document.images)
 {
  allPics[$aktnr] = new Image();
  allPics[$aktnr].src = \"$basePath\"+picNames[$aktnr];
";
 for($i=$aktnr+1;$i<sizeof($filter_files);$i++){
  echo "
  allPics[$i] = new Image();";
  }
 for($i=0;$i<$aktnr;$i++){
  echo "
  allPics[$i] = new Image();";
  }
echo "\r\ncheckLoad();
  }
}
function wiwi () {
if (window.innerWidth) {
return window.innerWidth;
} else if (document.body && document.body.offsetWidth) {
return document.body.offsetWidth;
} else {
return 0;
}
}
function wihei () {
if (window.innerHeight) {
return window.innerHeight;
} else if (document.body && document.body.offsetHeight) {
return document.body.offsetHeight;
} else {
return 0;
}
}";
if($useFading){ 
echo "
function fitOpi(){ 
  var tx=currentPic.offsetParent.offsetParent.offsetLeft+currentPic.offsetParent.offsetLeft+currentPic.offsetLeft;
  var ty=currentPic.offsetParent.offsetParent.offsetTop+currentPic.offsetParent.offsetTop+currentPic.offsetTop;
";
for($i=1;$i<=$fadeSteps;$i++)
  echo "
  opaq$i.style.width=currentPic.offsetWidth+3; 
  opaq$i.style.height=currentPic.offsetHeight+3;
  opaq$i.style.left=tx;
  opaq$i.style.top=ty;
  ";
echo "
}
function doOpi(opa){
";
for($i=1;$i<=$fadeSteps;$i++)
  echo "if(opa!=$i) opaq$i.style.visibility=\"hidden\";\r\n";
echo "switch(opa){\r\n";
for($i=1;$i<=$fadeSteps;$i++)
  echo "case $i: opaq$i.style.visibility=\"visible\"; break;\r\n";
echo "default: break; };
  ";
echo "
}
var fadeSteps=$fadeSteps;
var fStep=0;
var goDir=1;
function doFade(){
  self.clearTimeout(counting);
  if(!((allPics[current])&&(allPics[current].complete==true))&&(fStep>=0)){
   if(goDir==1)
    return gonext();
   else{
    goDir=1;
    return goback();
    }
   }
  fStep++;
  if(fStep==0){
    if(stopMode!='$playString')
      counting=self.setTimeout(\"$timeOutFunc\",delay);
    return doOpi(fStep);
    }
  if(fStep>=fadeSteps){
   if(goDir==1)
    return gonext();
   else{
    goDir=1;
    return goback();
    }
  }
  counting=self.setTimeout(\"$timeOutFunc\",$fadeTime);
  if(fStep<0)
    doOpi(-1*fStep);
  else
    doOpi(fStep);
}
";}//end "if $useFading"
echo "
var waitPos=0.0;
var waitOne;
var waitTwo;
var waitThree;
function checkLoad(){
stopit(false);
if((allPics[current])&&(allPics[current].complete==true)){
  nodi = document.getElementById(\"picLoaded\");
  //nodi.innerHTML=\"\";
  nodi.style.display=\"none\";
  waitPos=0.0;
  show();
  if(stopMode!='$playString')
   counting=self.setTimeout(\"$timeOutFunc\",delay);
  }
else
{
  if(allPics[current].src!=\"$basePath\"+picNames[current])
    allPics[current].src=\"$basePath\"+picNames[current];
  waitPos+=0.5; 
  if(waitPos>2.999){ waitPos=0.0;}
  var waitPosInt=Math.floor(waitPos);
  if(waitPosInt==0){
    waitOne.style.visibility=\"visible\";
    waitTwo.style.visibility=\"hidden\";
    waitThree.style.visibility=\"hidden\";
    }
  if(waitPosInt==1){
    waitOne.style.visibility=\"hidden\";
    waitTwo.style.visibility=\"visible\";
    waitThree.style.visibility=\"hidden\";
    }
  if(waitPosInt==2){
    waitOne.style.visibility=\"hidden\";
    waitTwo.style.visibility=\"hidden\";
    waitThree.style.visibility=\"visible\";
    }
  counting=self.setTimeout(\"checkLoad()\",100);
  }
}
function showPicInfo(){
if((!picTexts[current])||(picTexts[current]==\"\")){
  ttext=\"<span class='info'>\"+picNames[current]+\"</span>\";";
if($showTitle)
  echo "currentPic.title=picNames[current];";
echo "  }
else{
  ttext=\"<span class='info'>\"+picTexts[current]+\"</span>\";\r\n";
if($showTitle)
  echo "currentPic.title=picTexts[current];";
echo " }
if((picLinks[current])&&(picLinks[current]!=\"\"))
  currentPic.style.cursor=\"pointer\";
else
  currentPic.style.cursor=\"default\";//  firstChild.nodeValue
noda.innerHTML=ttext";
if($showPicSize)
 echo "+\" (\"+picWidths[current]+\"x\"+picHeights[current]+\")\"";
if($showPicNum)
 echo "+\" #\"+(current+1)+\"/".sizeof($filter_files)."\"";
echo ";
if(allPics[current].complete==false){
  currentPic.width =1;
  currentPic.height=1;
  self.clearTimeout(counting);
";
if($useFading){
 for($i=1;$i<=$fadeSteps;$i++)
   echo "  opaq$i.width =1;\r\n  opaq$i.height =1;\r\n";
  echo "fStep=-1;
        doOpi(fStep);
     ";
  }
echo "
  //nodu.innerHTML=\"<span id='wait1' style='visibility:visible'>&nbsp;.&nbsp;</span><span id='wait2' style='visibility:hidden'>.&nbsp;</span><span id='wait3' style='visibility:hidden'>.&nbsp;</span><br/>$notLoadedString<br/><br/>\";
  nodu.style.display=\"block\";
  waitOne = document.getElementById(\"wait1\");
  waitTwo = document.getElementById(\"wait2\");
  waitThree = document.getElementById(\"wait3\");
  counting=self.setTimeout(\"checkLoad()\",100);
  }
else{
  for(ci=1;ci<=$preloadForward;ci++){
    if(allPics[(current+ci)%".sizeof($filter_files)."].src!=\"$basePath\"+picNames[(current+ci)%".sizeof($filter_files)."])
      allPics[(current+ci)%".sizeof($filter_files)."].src=\"$basePath\"+picNames[(current+ci)%".sizeof($filter_files)."];
    var cn=(".sizeof($filter_files)."+(current-ci))%".sizeof($filter_files).";
    if(allPics[cn].src!=\"$basePath\"+picNames[cn])
      allPics[cn].src=\"$basePath\"+picNames[cn];
  }
  //nodu.innerHTML=\"\";
  nodu.style.display=\"none\";
";
if($useFading)
  echo "fitOpi();
     ";
echo "  }
}
function show(){
if(allPics[current].complete==true){
";
echo " if(fitScr==1){
  ratioX=(wiwi()-$wiwiMargin)/picWidths[current];
  ratioY=(wihei()-$wiheiMargin)/picHeights[current];
  if(ratioX<ratioY){
    currentPic.height=picHeights[current]*(wiwi()-$wiwiMargin)/picWidths[current];
    currentPic.width=wiwi()-$wiwiMargin;
    }
  else{
    currentPic.width=picWidths[current]*(wihei()-$wiheiMargin)/picHeights[current];
    currentPic.height=wihei()-$wiheiMargin;
    }
 }
 else
 {
  currentPic.width =picWidths[current];
  currentPic.height=picHeights[current];
 }
}
currentPic.src=allPics[current].src;
showPicInfo();
}
function resizeIt(){
if(fitScr==0){
  fitScr=1;
  nodj.value=\"$actualString\";
  }
else{
  fitScr=0;
  nodj.value=\"$fitString\";
}
show();
}
function selected(){
if(stopMode=='$playString')
  stopit(true);
else
  stopit(false);
selA=document.getElementById(\"sel\");
";
if($useFading)
  echo "fStep=0;doOpi(0);delay=selA.options[selA.selectedIndex].value-(fadeSteps*$fadeTime);
  ";
else
  echo "delay=selA.options[selA.selectedIndex].value;
  ";
echo "counting=self.setTimeout(\"$timeOutFunc\",delay);
}
var stopMode='";
if($autoplay)
  echo $pauseString."';\r\n";
else
  echo $playString."';\r\n";
if($stopOnMouseOver){
  echo "\r\nvar mouseEntered=false;
  function stopTemp(){
    if((!mouseEntered)&&(stopMode=='$pauseString')){
     self.clearTimeout(counting);";
  if($useFading) echo "\r\n fStep=0; doOpi(0);\r\n";
  echo "     mouseEntered=true;
     }
    }
  function endStopTemp(){
    if(mouseEntered){
     mouseEntered=false;
     counting=self.setTimeout(\"$timeOutFunc\",500);
     }
    }\r\n";
}
echo "
function stopit(mode){
";
echo "if(mode){
  if(stopMode=='$playString'){
    stopMode='$pauseString';
    counting=self.setTimeout(\"$timeOutFunc\",1);//was delay instead of 1
    }
  else{
    stopMode='$playString';
    self.clearTimeout(counting);
";
if($useFading) echo "    fStep=0;doOpi(0);
    ";
echo "    }
  document.getElementById(\"pause\").value=stopMode;
  }
else{
";
echo "  self.clearTimeout(counting);}
}
function browseIt(){
self.location.href=\"$SCRNAME?$GD_WORKAROUND$sep\"+current;
}
function gonext() {
";
echo "stopit(false);
//currentPic.complete=false;
if(current==".sizeof($filter_files)."-1)
  current=0;
else
  current++;
if(current+1==".sizeof($filter_files)."-1)
  nextone=0;
else
  nextone=current+1;
show();
";
if($useFading) echo "fStep=(-1*fadeSteps)+1;
                     doOpi(-1*fStep);
      ";
if($useFading) echo "  counting=self.setTimeout(\"$timeOutFunc\",$fadeTime);
       ";
else 
  echo "if(stopMode!='$playString')\r\n  counting=self.setTimeout(\"$timeOutFunc\",delay);
       ";
echo "}
function goback() {
";
if($useFading) echo "fStep=-1*fadeSteps;
      ";
echo "stopit(false);
//currentPic.complete=false;
if(current==0)
  current=".sizeof($filter_files)."-1;
else
  current--;
if(current+1==".sizeof($filter_files)."-1)
  nextone=0;
else
  nextone=current+1;
show();
";
if($useFading) echo "  counting=self.setTimeout(\"$timeOutFunc\",$fadeTime);
       ";
else 
  echo "if(stopMode!='$playString')\r\n  counting=self.setTimeout(\"$timeOutFunc\",delay);
       ";
echo "}
window.onresize = show;
var current=$aktnr;
var nextone;
if(current+1==".sizeof($filter_files)."-1)
  nextone=0;
else
  nextone=current+1;
fitScr=";
if($startFit)
  echo "1;\r\n";
else
  echo "0;\r\n";
if($useFading) 
  echo "delay=($initialDelay*1000)-(fadeSteps*$fadeTime); //counting=self.setTimeout(\"$timeOutFunc\",delay);";
else 
  echo "delay=($initialDelay*1000); //counting=self.setTimeout(\"$timeOutFunc\",delay);";
echo "
function checkGoto(){
  if((picLinks[current])&&(picLinks[current]!=\"\"))
    window.open(picLinks[current],\"$openLinksIn\",\"$openLinkOpts\");
  }

function keyControl(evnt){
  if (!evnt)
    evnt = window.event;
  if (evnt.which) {
    evntkeycode = evnt.which;
  } else if (evnt.keyCode) {
    evntkeycode = evnt.keyCode;
  }
  if(evntkeycode==37)
";
if($useFading) 
  echo "    goDir=0;$timeOutFunc;";
else
  echo "    goback();";
echo "
  if(evntkeycode==39)
    $timeOutFunc;
}
document.onkeyup = keyControl;
//-->
</script>
<meta name=\"description\" content=\"$description\">
<meta name=\"keywords\" content=\"$keywords\">
<title>$slidetitle</title>
</head>
<body onload=\"preload()\" style=\"background-color:$bgcolor\">
<div align=\"center\" class=\"controlTable\">
<table style=\"vertical-align:middle; margin-bottom:2px;\" align=\"center\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\">
<tr><td>
<input class=\"slidebrowse\" type=\"button\" value=\"$browseString\" name=\"browse\" onClick=\"browseIt();\">
</td><td>";
if($startFit)
  echo "<input class=\"sizebutton\" id=\"resize\" type=\"button\" value=\"$actualString\" name=\"fit\" onClick=\"resizeIt();\">";
else
  echo "<input class=\"sizebutton\" id=\"resize\" type=\"button\" value=\"$fitString\" name=\"fit\" onClick=\"resizeIt();\">";
echo "</td><td><input id=\"pause\" class=\"playpause\" type=\"button\" value=\"";
if($autoplay)
  echo $pauseString;
else
  echo $playString;
echo "\" name=\"stop\" onClick=\"stopit(true);\">
</td><td><select id=\"sel\" size=\"1\" name=\"del\" onChange=\"selected();\">";
echo "<option value=\"".intval(($initialDelay/2.0)*1000)."\">".($initialDelay/2.0)." $secString</option>";
echo "<option selected value=\"".intval($initialDelay*1000)."\">$initialDelay $secString $delayString</option>";
echo "<option value=\"".intval(($initialDelay/2.0)*3000)."\">".($initialDelay/2.0*3.0)." $secString</option>";
echo "<option value=\"".intval(($initialDelay/2.0)*4000)."\">".($initialDelay/2.0*4.0)." $secString</option>";
echo "<option value=\"".intval(($initialDelay/2.0)*6000)."\">".($initialDelay/2.0*6.0)." $secString</option>";
echo "</select></td></tr><tr>
<td colspan=\"4\" style=\"text-align:center\">
<table width=\"100%\"><tr><td style=\"text-align:center;\" width=\"50%\">
<a class=\"goLink\" href=\"javascript:";
if($useFading) 
  echo "goDir=0;$timeOutFunc;";
else
  echo "goback();";
echo "\">&lt;$backString</a></td><td style=\"text-align:center;\" width=\"50%\"><a class=\"goLink\" href=\"javascript:$timeOutFunc;\">$nextString&gt;</a>
</td></tr></table>
</td></tr>
</table></div>
<div align=\"center\" id=\"picInfo\">";
 $info = getimagesize("$basePath$filter_files[$aktnr]");
 echo $filter_files[$aktnr]."&nbsp;(".$info[0]."x".$info[1].")&nbsp;#".($aktnr+1)."/".sizeof($filter_files);
echo "</div>
<table align=\"center\"><tr><td class=\"currpic\" onclick=\"checkGoto();\" style=\"z-index:10; width:1px; height: 1px;\">
<img ";
if($stopOnMouseOver)
  echo " onmouseover=\"stopTemp()\" onmouseout=\"endStopTemp()\" ";
echo " id=\"currpici\" border=\"0\" style=\"z-index:0;\" src=\"$basePath$filter_files[$aktnr]\" alt=\"\" title=\"";
if($showTitle)
  echo $filter_files[$aktnr];
echo "\" width=\"1\" height=\"1\">
</td></tr></table>
";
if($useFading){
 for($i=1;$i<=$fadeSteps;$i++){
  echo "<img "; 
  if($stopOnMouseOver)
    echo " onmouseover=\"stopTemp()\" ";
  echo "alt=\"$i\" class=\"opaquepic\" style=\"position:absolute; visibility:hidden; z-index:1;\" id=\"opi$i\" src=\"$SCRNAME?opaque$sep".round((127.0/$fadeSteps)*($fadeSteps-$i))."$sep"."$i"."$sep"."$fadeMode\"/>
      ";
  }
 }
echo "<div align=\"center\" style=\"text-align:center; width:100%; z-index:99;\" id=\"picLoaded\"><span id='wait1' style='visibility:hidden'>&nbsp;.&nbsp;</span><span id='wait2' style='visibility:hidden'>.&nbsp;</span><span id='wait3' style='visibility:hidden'>.&nbsp;</span><br/>$notLoadedString<br/><br/></div>
$aftertext
</body></html>";
die;
 }
//end part for showing a slideshow      



//begin part for giving out zip archive of all photos:

//begin zip routine class based on and mostly equal to code found here:
//http://www.zend.com/zend/spotlight/creating-zip-files1.php
//some zip file format specification used from:
//http://www.pkware.com/business_and_developers/developer/popups/appnote.txt

class zipfile
{
  var $ctrl_dir = array();
  var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
  var $old_offset = 0;
  var $new_offset = 0;

function add_dir($name)
    {
        $name = str_replace("\\", "/", $name);

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x0a\x00";
        $fr .= "\x00\x00";
        $fr .= "\x00\x00";
        $fr .= "\x00\x00\x00\x00";

        $fr .= pack("V",0);
        $fr .= pack("V",0);
        $fr .= pack("V",0);
        $fr .= pack("v", strlen($name) );
        $fr .= pack("v", 0 );
        $fr .= $name;
        $fr .= pack("V", 0);
        $fr .= pack("V", 0);
        $fr .= pack("V", 0);

        $this->new_offset += strlen($fr);
        echo $fr;

     $cdrec = "\x50\x4b\x01\x02";
     $cdrec .="\x00\x00";
     $cdrec .="\x0a\x00";
     $cdrec .="\x00\x00";
     $cdrec .="\x00\x00";
     $cdrec .="\x00\x00\x00\x00";
     $cdrec .= pack("V",0);
     $cdrec .= pack("V",0);
     $cdrec .= pack("V",0);
     $cdrec .= pack("v", strlen($name) );
     $cdrec .= pack("v", 0 );
     $cdrec .= pack("v", 0 );
     $cdrec .= pack("v", 0 );
     $cdrec .= pack("v", 0 );
     $ext = "\x00\x00\x10\x00";
     $ext = "\xff\xff\xff\xff";
     $cdrec .= pack("V", 16 );
     $cdrec .= pack("V", $this -> old_offset );
     $cdrec .= $name;

     $this -> ctrl_dir[] = $cdrec;
     $this -> old_offset = $this->new_offset;
     return;
}


function add_file($data, $name, $timedate) {
   $name = str_replace("\\", "/", $name);
   $unc_len = strlen($data);
   $crc = crc32($data);
   $zdata = $data;
   $c_len = strlen($zdata);

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";
        $fr .= "\x00\x00";
        $fr .= "\x00\x00";//no compression
        $fr .= $timedate;//date stamp
        $fr .= pack("V",$crc);
        $fr .= pack("V",$c_len);
        $fr .= pack("V",$unc_len);
        $fr .= pack("v", strlen($name) );
        $fr .= pack("v", 0 );
        $fr .= $name;
        $fr .= $zdata;
        $fr .= pack("V",$crc);
        $fr .= pack("V",$c_len);
        $fr .= pack("V",$unc_len);

  $this->new_offset += strlen($fr);
  echo $fr;

  $cdrec = "\x50\x4b\x01\x02";
  $cdrec .="\x00\x00";
  $cdrec .="\x14\x00";
  $cdrec .="\x00\x00";
  $cdrec .="\x00\x00";//no compression
  $cdrec .= $timedate;//date stamp
  $cdrec .= pack("V",$crc);
  $cdrec .= pack("V",$c_len);
  $cdrec .= pack("V",$unc_len);
  $cdrec .= pack("v", strlen($name) );
  $cdrec .= pack("v", 0 );
  $cdrec .= pack("v", 0 );
  $cdrec .= pack("v", 0 );
  $cdrec .= pack("v", 0 );
  $cdrec .= pack("V", 32 );
  $cdrec .= pack("V", $this -> old_offset );

  $this -> old_offset = $this -> new_offset;

  $cdrec .= $name;
  $this -> ctrl_dir[] = $cdrec;
}

function file() {
        $ctrldir = implode("", $this -> ctrl_dir);

        return
            $ctrldir.
            $this -> eof_ctrl_dir.
            pack("v", sizeof($this -> ctrl_dir)).
            pack("v", sizeof($this -> ctrl_dir)).
            pack("V", strlen($ctrldir)).
            pack("V", $this->new_offset).
            "\x00\x00";
    }
}//end zip routine class

function dosti($y, $n, $d, $h, $m, $s){
  return (int)((int)(((int)$y - 1980) << 25) | (int)((int)$n << 21) | 
    (int)((int)$d << 16) | (int)((int)$h << 11) | 
    (int)((int)$m << 5) | (int)((int)$s >> 1));
}




if($QUERY_STRING!="" && sizeof($args)>=1 && $args[0]=="zipfile" && $allowZip!=false){
if(strlen($basePath)>2)
  $dirnm=substr($basePath,0,strlen($basePath)-1);//full path but without final slash
else
  $dirnm=dirname($_SERVER["SCRIPT_NAME"]);
$dirnm=substr($dirnm,strrpos($dirnm,"/"));
$dirnm=str_replace(array("\\","/"),"",$dirnm);
header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=\"$dirnm.zip\"");
$dirnm.="/";
$zippi=new zipfile();
$zippi->add_dir($dirnm);
for($i=0;$i<sizeof($filter_files);$i++){
  $imgData=file_get_contents($basePath.$filter_files[$i]);
  $timestamp=filemtime($basePath.$filter_files[$i]);
  $datetime=pack("V",dosti(  date("Y",$timestamp),
                             date("n",$timestamp),
                             date("j",$timestamp),
                             date("G",$timestamp),
                             date("i",$timestamp),
                 (int)((int)(date("s",$timestamp)/2)*2) ));
  $zippi->add_file($imgData,$dirnm.$filter_files[$i],$datetime);
  }
echo $zippi->file();
die();
}
//end part for giving out zip archive of all photos:




//begin part for browsing through images:
function photosSize(){
global $filter_files;
global $basePath;
$fSum=0;
foreach($filter_files as $fn){
  $fSum+=filesize($basePath.$fn);
  }
return sprintf("%01.2f MB",(((int)((int)$fSum/1024))/1024));
}

if($QUERY_STRING!="" && sizeof($args)>=1 && is_numeric($args[0]))
  $offset=((int)(intval($args[0])/$maxperpage))*$maxperpage;
else
  $offset=0;

if($offset<0)
  $offset=0;
if($offset>=sizeof($filter_files))
  $offset=sizeof($filter_files)-(sizeof($filter_files) % $maxperpage);

echo "$doctype<html><head>$encoding<meta name=\"info\" content=\"Generated using PHPshow, available at phpshow.panmental.de\">
$stylesheet
";
   echo "<script type=\"text/javascript\">";
   echo "<!--\r\n";
   echo "function slides(){\r\n";
   echo "self.location.href=\"$SCRNAME?$GD_WORKAROUND".$sep."slides$sep".$offset."\";\r\n";
   echo "\r\n}
 picTexts=new Array(".sizeof($filter_files).");
 picLinks=new Array(".sizeof($filter_files).");\r\n";
browsePicMetaData();
   echo "
var dontHide=false;
var lastNum=-1;
function showData(num,caller) {
  infotxt=document.getElementById(\"infotext\");
  if((picTexts[num])&&(picTexts[num]!=\"\")){
   dontHide=true;
   if(lastNum!=num){
    lastNum=num;
    infotxt.style.visibility=\"visible\";
    x=caller.offsetParent;
    tmpLeft=0;
    while(x){
      tmpLeft+=x.offsetLeft;
      x=x.offsetParent;
      }
    infotxt.style.left=tmpLeft;
    y=caller;
    tmpTop=0;
    while(y){
      tmpTop+=y.offsetTop;
      y=y.offsetParent;
      }
    infotxt.style.top=tmpTop+caller.offsetHeight+4;
    infotxt.style.width=caller.offsetWidth-4;
    if((picLinks[num])&&(picLinks[num]!=\"\")){
      infotxt.innerHTML=\"<a target='$openLinksIn' href='\"+picLinks[num]+\"'><span class='info'>\"+picTexts[num]+\"</span></a>\";
      }
    else
      infotxt.innerHTML=\"<span class='info'>\"+picTexts[num]+\"</span>\";
    }
  }
  else{
   if(infotxt)
    infotxt.style.visibility=\"hidden\";
    lastNum=-1;
  }
 return false;
}
function doHide(){
  dontHide=false;
  hideData();
}
function hideData(){
 if(!dontHide){
  infotxt=document.getElementById(\"infotext\");
  infotxt.style.visibility=\"hidden\";
  lastNum=-1;
 }
}
window.onresize = doHide;
  ";
if($allowZip){
  $downloadString=str_replace(array("#SIZE#"),photosSize(),$downloadString);
  $downloadString=str_replace(array("#NUM#"),sizeof($filter_files),$downloadString);
  echo "function downloadExec(){
        res=confirm(\"$downloadString\");
        if(res)
          self.location.href=\"$SCRNAME?zipfile\";\r\n}\r\n";
   }

$priorPageLink="$SCRNAME?$GD_WORKAROUND$sep".strval($offset-$maxperpage);
$nextPageLink="$SCRNAME?$GD_WORKAROUND$sep".strval($offset+$maxperpage);
echo "
function keyControl(evnt){
  if (!evnt)
    evnt = window.event;
  if (evnt.which) {
    evntkeycode = evnt.which;
  } else if (evnt.keyCode) {
    evntkeycode = evnt.keyCode;
  }
";
if($offset>0) 
 echo "  if(evntkeycode==37)
    location.href=\"$priorPageLink\";
";
if($offset+$maxperpage<sizeof($filter_files))
 echo "  if(evntkeycode==39)
    location.href=\"$nextPageLink\";
";
echo "}
document.onkeyup = keyControl;
//-->\n </script>\r\n";
echo "<meta name=\"description\" content=\"$description\">
<meta name=\"keywords\" content=\"$keywords\">
<title>$browsertitle</title>";
echo "</head>";
echo "<body style=\"background-color:$bgcolor\" onmouseover=\"hideData()\">";//<span class=\"browser\">
echo "<div align=\"center\" class=\"controlTable\">";
//echo "<form name=\"ctrl\" action=\"\">";
echo "<table onmouseover=\"hideData()\" style=\"vertical-align:middle;\" align=\"center\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\"><tr>";
echo "<td><input class=\"slidebrowse\" type=\"button\" value=\"$slidesString\" name=\"B1\" onClick=\"slides();\"/></td>";
if($allowZip)
  echo "<td><input class=\"downloadLink\" type=\"button\" value=\"$downloadButton\" onClick=\"downloadExec();\"/></td>";

echo "</tr></table>";
echo "<table onmouseover=\"hideData()\" style=\"vertical-align:middle;\" align=\"center\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\"><tr>";
echo "<td><a class=\"goLink\" ";
if($offset<=0)
  echo "style=\"color:#aaa; text-decoration:none;\" ";
else
  echo "href=\"$priorPageLink\"";
echo " >&lt;$priorPageString</a>&nbsp;</td>";

$startxi=$offset%$maxperpage;
$countxi=1;
if($startxi!=0){
  echo "<td><a class=\"goLink\" ";
  echo "href=\"$SCRNAME?$GD_WORKAROUND$sep"."0\"";
  echo " >$countxi</td>";
  $countxi++;
}
for($xi=$startxi;$xi<sizeof($filter_files);$xi+=$maxperpage){
 //if($xi%sizeof($filter_files)==$startxi)
  echo "<td><a class=\"goLink\" ";
  if($xi==$offset)
    echo "style=\"color:#a00; font-weight:bold; text-decoration:none;\" ";
  else
    echo "href=\"$SCRNAME?$GD_WORKAROUND$sep".strval($xi)."\"";
  echo " >$countxi</td>";
  $countxi++;
}

echo "<td>&nbsp;<a class=\"goLink\" ";
if($offset+$maxperpage>=sizeof($filter_files))
   echo "style=\"color:#aaa; text-decoration:none;\" ";
else
  echo "href=\"$nextPageLink\"";
echo " >$nextPageString&gt;</a></td>";
echo "</tr></table></div>";//</tr></table></form></div>
echo "<div align=\"center\"><center>\n";
echo "<table onmouseover=\"hideData()\" width=\"100%\"; border=\"0\" cellpadding=\"0\" cellspacing=\"4\" style=\"table-layout:fixed; height:100;\">\r\n";//
$newline=false;
$firstTime=true;
for($i=$offset;($i<sizeof($filter_files)&&$i<$offset+$maxperpage);$i++){
 if((array_key_exists($i,$picTexts))&&($picTexts[$i])&&(trim($picTexts[$i])!=""))
  $textStyle="border-bottom-style:dashed;"; 
 else
  $textStyle="";
 if(($i-$offset) % $imgsperline==0) //$scrwidth - ($realimgwidth*i % $scrwidth) - $imgwidth<0)
   $newline=true;
 if($newline==true){
   if(!$firstTime){
     echo "</tr>";
     $firstTime=false;
     }
   echo "<tr onmouseover=\"hideData()\" style=\"text-align:center; vertical-align:middle\">";
   $newline=false;
   }
 if($GD_WORKAROUND=="oldGD"){
   $winfo=getimagesize("$filter_files[$i]");
   $wwidth=$imgwidth;
   $wheight=$winfo[1]*($imgwidth/$winfo[0]);
   //workaround for old PHP version (GD < 1.8), no jpeg abilities 
   echo "<td class=\"browsetd\" width=\"".strval(100/$imgsperline)."%\"><div style=\"$textStyle\" class=\"tdpic\"><a href=\"$SCRNAME?$GD_WORKAROUND$sep"."slides$sep".$i."\"><img border=\"0\" alt=\"".$filter_files[$i]."\" src=\"".$filter_files[$i]."\" onmouseover=\"showData($i,this)\" width=\"100%\"></a></div></td>";//was: width=\"$wwidth\" height=\"$wheight\"
 }
 else
   echo "<td class=\"browsetd\" width=\"".strval(100/$imgsperline)."%\"><div style=\"$textStyle\" class=\"tdpic\"><a href=\"$SCRNAME?$GD_WORKAROUND$sep"."slides$sep".$i."\"><img class=\"tdpic\" border=\"0\" alt=\"".$filter_files[$i]."\"  src=\"".$SCRNAME."?$GD_WORKAROUND$sep"."image".$sep.$filter_files[$i].$sep.$imgwidth."\" onmouseover=\"showData($i,this)\" width=\"100%\" ></a></div></td>";
}
$fillup=$imgsperline-(sizeof($filter_files)-$offset);
for($i=$fillup;$i>0;$i--)
  echo "<td class=\"browsetd\" width=\"".strval(100/$imgsperline)."%\"></td>";
echo "</tr>\n</table></center></div>
<table><tr><td onmouseover=\"dontHide=true\" onmouseout=\"dontHide=false\"  class=\"currpic\" style=\"border-top-style:none; z-index:99; visibility:hidden; position:absolute\" id=\"infotext\">
</td></tr></table>";


echo "$aftertext</body>";
echo "</html>";
die;
//end part for browsing through images
?>