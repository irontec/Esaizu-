<?php
    $this->lang->load("linkedin");
?>
  <p><?php echo $this->lang->line("comment"); ?></p>
  <textarea name="comment"><?php echo isset($post["comment"]) ? $post["comment"] : ""; ?></textarea>
  <p class="error"><?php echo $msg->getErrors("comment"); ?></p>

<fieldset style="margin-top:10px;">
  <legend><?php echo $this->lang->line("referenceContent"); ?></legend>
  <p><?php echo $this->lang->line("title"); ?></p>
  <input type="text" name="title" maxlength="255" length="250"
  value="<?php echo isset($post["title"]) ? $post["title"] : ""; ?>"  />

  <p><?php echo $this->lang->line("contentUrl"); ?></p>
  <input type="text" name="url" maxlength="255" length="250"
  value="<?php echo isset($post["url"]) ? $post["url"] : ""; ?>"  />
  
  <p><?php echo $this->lang->line("contentImg"); ?></p>
  <input type="text" name="img" maxlength="255" length="250"
  value="<?php echo isset($post["img"]) ? $post["img"] : ""; ?>"  />

  <p><?php echo $this->lang->line("description"); ?></p>
  <textarea name="description"><?php echo isset($post["description"]) ? $post["description"] : ""; ?></textarea>
  <p class="error"><?php echo $msg->getErrors("fieldset"); ?></p>
</fieldset>

  <p class="error"><?php echo $msg->getErrors("form"); ?></p>
  <p>
    <input type="checkbox" <?php if (isset($post["private"]) and $post["private"] == 1) { ?>checked="checked" <?php }//endif ?> id="private" name="private" value="1">
    <?php echo $this->lang->line("private"); ?>
  </p>
