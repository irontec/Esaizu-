<?php
    $active_group = "default";

    $conf['default'] =  array (
        'js'  		=> array ( 'jquery.js', 'jquery-ui.js', 'jquery.tipsy.js', 'jquery.cookie.js' ),
        'css' 		=> array ( 'default.css', 'tipsy.css', 'ui/jquery-ui.css'),
        'header'	=> 'header',
        'footer'	=> 'footer',
        'title'		=> 'Esaizu',
        'auth'      => Auth::get_instance()
    );