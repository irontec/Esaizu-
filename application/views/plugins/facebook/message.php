<?php
    $this->lang->load("app");
    if ($this->input->is_ajax_request()) {

        $class = "";
        if ($message->getOwner() == 0) {

            $class .= " others";
        }
?>
        <div class="facebook<?php echo $class; ?>" id="<?php echo $message->getId(); ?>" rel="<?php echo $message->getPublishDate() ?>">
<?php
    }
?>
<span class="avatar">
    <img height="50px" src="https://graph.facebook.com/<?php echo $message->getData("from")->id; ?>/picture"  style="float:left;padding:2px;" />
</span>
<?php
    if ($message->getOwner() == 0) {
?>
    <div class="actions">
        <a href="<?php echo site_url("invoke/method/facebook/like/".$message->getId()); ?>">
            <img src="<?php echo site_url(); ?>img/ilike.png" title="Me gusta" />
        </a>
    </div>
    
<?php
    }//end if
    $this->load->view("plugins/facebook/message_".$message->getData("type"));

    if($message->getData("likes") > 0 or $message->getData("comments") > 0) {
?>
        <div class="expand">
            <div class="options">
            <?php if ($message->getData("likes") > 0) { ?>
                <a href="#" rel="likes"><?php echo $message->getData("likes") ?>
                    <img src="<?php echo base_url(); ?>img/ilike.png" />
                </a>
            <?php } //endif ?>
            <?php if ($message->getData("comments") > 0) { ?>
                <a href="#" rel="comments"><?php echo $message->getData("comments") ?>
                    <img src="<?php echo base_url(); ?>img/comment.png" />
                </a>
            <?php } //endif ?>
            </div>

            <?php if ($message->getData("likes") > 0) { ?>
            <ul style="margin: 5px 0;" class="likes">
                <li style="overflow:hidden;">
                <?php
                    foreach ($message->getData("whoLikes") as $who) {
                    ?>
                        <a href="http://www.facebook.com/profile.php?id=<?php echo $who->id; ?>" title="<?php echo $who->name; ?>">
                            <img src="https://graph.facebook.com/<?php echo $who->id; ?>/picture"  style="float: left; padding: 5px 2px 2px 0pt; width: 45px;" />
                        </a>
                    <?php
                    } //end foreach
                ?>
                </li>
            </ul>
            <?php
            } //end if
            ?>
            <?php if ($message->getData("comments") > 0) { ?>
            <ul style="margin: 5px 0;" class="comments">
            <?php
                foreach ($message->getData("msgComments") as $msgComment) {
                ?>
                <li style="overflow:hidden;">
                    <a href="http://www.facebook.com/profile.php?id=<?php echo $msgComment->from->id; ?>" title="<?php echo $msgComment->from->name; ?>">
                        <img src="https://graph.facebook.com/<?php echo $msgComment->from->id; ?>/picture"  style="float: left; padding: 5px 2px 2px 0pt; width: 25px;" />
                    </a>
                    <?php echo $msgComment->message; ?>
                </li>
                <?php
                } // endforeach

                if( count($message->getData("msgComments")) < $message->getData("comments")) {
                ?>
                    <li style="margin-top:5px;">
                        <a href="#"><?php echo $this->lang->line("seeAll"); ?></a>
                    </li>
                <?php
                }
            ?>
            </ul>
            <?php
            }//endif
            ?>
            
        </div>
<?php
    }

    if ( $this->input->is_ajax_request() ) {
?>
    </div>
<?php
    }
?>