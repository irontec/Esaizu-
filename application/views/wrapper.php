<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $this->config->item('appName');?></title>
    <meta name="description" content="Agregador social open source" />
    <meta name="keywords" content="Twitter, facebook, wordpress, rss, flickr" />

    <meta name="application-name" content="Esaizu!" />
    <meta name="application-url" content="<?php echo site_url();?>" />

    <?php
    if(isset($css)) {
        foreach($css as $item) {
        ?>
        <link href="<?php echo base_url()."css/".$item?>" rel="stylesheet" type="text/css" media="screen, projection" />
        <?php
        }
    }?>
    <base href="<?php echo base_url();?>" />
</head>
<body>

    <?php
        echo '<div id="header">';
        $this->load->view($header);
        echo '</div>';

        if(isset($view)) {

            $segment = $this->uri->segment(1);
            if ($segment == "") {

                $segment = "app";
            }

            if ($segment == "app" and isset($_COOKIE["windowHeight"]) and is_numeric($_COOKIE["windowHeight"]) ) {
                
                echo '<div class="content '.$segment.'" style="height:'.$_COOKIE["windowHeight"].'px;">';
                
            } else {
                
                echo '<div class="content '.$segment.'">';
            }
                
            $this->load->view($view);
            echo '</div>';
        }

        echo '<div id="footer">';
        $this->load->view($footer);
        echo '</div>';

        if(isset($js)) {
            foreach($js as $item) {
            ?>
            <script src="<?php echo base_url();?>js/<?php echo $item;?>"></script>
            <?php
            }
        }
    ?>
</body>
</html>