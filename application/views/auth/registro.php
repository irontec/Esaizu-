<?php
    $this->lang->load("auth");
?>
<div id="msgs">
    <div class="tabs">
        <ul>
            <li class="">
                <a href="<?php echo site_url("login/");?>">
                    Login
                </a>
            </li>
            <li class="selected">
                <a href="<?php echo site_url("login/registro"); ?>">
                    <?php echo $this->lang->line("register"); ?>
                </a>
            </li>
        </ul>
    </div>
    <?php echo form_open("login/registro"); ?>

        <?php echo $this->lang->line("nickname");?>:
        <br />
        <input type="text" name="userName" value="<?php echo $obj->getUserName();?>" />
        <p class="error"><?php echo $obj->getErrors("userName"); ?></p>

        <?php echo $this->lang->line("email");?>:
        <br />
        <input type="text" name="email" value="<?php echo $obj->getEmail();?>" />
        <p class="error"><?php echo $obj->getErrors("email"); ?></p>
        <br />

        <?php echo $this->lang->line("password");?>:
        <br />
        <input type="password" name="passwd" value="" />
        <p class="error"><?php echo $obj->getErrors("password"); ?></p>

        <?php echo $this->lang->line("passwordAgain");?>:
        <br />
        <input type="password" name="passwd2" value="" />
        <p class="error"><?php echo $obj->getErrors("password2"); ?></p>

        <br />
        <p class="error"><?php echo $obj->getErrors("form"); ?></p>
        <input type="submit" value="enviar" />
    </form>
</div>
