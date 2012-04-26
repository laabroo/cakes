<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Premium Series
Description: A three-column, fixed-width blog design.
Version    : 1.0
Released   : 20090303

-->
<?php
$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

        <?php echo $this->Html->charset(); ?>
        <title>
            <?php echo $cakeDescription ?>:
            <?php echo $title_for_layout; ?>
        </title>
        <?php
        echo $this->Html->meta('icon');

//        echo $this->Html->css('cake.generic');
        echo $this->Html->css(array('default.css'));

        echo $scripts_for_layout;
        ?>

        <link href="default.css" rel="stylesheet" type="text/css" media="screen" />
    </head>
    <body>
        <!-- start header -->
        <div id="header">
            <div id="logo">

                <p>Dinas Perindustrian dan Perdaganan Kota Batam</p>

            </div>
            <div id="menu">
                <ul id="main">
                    <li class="current_page_item"><a href="<?php echo $this->base ; ?>">Home</a></li>
                    <li><a href="#">Products</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li>
                        <?php if ($logged_in): ?>
                        <?php echo $this->Html->link('Logout', array('controller' => 'users', 'action' => 'logout')); ?>
                        <?php else: ?>
                            <?php echo $this->Html->link('Login', array('controller' => 'users', 'action' => 'login')); ?>
                        <?php endif; ?>
                    </li>
                </ul>

            </div>

        </div>
        <!-- end header -->

        <div id="wrapper">
            <!-- start page -->
            <div id="page">
                <div id="sidebar1" class="sidebar">
                    <ul>
                        <li>
                            <h2>Recent Posts</h2>
                            <ul>
                                <li><a href="#">Aliquam libero</a></li>
                                <li><a href="#">Consectetuer adipiscing elit</a></li>
                                <li><a href="#">Metus aliquam pellentesque</a></li>
                                <li><a href="#">Suspendisse iaculis mauris</a></li>
                                <li><a href="#">Proin gravida orci porttitor</a></li>
                                <li><a href="#">Aliquam libero</a></li>

                            </ul>
                        </li>
                    </ul>
                </div>


                <!-- start content -->
                <div id="content">
                    <div class="post">
            

                        <?php echo $this->Session->flash(); ?>
                        <?php echo $this->Session->flash('auth'); ?>

                        <?php echo $content_for_layout; ?>
                    </div>
                </div>
                <!-- end content -->


                <div id="sidebar2" class="sidebar">
                    <ul>
                        <li>
                            <form id="searchform" method="get" action="#">
                                <div>
                                    <h2>Site Search</h2>
                                    <input type="text" name="s" id="s" size="15" value="" />
                                </div>
                            </form>
                        </li>
                        <li>
                            <h2>Categories</h2>
                            <ul>
                                <li><a href="#">Aliquam libero</a></li>
                                <li><a href="#">Consectetuer adipiscing elit</a></li>
                                <li><a href="#">Metus aliquam pellentesque</a></li>
                                <li><a href="#">Suspendisse iaculis mauris</a></li>
                                <li><a href="#">Urnanet non molestie semper</a></li>

                            </ul>
                        </li>
                    </ul>
                </div>

                <!-- end sidebars -->
                <div style="clear: both;">&nbsp;</div>
            </div>
        </div>

        <div id="footer">
            <p class="copyright">&copy;&nbsp;&nbsp;2009 All Rights Reserved &nbsp;&bull;&nbsp; Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a>.</p>
            <p class="link"><a href="#">Privacy Policy</a>&nbsp;&#8226;&nbsp;<a href="#">Terms of Use</a></p>
        </div>
    </body>
</html>