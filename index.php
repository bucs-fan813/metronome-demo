<html>
  <head>
    <title>MetronomeUSA Demo</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css">
	<link rel="shortcut icon" href="https://wearemetronome.com/images/phocafavicon/email%20logo.jpg" type="image/x-icon">
	
    <style>
video {
  object-fit: cover;
  width: 100vw;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
}

html, body {
  height: 100%;
}

html {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  font-size: 150%;
  line-height: 1.4;
}

body {
  margin: 0;
}

.viewport-header {
  position: relative;
  height: 50vh;
  text-align: center;
  /* display: flex; */
  align-items: center;
  justify-content: center;
}

h1 {
  font-family: 'Syncopate', sans-serif;
  color: #fff;
  text-transform: uppercase;
  letter-spacing: 3vw;
  line-height: 1.2;
  font-size: 3vw;
  text-align: center;
}
h1 span {
  display: block;
  font-size: 10vw;
  letter-spacing: 0vw;
}

main {
  width: 80vw;
  left: 10%;
  height: 40vh;
  overflow: auto;
  background: rgba(0, 0, 0, 0.66);
  color: white;
  position: relative;
  padding: 1rem;
}

.logosize {
    width: 59px;
    height: auto;
}

</style>

    
  </head>
  <body>
  <!-- <video src="//media.istockphoto.com/videos/fly-over-city-lights-from-space-till-morning-video-id473264399" autoplay loop playsinline muted></video> -->
  <video src="//ak0.picdn.net/shutterstock/videos/20886700/preview/stock-footage-follow-shot-of-squad-of-soldiers-running-forward-during-military-operation-in-the-desert-slow.mp4" autoplay loop playsinline muted></video>


  <header class="viewport-header">

<h1>
  <a href="https://wearemetronome.com" target="_self" title="Metronome LLC" rel="home">
                        <img src="https://wearemetronome.com/images/Logo_notext6.png" alt="Metronome LLC" class="logosize">
            </a>
    Welcome to
    <span>Metronome</span>
  </h1>
  <div style="display:block"> 
  <a href="authorize" class="btn btn-lg btn-outline-light" >Login</a>
  </div>
</header>

<main>
    <!--  <h1>Welcome to the AWS CloudFormation PHP Sample</h1> -->
    <p/>
    <?php
      // Print out the current data and tie
      print "The Current Date and Time is: <br/>";
      print date("g:i A l, F j Y.");
    ?>
    <p/>
    <?php
      // Setup a handle for CURL
      $curl_handle=curl_init();
      curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
      curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
      // Get the hostname of the intance from the instance metadata
      curl_setopt($curl_handle,CURLOPT_URL,'http://169.254.169.254/latest/meta-data/public-hostname');
      $hostname = curl_exec($curl_handle);
      if (empty($hostname))
      {
        print "Sorry, for some reason, we got no hostname back <br />";
      }
      else
      {
        print "Server = " . $hostname . "<br />";
      }
      // Get the instance-id of the intance from the instance metadata
      curl_setopt($curl_handle,CURLOPT_URL,'http://169.254.169.254/latest/meta-data/instance-id');
      $instanceid = curl_exec($curl_handle);
      if (empty($instanceid))
      {
        print "Sorry, for some reason, we got no instance id back <br />";
      }
      else
      {
        print "EC2 instance-id = " . $instanceid . "<br />";
      }
      $Database   = "mm1n1pmg0j0p576.cgy3hxrvpwxg.us-east-1.rds.amazonaws.com";
      $DBUser     = "root";
      $DBPassword = "password";
      print "Database = " . $Database . "<br />";
      $dbconnection = mysql_connect($Database, $DBUser, $DBPassword)
                      or die("Could not connect: " . mysql_error());
      print ("Connected to $Database successfully");
      mysql_close($dbconnection);
    ?>
    <script src="//gist.github.com/bucs-fan813/927501523300860a1103333a751a8c1a.js"></script>
</main>
  </body>
</html>
