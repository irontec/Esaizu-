<?php
    $this->load->view("sidebar");
    $this->load->helper("form");
    $this->lang->load("columns");
?>
<div id="msgs">
<?php
$this->load->view("columns/tabs");
?>
    <div id="account-list">
        <ul>
        <?php
        foreach ($columns as $column) {
        ?>
        <li>
            <h3><?php echo $column->getIdentificador(); ?></h3>
            <a href="<?php echo site_url("columns/edit/".$column->getId());?>"><?php echo $this->lang->line("edit");?></a>
            <a href="<?php echo site_url("columns/delete/".$column->getId());?>"><?php echo $this->lang->line("delete");?></a>
            
        </li>
        <?php
        }
        ?>
        </ul>
    </div>
</div>