<?php
  require_once('AppInfo.php')
?>

<html>
  <head>
    <meta prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# dwaper-php-dev: http://ogp.me/ns/fb/dwaper-php-dev#">
    <meta property="fb:app_id" content="411022408959008" /> 
    <meta property="og:type"   content="dwaper-php-dev:game" /> 
    <meta property="og:url"    content="<?php echo AppInfo::getUrl(); ?>frisbee.php" /> 
    <meta property="og:title"  content="Frisbee" /> 
    <meta property="og:image"  content="<?php echo AppInfo::getUrl(); ?>images/icon_frisbee.png" />  
    <title>Frisbee</title>
  </head>  

  <body>
    <p> Some information to display about the frisbee</p>
  </body>
</html>
