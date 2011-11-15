<?php
    $this->lang->load('wordpress');
?>
<p><?php echo $this->lang->line('url'); ?></p>
<input type="text" name="feed_url" value="<?php echo isset($_POST["feed_url"]) ? $_POST["feed_url"] : ""; ?>" />
<p class="error"><?php echo $obj->getErrors("feed_url");?></p>
