<?php
    $this->lang->load("login");
?>
<div id="msgs">
    <div class="tabs">
        <ul>
            <li class="selected">
                <a href="<?php echo site_url("login/");?>">
                    <?php echo $this->lang->line("login"); ?>
                </a>
            </li>
    		<?php if($auth->getUserName() != "") { ?>
                <li class="">
                    <a href="<?php echo site_url("login/baja"); ?>">
                        <?php echo $this->lang->line("unregister"); ?>
                    </a>
                </li>
    		<?php } else { ?>
                <li class="">
                    <a href="<?php echo site_url("login/registro"); ?>">
                        <?php echo $this->lang->line("register"); ?>
                    </a>
                </li>
    		<?php } ?>
        </ul>
    </div>

    <?php
        echo form_open("login");
        if ($this->session->userdata('accessCounter') > 1) { ?>
        <div style="text-align:center;">
            <a href="<?php echo site_url("login/remember");?>"><?php echo $this->lang->line("remember_passwd"); ?></a>
        </div>
    <?php } //endif
        echo $this->lang->line("nick_or_email"); ?>:
        <br />
        <input type="text" name="nickname" value="<?php echo $obj->getEmail();?>" />
        <p class="error"><?php echo $obj->getErrors("email").$obj->getErrors("userName"); ?></p>
        <br />
        <?php echo $this->lang->line("password"); ?> :
        <br />
        <input type="password" name="passwd" value="" />
        <p class="error"><?php echo $obj->getErrors("password"); ?></p>
        <br />
        <input type="checkbox" name="remember" value="1" /> <?php echo $this->lang->line("rememberMe"); ?>
        <p class="error"><?php echo $obj->getErrors("form"); ?></p>
        <input type="submit" value="<?php echo $this->lang->line("submit"); ?>" />
    </form>
</div>