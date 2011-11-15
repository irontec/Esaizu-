<div id="msgs">
    <div id="account-list">
<?php
    $this->lang->load("auth");
    echo sprintf($this->lang->line("yourNewPasswd"), $this->config->item('appName'), $newPass);
?>
    </div>
</div>