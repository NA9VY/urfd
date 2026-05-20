<!DOCTYPE html>
<?php
/*
 * This dashboard is being developed by the DVBrazil Team as a courtesy to
 * the XLX Multiprotocol Gateway Reflector Server project.
 * The dashboard is based of the Bootstrap dashboard template.
*/

if (file_exists("./pgs/functions.php")) {
    require_once("./pgs/functions.php");
} else {
    die("functions.php does not exist.");
}
if (file_exists("./pgs/config.inc.php")) {
    require_once("./pgs/config.inc.php");
} else {
    die("config.inc.php does not exist.");
}
if (!class_exists('ParseXML')) require_once("./pgs/class.parsexml.php");
if (!class_exists('Node')) require_once("./pgs/class.node.php");
if (!class_exists('xReflector')) require_once("./pgs/class.reflector.php");
if (!class_exists('Station')) require_once("./pgs/class.station.php");
if (!class_exists('Peer')) require_once("./pgs/class.peer.php");
if (!class_exists('Interlink')) require_once("./pgs/class.interlink.php");

$Reflector = new xReflector();
$Reflector->SetFlagFile("./pgs/country.csv");
$Reflector->SetPIDFile($Service['PIDFile']);
$Reflector->SetXMLFile($Service['XMLFile']);

$Reflector->LoadXML();

// ==================== URFD & TCD SERVICE STATUS ====================
function getServiceStatus($service) {
    $output = trim(shell_exec("sudo systemctl is-active " . escapeshellarg($service) . " 2>/dev/null"));
    return $output === 'active';
}

$urfd_active = getServiceStatus('urfd.service');
$tcd_active  = getServiceStatus('tcd.service');
// ================================================================

