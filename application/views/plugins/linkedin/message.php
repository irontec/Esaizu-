<?php
if( $this->input->is_ajax_request() ) {
?>
<div class="linkedin" id="<?php echo $message->getId(); ?>" rel="<?php echo $message->getPublishDate() ?>">
<?php
} //endif
?>
    <a target="_blank" href="<?php echo $message->getLink(); ?>">
        <?php
            $img = $message->getData("pictureUrl");
            
            if ($img == "") {

               $img = base_url()."img/anonymous.png";
            }
        ?>
        <img width="50px" src="<?php echo $img; ?>" style="float:left;padding:2px;" />
    </a>
    <?php
        echo link_wrap($message->getTitle());

        if ($message->getData("title") != "") {
    ?>
         <p>
            <a href="<?php echo $message->getData("link"); ?>">
                <?php echo $message->getData("title"); ?>
            </a>
         </p>
         <p>
            <img src="<?php echo $message->getData("thumbnailUrl"); ?>" style="float:left;" />
            <?php echo $message->getData("description"); ?>
         </p>
    <?php
        }
    ?>
    <p>
        <a target="_blank" href="<?php echo $message->getData("profile"); ?>">
            <?php echo $message->getData("firstName"); ?>
            <?php echo $message->getData("lastName"); ?>
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
}//endif
?>
