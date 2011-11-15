<?php
    $this->lang->load("columns");
?>
<div class="tabs">
    <ul>
        <li class="<?php echo in_array($this->uri->segment(2), array("", "add")) ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("columns");?>"><?php echo $this->lang->line("addColumn"); ?></a>
        </li>
        <li class="<?php echo in_array($this->uri->segment(2), array("listar","edit","update")) ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("columns/listar");?>"><?php echo $this->lang->line("manageColumns"); ?></a>
        </li>
        <li class="<?php echo in_array($this->uri->segment(2), array("order")) ? "selected" : ""; ?>" >
            <a href="<?php echo site_url("columns/order");?>"><?php echo $this->lang->line("orderColumns"); ?></a>
        </li>
    </ul>
</div>