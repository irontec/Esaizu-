<?php
    $this->lang->load("accounts");
?>
<h1>
    <a href="<?php echo site_url(); ?>">
        <img alt="<?php echo $this->config->item('appName');?>" src="img/logo6.png">
    </a>
</h1>
<?php if($auth->getUserName() != "") { ?>

    <a class="logout" href="<?php echo site_url("login/logout");?>" title="Logout">
        <img src="<?php echo base_url()."img/shut-down.png";?>" alt="Logout" id="logout" style="vertical-align: middle;" />
        <?php echo $this->lang->line("logout"); ?>
    </a>

    <a class="misdatos" href="<?php echo site_url("accounts/me");?>" title="misdatos">
        <?php echo $this->lang->line("me"); ?>
    </a>

<?php }//endif ?>
