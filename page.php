<?php
require_once('vendor/autoload.php');

$page = (isset($_GET['page'])) ? $_GET['page'] : false;

$html_content = '';

if( $page )
{
    $html_content = Deploy\Render::render($page);
}
?><!DOCTYPE HTML>
<html>
<head>
    <title>Deploy! | Servers for Hackers</title>
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
<body class="homepage">
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
    <section class="wrapper style4 container">

        <!-- Content -->
        <div class="content">
            <section>
                <a href="#" class="image featured"><img src="images/pic04.jpg" alt="" /></a>
                <?php echo $html_content; ?>
            </section>
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
