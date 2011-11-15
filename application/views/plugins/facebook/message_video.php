
<p>
    <a target="_blank" href="<?php echo $message->getLink(); ?>">
        <?php echo $message->getTitle(); ?>
    </a>
</p>
<p>
    <a target="_blank" href="<?php echo $message->getLink(); ?>">
        <img class="link" height="45px" src="<?php echo $message->getData("picture"); ?>"/>
    </a>
    <?php
        echo $message->getData("text");
    ?>
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