if ($CallingHome['Active']) {
    $CallHomeNow = false;
    if (!file_exists($CallingHome['HashFile'])) {
        $Hash = CreateCode(16);
        $LastSync = 0;
        $Ressource = @fopen($CallingHome['HashFile'], "w");
        if ($Ressource) {
            @fwrite($Ressource, "<?php\n");
            @fwrite($Ressource, "\n" . '$LastSync = 0;');            @exec("chmod 777 " . $CallingHome['HashFile']);
            $CallHomeNow = true;
        }
    } else {
        include($CallingHome['HashFile']);
        if ($LastSync < (time() - $CallingHome['PushDelay'])) {
            $Ressource = @fopen($CallingHome['HashFile'], "w");
            if ($Ressource) {
                @fwrite($Ressource, "<?php\n");
                @fwrite($Ressource, "\n" . '$LastSync = ' . time() . ';');
                @fwrite($Ressource, "\n" . '$Hash = "' . $Hash . '";');
                @fwrite($Ressource, "\n\n" . '?>');
                @fclose($Ressource);
            }
            $CallHomeNow = true;
        }
    }
    if ($CallHomeNow || isset($_GET['callhome'])) {
        $Reflector->SetCallingHome($CallingHome, $Hash);
        $Reflector->ReadInterlinkFile();
        $Reflector->PrepareInterlinkXML();
        $Reflector->PrepareReflectorXML();
        $Reflector->CallHome();
    }
} else {
    $Hash = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $PageOptions['MetaDescription']; ?>"/>
    <meta name="keywords" content="<?php echo $PageOptions['MetaKeywords']; ?>"/>
    <meta name="author" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>
    <meta name="revisit" content="<?php echo $PageOptions['MetaRevisit']; ?>"/>
    <meta name="robots" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title><?php echo str_replace("XLX", "URF", $Reflector->GetReflectorName()); ?> Universal Reflector</title>
    <link rel="icon" href="./favicon.ico" type="image/vnd.microsoft.icon">
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles -->
    <link href="css/dashboard.css" rel="stylesheet">
            @fwrite($Ressource, "\n" . '$Hash = "' . $Hash . '";');
            @fwrite($Ressource, "\n\n" . '?>');
            @fclose($Ressource);
            
    <style>
        .service-led {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #222;
            box-shadow: 0 0 5px currentColor;
            vertical-align: middle;
            margin: 0 4px;
        }
        .led-green { background-color: #00ff00; color: #00ff00; }
        .led-red   { background-color: #ff4444; color: #ff4444; }
    </style>

    <!-- HTML5 shim and Respond.js -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <?php
    if ($PageOptions['PageRefreshActive']) {
        echo '
   <script src="./js/jquery-1.12.4.min.js"></script>
   <script>
      var PageRefresh;
      function ReloadPage() {
         $.get("./index.php'.(isset($_GET['show'])?'?show='.$_GET['show']:'').'", function(data) {
            var BodyStart = data.indexOf("<bo"+"dy");
            var BodyEnd = data.indexOf("</bo"+"dy>");
            if ((BodyStart >= 0) && (BodyEnd > BodyStart)) {
               BodyStart = data.indexOf(">", BodyStart)+1;
               $("body").html(data.substring(BodyStart, BodyEnd));
            }
         })
            .always(function() {
               PageRefresh = setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');
            });
      }';
        if (!isset($_GET['show']) || (($_GET['show'] != 'livequadnet') && ($_GET['show'] != 'reflectors') && ($_GET>
            echo '
      PageRefresh = setTimeout(ReloadPage, ' . $PageOptions['PageRefreshDelay'] . ');';
        }
        echo '
      function SuspendPageRefresh() {
        clearTimeout(PageRefresh);
      }
   </script>';
    }
    if (!isset($_GET['show'])) $_GET['show'] = "";
    ?>
</head>
<body>
<?php if (file_exists("./tracking.php")) { include_once("tracking.php"); } ?>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <span class="navbar-brand">
                <a href="http://w9winxlx.us/"><img src="./img/SIN1.jpg" alt="SIN Logo" width="70" height="40"></a>
                Southern Indiana Network
            </span>
        </div>

        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
<!--                <li class="navbar-info">NA9VY Dashboard v<?php echo $PageOptions['DashboardVersion']; ?></li> ->
                <li class="navbar-info">Days since last injury: <?php echo FormatSeconds($Reflector->GetServiceUpti>

                <!-- URFD & TCD Status -->
                <li class="navbar-info">
                    URFD:
                    <span class="service-led <?php echo $urfd_active ? 'led-green' : 'led-red'; ?>"></span>
                    <strong><?php echo $urfd_active ? 'ACTIVE' : 'INACTIVE'; ?></strong>
                </li>
                <li class="navbar-info">
                    TCD:
                    <span class="service-led <?php echo $tcd_active ? 'led-green' : 'led-red'; ?>"></span>
                    <strong><?php echo $tcd_active ? 'ACTIVE' : 'INACTIVE'; ?></strong>
                </li>

<!-- XML File Updated? -->

<?php
date_default_timezone_set('America/Indiana/Indianapolis');

$xmlfile = '/var/log/xlxd.xml';

if (file_exists($xmlfile)) {
    $lastmod = filemtime($xmlfile);
    $age_seconds = time() - $lastmod;
    $is_stale = $age_seconds > 180;        // ← change to 30 if you want it stricter

    $led_class = $is_stale ? 'service-led led-red' : 'service-led led-green';
    $status_text = $is_stale ? 'STALE' : 'UPDATED';
    $time_str = date('H:i:s', $lastmod);
    
    echo '<li class="navbar-info">XML:
          <span class="' . $led_class . '"></span>
          <strong>' . $status_text . '</strong>
          <span style="color: ' . ($is_stale ? '#ff4444' : '#44ff88') . '; font-size: 0.85em;">
              (' . $time_str . ')
          </span>
          </li>';
                } else {
                    echo '<li class="navbar-info" style="color:#ff8888;">⚠️ xlxd.xml not found</li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <ul class="nav nav-sidebar">
                <li<?php echo (($_GET['show'] == "users") || ($_GET['show'] == "")) ? ' class="active"' : ''; ?>><a>
                <li<?php echo ($_GET['show'] == "repeaters") ? ' class="active"' : ''; ?>><a href="./index.php?show>
                <li<?php echo ($_GET['show'] == "peers") ? ' class="active"' : ''; ?>><a href="./index.php?show=pee>
                <li<?php echo ($_GET['show'] == "modules") ? ' class="active"' : ''; ?>><a href="./index.php?show=m>
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-----------------------<br>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RPi & Network Stats<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for SIN</p>
                <li<?php echo ($_GET['show'] == "pidashboard") ? ' class="active"' : ''; ?>><a href="https://<?php >
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-----------------------<br>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BrandMeister Links<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for SIN</p>
                <li<?php echo ($_GET['show'] == "lastheardsin") ? ' class="active"' : ''; ?>><a href="https://brand>
                <li<?php echo ($_GET['show'] == "devicessin") ? ' class="active"' : ''; ?>><a href="https://brandme>

                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-----------------------<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Links to >
                <li<?php echo ($_GET['show'] == "reflectors") ? ' class="active"' : ''; ?>><a href="https://dvref.c>
                <li<?php echo ($_GET['show'] == "livequadnet") ? ' class="active"' : ''; ?>><a href="./index.php?sh>
            </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php
            if ($CallingHome['Active']) {
                if (!is_readable($CallingHome['HashFile']) && (!is_writeable($CallingHome['HashFile']))) {
                    echo '
         <div class="error">
            your private hash in ' . $CallingHome['HashFile'] . ' could not be created, please check your config fi>
         </div>';
                }
            }
            switch ($_GET['show']) {
                case 'users' :
                    require_once("./pgs/users.php");
                    break;
                case 'repeaters' :
                    require_once("./pgs/repeaters.php");
                    break;
                case 'modules' :
                    require_once("./pgs/modules.php");
                    break;
                case 'livequadnet' :
                    require_once("./pgs/livequadnet.php");
                    break;
                case 'peers' :
                    require_once("./pgs/peers.php");
                    break;
                case 'reflectors' :
                    require_once("./pgs/reflectors.php");
                    break;
                default :
                    require_once("./pgs/users.php");
            }
            ?>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p><a href="mailto:<?php echo $PageOptions['ContactEmail']; ?>"><?php echo $PageOptions['ContactEmail']; ?>>
    </div>
</footer>

<!-- Bootstrap core JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="js/jquery-1.12.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>                
