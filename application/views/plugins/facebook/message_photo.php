
<a target="_blank" href="<?php echo $message->getLink(); ?>">
    <?php echo $message->getTitle(); ?>
</a>

<img height="85px" src="<?php echo $message->getData("picture"); ?>"  style="float:left;padding:2px;" />
<p>
    <?php echo $message->getData("text"); ?>
</p>
<p style="clear:both;">
    <a target="_blank" href="http://www.facebook.com/profile.php?id=<?php echo $message->getData("from")->id; ?>">
        <?php echo $message->getData("from")->name; ?>
    </a>
     hace
    <?php
        $this->load->view("common/publishDate");
    ?>
</p>

