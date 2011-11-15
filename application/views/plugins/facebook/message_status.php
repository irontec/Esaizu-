
<?php echo link_wrap($message->getTitle()); ?>
<p style="clear:both;">
    <a target="_blank" href="http://www.facebook.com/profile.php?id=<?php echo $message->getData("from")->id; ?>">
        <?php echo $message->getData("from")->name; ?>
    </a>
     hace
    <?php
        $this->load->view("common/publishDate");
    ?>
</p>