<?php
require_once('vendor/autoload.php');

$page = (isset($_GET['page'])) ? $_GET['page'] : false;

$titles = [
    '01-ssh-to-connect'      => 'SSH to Connect',
    '02-new-users'           => 'New Users',
    '03-ssh-security'        => 'SSH Security',
    '04-firewalls'           => 'Firewalls',
    '05-fail2ban'            => 'Fail2Ban',
    '06-application-server'  => 'The Application Server',
    '07-ssh-config'          => 'SSH Config',
    '08-scp'                 => 'Deploying with SCP',
    '09-rsync'               => 'Deploying with Rsync',
    '09b-NEEDS-CREATING-SERVER-COMM-GIT' => 'Git &amp; Github',
    '10-fabric-intro'        => 'Introducing Fabric',
    '11-fabric-sudoers'      => 'Sudoers &amp; Sudo',
    '12-fabric-symlinks'     => 'Downtime &amp; Symlinks',
    '13-fabric-rollback'     => 'Rollbacks',
    '14-fabric-migrations'   => 'Migrations',
    '15-other-services'      => 'What else does this?',
    '16-automate-id'         => 'Run Fabric Programmatically',
    '17-create-build-server' => 'Creating a Build Server',
    '18-web-listener'        => 'The Web Listener',
    '19-github-webhook'      => 'Github Webhooks',
    '20-setup-sqs'           => 'Using Queues with SQS',
    '21-setup-python'        => 'Consuming Queue Jobs with Python',
    '22-notify'              => 'Notifications During Deployment',
    '23-upstart_systemd'     => 'Monitoring the Deploy Services',
    '24-app-to-build-static' => 'Building the Application Ahead of Deployment',
    '25-build-server-deps'   => 'Making the Build Server Build',
    '26-deploy-zip'          => 'Deploying a Built Application',
];

$title = '';
$html_content = '';

if( $page )
{
    $title = (isset($titles[$page])) ? $titles[$page] : '';
    $html_content = Deploy\Render::render($page);
}
?><!DOCTYPE HTML>
<html>
<head>
    <title><?=$title?> | Deploy!</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
    <link rel="stylesheet" href="/assets/css/main.css" />
    <!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
    <!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
    <!--Facebook Metadata /-->
    <meta property="og:image" content="https://deploy.serversforhackers.com/images/deploy_social.png" />
    <meta property="og:description" content="Zero-downtime, professional deployments. A video series to massively improve your deployment strategy."/>
    <meta property="og:title" content="Deploy! by Servers for Hackers"/>

    <!--Google+ Metadata /-->
    <meta itemprop="name" content="Deploy! by Servers for Hackers">
    <meta itemprop="description" content="Zero-downtime, professional deployments. A video series to massively improve your deployment strategy.">
    <meta itemprop="image" content="https://deploy.serversforhackers.com/images/deploy_social.png">

    <!-- Twitter Metadata /-->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@srvrsforhackers" />
    <meta name="twitter:title" content="Deploy! by Servers for Hackers" />
    <meta name="twitter:description" content="Zero-downtime, professional deployments. A video series to massively improve your deployment strategy." />
    <meta name="twitter:image" content="https://deploy.serversforhackers.com/images/deploy_social.png" />
    <meta name="twitter:domain" content="deploy.serversforhackers.com">
    <link rel="icon" href="/favicon.png">
</head>
<body class="content-page">
<div id="page-wrapper">

    <!-- Header -->
    <div id="header-wrapper">
        <!-- Hero -->
        <section id="hero" class="container">
            <header>
                <img src="images/logo.png" alt="" width="85" height="85"/>
            </header>
        </section>
    </div>

    <!-- One -->
    <section class="page-content wrapper style4 container">

        <!-- Content -->
        <div class="content">
            <article>
                <header><h1><?=$title?></h1></header>
            </article>
            <section>
            <div class="media-resize">
                <video id="vjs-video-current" class="video-js vjs-default-skin vjs-big-play-centered"
                       controls preload="auto" width="100%" height="641"
                       poster="https://s3.amazonaws.com/serversforhackers/sfh-bumper-compressor.png">
                    <source src="http://player.vimeo.com/external/118355954.hd.mp4?s=baa43f374fb1ed81fcca09524cdf818f" data-quality="hd" />
                    <source src="http://player.vimeo.com/external/118355954.sd.mp4?s=740eacd4f71c4e9293a7c160941dc575" />
                    <source src="http://player.vimeo.com/external/118355954.mobile.mp4?s=fc534961d7d3242aa71b6b9c2b0aaf28" />
                    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
                </video>
            </div>
            </section>
            <article>
                <?php echo $html_content; ?>
            </article>
        </div>

    </section>



    <!-- Footer -->
    <div id="footer-wrapper">
        <div id="copyright" class="container">
            <ul class="menu">
                <li>&copy; Fideloper LLC</li>
                <li>Servers for Hackers</li>
            </ul>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.dropotron.min.js"></script>
<script src="assets/js/skel.min.js"></script>
<script src="assets/js/util.js"></script>
<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/highlight.min.js"></script>
<script src="//vjs.zencdn.net/4.12/video.js"></script>
<script src="assets/js/main.js"></script>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-20914866-6', 'auto');
    ga('send', 'pageview');

</script>
<script src="//platform.twitter.com/oct.js" type="text/javascript"></script>
<script type="text/javascript">twttr.conversion.trackPid('l6gen', { tw_sale_amount: 0, tw_order_quantity: 0 });</script>
<noscript>
    <img height="1" width="1" style="display:none;" alt="" src="https://analytics.twitter.com/i/adsct?txn_id=l6gen&p_id=Twitter&tw_sale_amount=0&tw_order_quantity=0" />
    <img height="1" width="1" style="display:none;" alt="" src="//t.co/i/adsct?txn_id=l6gen&p_id=Twitter&tw_sale_amount=0&tw_order_quantity=0" />
</noscript>


</body>
</html>
