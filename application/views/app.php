<?php
    $this->lang->load("app");
    $this->load->view("sidebar");
    
    $viewportHeight = isset($_COOKIE["viewportHeight"]) ? "height:".$_COOKIE["viewportHeight"]."px;" : "";
?>
<div id="msgs">
    <div class="wrapper" style="position: relative; overflow: hidden; padding-left: 5px;">
<?php
if (count($columns) > 0) {

    $i=0;
    foreach ($columns as $column) {

    if ($column->getType() == "standard" ) {

        $class = $column->getMinimized() == 1 ? " hidden" : "";
    ?>
        <div class="column<?php echo $class; ?>" rel="<?php echo $column->getId(); ?>">
            <div class="header">
                <?php echo $column->getIdentificador(); ?>
            </div>

            <a class="minim propiedades" title="Propiedades" href="<?php echo site_url("columns/edit/".$column->getId()); ?>">></a> &nbsp;
            <a class="minim" title="Minimizar" href="<?php echo site_url("app/minimize/".$column->getId()); ?>">-</a>

            <div class="body">
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb"></div>
                    </div>
                </div>
                <div class="viewport" class="noRightBorder" style="<?php echo $viewportHeight; ?>">
                    <div class="overview">
                        <?php
                            $messages = $column->getMessages();

                            if (count($messages) == 0) {
                            ?>
                                <center style="margin-bottom:10px;">
                                    <a class="hidden" class='more' href='#'><?php echo $this->lang->line("seeMore"); ?></a>
                                    <img src="<?php echo base_url();?>img/preloader.gif" alt="empty"
                                    style="width:auto;margin-left:20px;" />
                                </center>
                            <?php
                            } else {

                                foreach ($messages as $msg) {
                                    $pluginId =  $msg->getIdUP();
                                    
                                    $class = "";
                                    if ($msg->getOwner() != 1) {
                                        
                                        $class = " others";
                                    }
                                ?>
                                    <div class="<?php echo $pluginClassNames[$pluginId].$class; ?>" id="<?php echo $msg->getId(); ?>" rel="<?php echo $msg->getPublishDate() ?>"
                                    <?php if(strtotime($column->getLastUserCheck()) < strtotime($msg->getPublishDate())) { ?> style="background-color:#FAF6CF;" <?php } ?>>
                                <?php
                                    
                                    echo $renders[$pluginId]->messageBox($msg);
                                ?>
                                    </div>
                                <?php
                                }

                                if (count($messages) == $messagesPerColumn) {
                                ?>
                                    <center style="margin-bottom:10px;">
                                        <a class='more' href='#'><?php echo $this->lang->line("seeMore"); ?></a>
                                        <img src="<?php echo base_url();?>img/preloader.gif" alt="empty"
                                        class="hidden" style="width:auto;margin-left:20px;" />
                                    </center>
                                <?php
                                }
                            }
                        ?>
                     </div>
                 </div>
            </div>
            <div class="footer"></div>
        </div>
    <?php

    } else if ($column->getType() == "search" ) {

        $class = $column->getMinimized() == 1 ? " hidden" : "";

        $filters = $column->getFilters();
        $search = "";

        foreach ($filters as $key => $filter) {

            if($search != "") {

                $search .= "|";
            }

            $search .= $key."=".$filter;
        }

    ?>
        <div class="column search<?php echo $class; ?>" rel="<?php echo $column->getId(); ?>" id='<?php echo $search; ?>'>
            <div class="header">
                <?php echo $column->getIdentificador(); ?>
            </div>

            <a class="minim propiedades" title="Propiedades" href="<?php echo site_url("columns/edit/".$column->getId()); ?>">></a> &nbsp;
            <a class="minim" title="Minimizar" href="<?php echo site_url("app/minimize/".$column->getId()); ?>">-</a>

            <div class="body">
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb"></div>
                    </div>
                </div>
                <div class="viewport" class="noRightBorder" style="<?php echo $viewportHeight; ?>">
                    <div class="overview">
                        <center style="margin-bottom:10px;">
                            <img src="<?php echo base_url();?>img/preloader.gif" alt="loading"
                           style="width:auto;margin-left:20px;" />
                        </center>
                     </div>
                 </div>
            </div>
            <div class="footer"></div>
        </div>
    <?php
    } //end if
}//end foreach

} else {
?>
    <div id="account-list" style="min-height:100px;margin-top:15px;">
<?php
    $this->lang->load("accounts");

    if ($accountNum == 0) {

        echo sprintf($this->lang->line("noAccount"), site_url("accounts/newaccount"));

    } else {

        echo sprintf($this->lang->line("noColumn"), site_url("columns"));
    }
}
?>
    </div>
    <div style="clear:both;"></div>
        </div>
    </div>