<?php $this->load->view("sidebar"); ?>
<div id="msgs">
    <?php
        $this->load->view("accounts/tabs");
        $this->lang->load('accounts');
    ?>
    <?php echo form_open("accounts/me"); ?>
    
    <p>
        <?php echo $this->lang->line("username"); ?> :
        <strong><?php echo $user->getUserName(); ?></strong>
    </p>

    <div>
        <h3><?php echo $this->lang->line("modPasswd"); ?></h3>
        <p>
            <?php echo $this->lang->line("passwd"); ?> :
            <br />
            <input type="password" name="passwd" />
            <p class="error"><?php echo $user->getErrors("password"); ?></p>
        </p>
        <p>
            <?php echo $this->lang->line("repPasswd"); ?> :
            <br />
            <input type="password" name="passwd2" />
        </p>
        <?php echo $user->getErrors("form"); ?>
        <input type="submit" value="<?php echo $this->lang->line("change"); ?>" />
        
        <p class="Error"><?php echo $user->getErrors("Form");?></p>
    </div>
       
    <?php echo form_close(); ?>
</div>
