<?php
    $this->lang->load('wordpress');
?>
<p><?php echo $this->lang->line('url'); ?></p>
<input type="text" name="url" value="<?php echo isset($_POST["url"]) ? $_POST["url"] : ""; ?>" />
<p class="error"><?php echo $obj->getErrors("wp_url");?></p>

<p><?php echo $this->lang->line('username'); ?></p>
<input type="text" name="username" value="<?php echo isset($_POST["username"]) ? $_POST["username"] : ""; ?>" />
<p class="error"><?php echo $obj->getErrors("wp_username");?></p>

<p><?php echo $this->lang->line('password'); ?></p>
<input type="password" name="password" value="" />
<p class="error"><?php echo $obj->getErrors("wp_password");?></p>