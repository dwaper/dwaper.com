<?php

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches 4 of your friends.
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

  // And this returns 16 of your photos.
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Person">

<head>
  <title><?php echo he($app_name); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
  <meta name="name" content="Dwaper" />
  <meta name="description" content="" />
  <meta name="keywords" content="" />
  <meta name="robots" content="NOODP,NOYDIR" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />
  <!-- <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" /> -->
  <!-- <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" /> -->
  <link rel="stylesheet" href="stylesheets/bootstrap.min.css" />
  <link rel="stylesheet" href="stylesheets/misc.css" />
  <link rel="stylesheet" href="stylesheets/bootstrap-responsive.min.css" />

  <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->

  <meta property="og:title" content="<?php echo he($app_name); ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
  <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
  <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
  <meta property="og:description" content="My first app" />
  <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

  <link rel="icon" type="image/png" href="img/favicon.png" />
  <link rel="shortcut icon" type="image/png" href="img/favicon.png" />
  <script type="text/javascript" src="javascript/jquery-1.8.0.js"></script>
  <script type="text/javascript" src="javascript/script.js"></script>
  <script type="text/javascript" src="javascript/bootstrap.min.js"></script>

  <script type="text/javascript">
    function logResponse(response) {
      if (console && console.log) {
        console.log('The response was', response);
      }
    }

    $(function(){
      // Set up so we handle click on the buttons
      $('#postToWall').click(function() {
        FB.ui(
          {
            method : 'feed',
            link   : $(this).attr('data-url')
          },
          function (response) {
            // If response is null the user canceled the dialog
            if (response != null) {
              logResponse(response);
            }
          }
        );
      });

      $('#sendToFriends').click(function() {
        FB.ui(
          {
            method : 'send',
            link   : $(this).attr('data-url')
          },
          function (response) {
            // If response is null the user canceled the dialog
            if (response != null) {
              logResponse(response);
            }
          }
        );
      });

      $('#sendRequest').click(function() {
        FB.ui(
          {
            method  : 'apprequests',
            message : $(this).attr('data-message')
          },
          function (response) {
            // If response is null the user canceled the dialog
            if (response != null) {
              logResponse(response);
            }
          }
        );
      });
    });
  </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
</head>

<body>
  <div id="fb-root"></div>
  <script type="text/javascript">
    window.fbAsyncInit = function() {
      FB.init({
        appId      : '<?php echo AppInfo::appID(); ?>', // App ID
        channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        xfbml      : true // parse XFBML
      });

      // Listen to the auth.login which will be called when the user logs in
      // using the Login button
      FB.Event.subscribe('auth.login', function(response) {
        // We want to reload the page now so PHP can read the cookie that the
        // Javascript SDK sat. But we don't want to use
        // window.location.reload() because if this is in a canvas there was a
        // post made to this page and a reload will trigger a message to the
        // user asking if they want to send data again.
        window.location = window.location;
      });

      FB.Canvas.setAutoGrow();
    };

    // Load the SDK Asynchronously
    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/all.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  </script>

  <div class="navbar">
    <div class="navbar-inner">
      <div class="container">
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </a>

        <a class="brand" onclick="showAbout()"><span class="cool-red id">d</span><span>waper</span></a>

        <div id="navmenu" class="nav-collapse">
          <ul class="nav">
            <li class="active about"><a onclick="showAbout()" data-toggle="collapse" data-target=".nav-collapse">Home</a></li>
            <li><a onclick="showContact()" target="_blank" data-toggle="collapse" data-target=".nav-collapse">Notifications</a></li>
            <li class="contact"><a onclick="showDeveloper()" data-toggle="collapse" data-target=".nav-collapse">Explore</a></li>
          </ul>

          <div class='pull-right'>
            <?php if (isset($basic)) { ?>
              <p id="picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=square)"></p>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="about" class="row-fluid">
    <div class="span1" style="min-height: 0px; height: 0px;">&nbsp;</div>
    <div class="span6">

    </div>

    <div class="span5">
    </div>
  </div>

  <div id="contact" class="row-fluid">
    <div class="span1" style="min-height: 0px; height: 0px;">&nbsp;</div>
    <div class="span6">
    </div>

    <div class="span5">
    </div>
  </div>

  <div id="developer" class="row-fluid">
    <div class="span1" style="min-height: 0px; height: 0px;">&nbsp;</div>
    <div class="span6">
    </div>

    <div class="span5">
    </div>
  </div>

  <div class="navbar navbar-fixed-bottom footer">
    <div class="lnsep"></div>

    <div class="row-fluid">
      <div class="span12">This page is <a href="https://github.com/djGrill/djgrill.com/" target="_blank">Open Source</a> - Last updated Sep 09, 2012</div>
    </div>

    <div class="row-fluid">
      <div class="span12 social">
        <a href="http://twitter.com/djGrill/" target="_blank"><span class="twitter"></span></a>
        <a href="https://github.com/djGrill/" target="_blank"><span class="github"></span></a>
        <a href="http://linkedin.com/in/djGrill/" target="_blank"><span class="linkedin"></span></a>
      </div>
    </div>
  </div>
</body>
</html>
