<?php
$this->lang->load("app");
if( $this->input->is_ajax_request() ) {
?>
<div class="wordpress" id="<?php echo $message->getId(); ?>" rel="<?php echo $message->getPublishDate() ?>">
<?php
} //endif
?>
    <div style="float: left; padding: 2px;">
        <img src="<?php echo base_url(); ?>img/wordpress.png" />
    </div>
    <a target="_blank" href="<?php echo $message->getLink(); ?>">
        <?php echo $message->getTitle(); ?>
    </a>
    <?php
        $data = $message->getData();
        $data["text"] = strip_tags($data["text"]);
    ?>
    <p>
        <?php
            echo substr($data["text"], 0, 140);
            if (strlen($data["text"]) > 140) {

                echo "...";
            }
        ?>
    </p>
    <p>
        <a target="_blank" href="<?php echo $message->getLink(); ?>#comments">
            <?php echo $data["comments"]; ?>
        </a> <?php echo $this->lang->line("comments");?>
    </p>
    <p>
        <?php echo $this->lang->line("publishDate"); ?>
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