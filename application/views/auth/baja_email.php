<?php
    $this->lang->load("auth");
    printf($this->lang->line("bajaEmail"), $this->config->item('appName'), site_url("login/baja_confirm/".$id."/".$code));
?>