
<?php
    $this->lang->load("auth");
    echo form_open("login/remember");
?>
    <p><?php echo $this->lang->line("newPasswdInfo");?></p>
    <input type="text" name="email" value="<?php echo $obj->getEmail();?>" />
    <p class="error"><?php echo $obj->getErrors("email"); ?></p>
    <input type="submit" value="enviar" />
</form>

