<?php
    if( $this->input->is_ajax_request() ) {
?>
    <div class="feed" id="<?php echo $message->getId(); ?>" rel="<?php echo $message->getPublishDate() ?>">
<?php
    }
    $account = Auth::get_instance()->getAccount($message->getIdUP())->getAlias();
?>
    <div style="float: left; padding: 2px;">
        <img src="<?php echo site_url()."img/feed.png"?>" alt="feed" />
    </div>
    <p>
        <a href="<?php echo $message->getLink(); ?>">
            <?php echo $message->getTitle(); ?>
        </a>
    </p>
    <p>
        <?php echo $message->getData("text"); ?>
    </p>
    <p>
        <a target="_blank" href="<?php echo $message->getLink(); ?>">
            <?php echo $account; ?>
        </a>
         hace
        <?php
            $this->load->view("common/publishDate");
        ?>
    </p>
<?php
    if( $this->input->is_ajax_request() ) {
?>
</div>
<?php
    }
?>