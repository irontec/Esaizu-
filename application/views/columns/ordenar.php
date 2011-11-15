<?php
    $this->load->view("sidebar");
    $this->load->helper("form");
    $this->lang->load("columns");
?>
<div id="msgs">
    <?php
        $this->load->view("columns/tabs");
        echo form_open("columns/reorder/");
    ?>
        <p><strong><?php echo $this->lang->line("orderColumnsInfo"); ?></strong></p>
        <ul id="sortable">
        <?php foreach($columns as $column) { ?>
            <li>
                <span title="<?php echo $column->getIdentificador(); ?>"><?php echo $column->getIdentificador(); ?></span>
                <input type="hidden" name="identifier[]" value="<?php echo $column->getId(); ?>" />
            </li>
        <?php } ?>
        </ul>
        
        <br />
        <input type="submit" value="<?php echo  $this->lang->line("save"); ?>" />
    <?php
        echo form_close();
    ?>
</div>