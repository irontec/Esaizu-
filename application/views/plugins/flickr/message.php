<?php
$event = $message->getData("event");
$image = $message->getData("image");

if( $this->input->is_ajax_request() ) {
    $this->lang->load("flickr");
?>
<div class="flickr" id="<?php echo $message->getId(); ?>" rel="<?php echo $message->getPublishDate() ?>">
<?php
} //end if
?>
    <a target="_blank" href="<?php echo $message->getLink(); ?>">
        <img width="50px" style="float:left;padding:2px;"
        src="http://farm<?php echo $image["farm"]; ?>.static.flickr.com/<?php echo $image["server"]."/".$image["id"]."_".$image["secret"];?>_s.jpg"
         />
    </a>
         <p>
            <?php
                $type = substr($message->getRemoteId(),0,2);

                switch($type) {

                    case "fa" :

                        echo $this->lang->line("addedToFavorites");
                        break;

                    case "co" :

                        echo $message->getTitle();
                        break;

                    case "up":

                        if ($event["photos_uploaded"] == 1) {

                           echo $this->lang->line("newImg");;
                           echo "<br />".$message->getTitle();

                        } else {

                           echo $event["photos_uploaded"].$this->lang->line("newImgs");
                           echo "<br />".$message->getTitle();
                        }

                        break;
                }
            ?>
         </p>
    <p>
        <a target="_blank" href="http://www.flickr.com/people/<?php echo $image["owner"]; ?>/">
            <?php
                if (isset($event["username"])) {
                    
                	echo $event["username"];
                     
                } else {
                	
                	echo $image["ownername"];
                }
            ?>
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