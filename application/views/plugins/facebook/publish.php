<?php
    $this->lang->load("twitter");
?>
<p><?php echo $this->lang->line("message"); ?></p>
<p>
    <textarea name="fb_content"><?php echo isset($post["fb_content"]) ? $post["fb_content"] : ""; ?></textarea>
    <p class="error"><?php echo $msg->getErrors("tw_content"); ?></p>
</p>
