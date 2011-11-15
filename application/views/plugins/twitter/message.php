<?php
if( $this->input->is_ajax_request() ) {

    $class = "";
    
    if ($message->getOwner() != 1) {
        $class = " others";
    }
?>
<div class="twitter<?php echo $class; ?>" id="<?php echo $message->getId(); ?>" rel="<?php echo $message->getPublishDate() ?>">
<?php
}//endif
?>
    <a target="_blank" href="<?php echo $message->getLink(); ?>" class="avatar">
        <img src="<?php echo $message->getData("profile_image_url"); ?>"
        alt="<?php echo $message->getData("user_name"); ?>" style="float:left;padding:2px;" />
    </a>
    <?php
    if ($message->getOwner() != 1) {
    ?>
    <div class="actions" id="<?php echo $message->getRemoteId(); ?>">
        <a href="<?php echo site_url("invoke/method/twitter/retweet/".$message->getId() ); ?>">
            <img src="<?php echo site_url(); ?>img/retweet.png" title="Retweet" />
        </a>
        <?php
        if ($message->getData("favorited") == "true") {
        ?>
            <a href="<?php echo site_url("invoke/method/twitter/revertfavorite/".$message->getId() ); ?>">
                <img src="<?php echo site_url(); ?>img/favorites_remove.png" title="Quitar de favoritos" />
            </a>
        <?php
        } else {
        ?>
            <a href="<?php echo site_url("invoke/method/twitter/favorite/".$message->getId() ); ?>">
                <img src="<?php echo site_url(); ?>img/favorites_add.png" title="Añadir a favoritos" />
            </a>
        <?php
        }//endif
        ?>
    </div>
    <?php
    }//endif
    ?>
    <p>
    <?php echo link_wrap($message->getTitle()); ?>
    </p>
    <p>
        <a target="_blank" href="<?php  echo $message->getLink(); ?>">
            <?php echo $message->getData("user_name");?>
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
} //endif
?>