<?php echo $this->load->view("sidebar");?>
<div id="msgs">
    <?php
        $this->load->view("accounts/tabs");
        $this->load->helper("form");
        $this->lang->load('columns');
        $this->lang->load('help');
        $this->lang->load('accounts');

        echo form_open("accounts/add", array("enctype" => "multipart/form-data"));
    ?>
    <input type="hidden" name="multiadd" value="0" />
    <select name ="plugin">
    <?php
        foreach ($plugins as $plugin) {

             if (isset($_POST["plugin"]) and $_POST["plugin"] == $plugin["id"]) {

                echo "<option selected='selected' value='".$plugin["id"]."'>".$plugin["name"]."</option>";

             } else {

                echo "<option value='".$plugin["id"]."'>".$plugin["name"]."</option>";
             }
        }
    ?>
    </select>

    <p>
        <?php echo $this->lang->line('accountName'); ?>
        <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("accountNameHelp"); ?>" />
    </p>
    <input type="text" name="alias" value="<?php echo $obj->getAlias(); ?>" />
    <p class="error"><?php echo $obj->getErrors("alias"); ?></p>
    <?php
        foreach ($plugins as $plugin) {

            echo "<div class='accountOptions' rel='".$plugin["id"]."' style='display:none;' >";
            if ($plugin["template"] != "") {
    
                //echo $plugin["template"]."<br />";
                echo $this->load->view($plugin["template"]);
            }
            echo "</div>";
        }
    ?>

    <p>
        <?php echo $this->lang->line("addToColumn"); ?>
        <br />
        <select name="column">
            <option><?php echo $this->lang->line("none"); ?></option>
            <option selected='selected' value="new"><?php echo $this->lang->line("addColumn"); ?></option>
            <?php if (count($userColumns) > 0) { ?>
                <optgroup label="<?php echo $this->lang->line("ExistingOne"); ?>">
                <?php
                    foreach($userColumns as $column) {
                ?>
                    <option value="<?php echo $column->getId(); ?>"><?php echo $column->getIdentificador(); ?></option>
                <?php
                    } //endforeach
                ?>
                </optgroup>
            <?php }//endif ?>
        </select>
    </p>

    <p class="error"><?php

    if(is_array($obj->getErrors("form"))) {

        echo implode("<br />", $obj->getErrors("form"));

    } else {

        //var_dump($obj->getErrors("form"));
        echo $obj->getErrors("form");
    }
    ?></p>
    <input type="submit" disabled="disabled" />
    </form>
</div>