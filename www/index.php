<?php
// get the path to the current directory
$path = dirname(__FILE__);
 
// get the url for the images
$path_info = pathinfo($_SERVER['SCRIPT_NAME']);
$url = $path_info['dirname'];

$pidfile = "pidfile.txt";
$outputfile = "output.txt";

// create an array to store image names in.
$images = array(); 
$dir = new DirectoryIterator($path);
foreach( $dir as $entry ){
   if( $entry->isFile() ){
      if( preg_match('#^(.+?)(_thumb)?\.(jpg|gif|png)#i', $entry->getFilename(), $matches) ){
         list( ,$name, $is_a_thumb, $extension) = $matches;
         // if its not a thumbnail
         if( !$is_a_thumb ){
            $images[$entry->getFilename()] = $name;
         }
      }
   }
}

// sort the images array so always in the same order
ksort($images);

$action_message = "";
if (isset($_POST["stop"])) {
   $pid = file_get_contents($pidfile);
   $action_message = "stopping process $pid\n<br /><br />";
   $action_message .= exec("sudo kill $pid");
}
if (isset($_POST["start"])) {
   $action_message = "starting system\n<br /><br />";
   $action_message .= exec("sudo /home/pi/run_pir_pics.sh");
}
if (isset($_POST["shutdown"])) {
   $action_message = "shutting system down\n<br /><br />";
   $action_message .= exec("sudo shutdown -h now &");
}
if (isset($_POST["clear_images"])) {
   $action_message = "cleared all images\n<br /><br />";
   $action_message .= exec("sudo rm /var/www/images/*.jpg");
   sleep(2);
   header('Location: '.$_SERVER['REQUEST_URI']);
}

function isRunning($pid){
   try{
      $result = exec(sprintf("sudo ps %d", $pid));
      if(count(preg_split("/\n/", $result)) > 0 && $result != "  PID TTY      STAT   TIME COMMAND"){
         return true;
      }
   } catch(Exception $e) { 

   }
   return false;
}

if (file_exists($pidfile)) {
   $action_message .= "There is a pid file";
   $pid = file_get_contents($pidfile);
   $is_running = isRunning($pid);
   if ($is_running){
      $action_message .= "Image Capture is running, pid: $pid<br /><br />";
   } else {
      $action_message .= "Image Capture process has ended<br /><br />";
   }
} else {
  $action_message .= "No pir pic system currently running";
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
   <head>
      <title>CatWatch Central Display Board</title>
      <style type="text/css">
<!--
#thumbs{
   position: relative;
}
 
#thumbs div{
   float: left;
   width: 330px;
   height: 330px;
   text-align: center;
}
 
#thumbs a:link img, #thumbs a:visited img{
   border: 1px solid #acacac;
   padding: 5px;
}
 
#thumbs a:hover img{
   border: 1px solid black;
}
-->
      </style>
   </head>
   <body>
      <div id="controls">
         <div style="float:left">
         CAT CATCHER CONTROLS: 
	 </div>
         <form action= <?php echo $_SERVER['PHP_SELF'] ?> method="get" style="float:left">
	    <input type="submit" value="REFRESH">
	 </form>
         <form action= <?php echo $_SERVER['PHP_SELF'] ?> method="post" style="float:left">
	    <input type="hidden" name="start" id="start" value="start">
	    <input type="submit" value="START">
	 </form>
         <form action= <?php echo $_SERVER['PHP_SELF'] ?> method="post" style="float:left">
	    <input type="hidden" name="stop" id="stop" value="stop">
	    <input type="submit" value="STOP">
	 </form>
         <form action= <?php echo $_SERVER['PHP_SELF'] ?> method="post" style="float:left">
	    <input type="hidden" name="shutdown" id="shutdown" value="shutdown">
	    <input type="submit" value="SHUTDOWN">
	 </form>
         <form action= <?php echo $_SERVER['PHP_SELF'] ?> method="post" style="float:left">
	    <input type="hidden" name="clear_images" id="clear_images" value="clear_images">
	    <input type="submit" value="CLEAR IMAGES">
	 </form>
      </div>
      <div style="clear:left">
         <?php echo $action_message ?>
      </div>
      <br />
      <hr />
      <div id="thumbs">
         <?php foreach( $images as $imagename => $name ){ ?>
            <div>
               <a href="<?php echo $url . '/' . $imagename?>" title="Full Size"><img src="<?php echo $url . '/' . $name . '_thumb.jpg'?>"  /></a>
	       <?php 
	         $image_name = substr($imagename,10, -4);
		 $image_name = str_replace("_", " ", $image_name);
	         echo $image_name;
	       ?>
            </div>
         <?php }?>
      </div>
   </body>
</html>
