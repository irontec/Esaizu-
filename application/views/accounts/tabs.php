<?php
    $this->lang->load('accounts');
?>
<div class="tabs">
    <ul>
        <?php
        if ($this->uri->segment(2) == "me") {
        ?>
            <li class="selected">
                <a href="<?php echo site_url("accounts/me");?>"><?php echo $this->lang->line('me'); ?></a>
            </li>
        <?php
        } //endif
        ?>
        <li class="<?php echo $this->uri->segment(2) == "" ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("accounts");?>"><?php echo $this->lang->line('myAccounts'); ?></a>
        </li>
         <li class="<?php echo in_array($this->uri->segment(2), array("newaccount","add")) ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("accounts/newaccount");?>"><?php echo $this->lang->line('newAccount'); ?></a>
        </li>
    </ul>
</div>