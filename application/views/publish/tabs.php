<?php
    $this->lang->load('publish');
?>
<div class="tabs">
    <ul>
        <li class="<?php echo in_array($this->uri->segment(2), array("","send")) ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("publish");?>"><?php echo $this->lang->line('publish'); ?></a>
        </li>
         <li class="<?php echo in_array($this->uri->segment(2), array("programmed","edit","update")) ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("publish/programmed");?>"><?php echo $this->lang->line('programmed'); ?></a>
        </li>
    </ul>
</div>