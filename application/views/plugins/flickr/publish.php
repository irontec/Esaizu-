<?php
    $this->lang->load("flickr");
?>
<p><?php echo $this->lang->line("img"); ?></p>
<p>
    <input type="file" name="flickr_img" value="<?php echo isset($post["flickr_img"]) ? $post["flickr_img"] : ""; ?>" />
    <p class="error"><?php echo $msg->getErrors("flickr_img"); ?></p>
</p>
<p><?php echo $this->lang->line("title"); ?></p>
<p>
    <input type="text" name="flickr_title" <?php echo isset($post["flickr_title"]) ? $post["flickr_title"] : ""; ?> />
    <p class="error"><?php echo $msg->getErrors("flickr_title"); ?></p>
</p>
<p><?php echo $this->lang->line("description"); ?></p>
<p>
    <textarea name="flickr_description"><?php echo isset($post["flickr_description"]) ? $post["flickr_description"] : ""; ?></textarea>
    <p class="error"><?php echo $msg->getErrors("flickr_description"); ?></p>
</p>
<p><?php echo $this->lang->line("tags"); ?></p>
<p>
    <input type="text" name="flickr_tags" value="<?php echo isset($post["flickr_tags"]) ? $post["flickr_tags"] : ""; ?>" />
    <p class="error"><?php echo $msg->getErrors("flickr_tags"); ?></p>
</p>
<p><?php echo $this->lang->line("privacity"); ?></p>
<p>
    <input selected='selected' type="radio" name="flickr_private" value="0" />
    <?php echo $this->lang->line("public"); ?> <br />
    <input type="radio" name="flickr_private" value="1" />
    <?php echo $this->lang->line("private"); ?> <br />
    <div style="margin-left:10px;">
        <input disabled="disabled" type="checkbox" name="flickr_friends" value="1" />
         <?php echo $this->lang->line("friends"); ?>  <br />
        <input disabled="disabled" type="checkbox" name="flickr_relatives" value="1" />
         <?php echo $this->lang->line("relatives"); ?>
    </div>
</p>
