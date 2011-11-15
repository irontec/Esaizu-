<?php
    $this->lang->load("twitter");
?>
<p><?php echo $this->lang->line("message"); ?></p>
<p>
    <textarea name="tw_content"><?php echo isset($post["tw_content"]) ? $post["tw_content"] : ""; ?></textarea>
    <p><span id="tw_cnum">0</span>/140</p>
    <p class="error"><?php echo $msg->getErrors("tw_content"); ?></p>
</p>
