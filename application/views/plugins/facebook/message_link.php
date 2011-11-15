
<p>
<?php echo $message->getData("text"); ?>
</p>
<p style="margin:3px 5px 3px 0; background-color:#FAF6CF;overflow:hidden;">
    <?php if($message->getData("picture") != "") { ?>
        <img class="link" src="<?php echo $message->getData("picture"); ?>" />
    <?php } //endif  ?>
    <a target="_blank" href="<?php echo $message->getLink(); ?>">
        <?php echo $message->getTitle(); ?>
    </a>
    <?php
        echo $message->getData("description");
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
