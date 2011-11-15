<?php
    $this->load->view("sidebar");
    $this->load->helper("form");
    $this->lang->load("help");
    $this->lang->load("columns");
?>
<div id="msgs">
<?php
if (count($accounts) > 0) {

    $this->load->view("columns/tabs");
    echo form_open("columns/add");
?>
        <p><?php echo $this->lang->line("identifier");?>
        <img src="<?php echo base_url()."img/help.png";?>" class="help" title="Nombre descriptivo de la columna" />:</p>
        <input type="text" name="identificador" value="<?php echo $obj->getIdentificador();?>" />
        <p class="error"><?php echo $obj->getErrors("identificador"); ?></p>
        <p>
            <?php
                $selected = isset($_POST["type"]) ? $_POST["type"] : "standard";
            ?>
            <?php echo $this->lang->line("columnType"); ?>:
            <br />
            <input type="radio" name="type" value="standard"
            <?php if ($selected == "standard") { ?> checked='checked' <?php } ?> /> <?php echo $this->lang->line("standard"); ?>
            <br />
            <input type="radio" name="type" value="search"
            <?php if ($selected == "search") { ?> checked='checked' <?php } ?>  /> <?php echo $this->lang->line("twSearch"); ?>
        </p>
        
        <div class="search">
            <p><?php echo $this->lang->line("text"); ?></p>
            <p>
                <input type="text" name="text" value="<?php if(isset($_POST["text"])) { echo $_POST["text"]; } ?>" />
                <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("anyCommaSeparated"); ?>" />
            </p>
            <p>Hashtags</p>
            <p>
                <input type="text" name="hashtags" value="<?php if(isset($_POST["hashtags"])) { echo $_POST["hashtags"]; } ?>" />
                <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("commaSeparatedhashtags");?>" />
            </p>
            <p><?php $this->lang->line("user"); ?></p>
            <p>
                <input type="text" name="username" value="<?php if(isset($_POST["username"])) { echo $_POST["username"]; } ?>" />
                <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("userMessages"); ?>" />
            </p>
            <p><?php $this->lang->line("idioma"); ?></p>
            <p>
            <?php
                $selected = isset($_POST["language"]) ? $_POST["language"] : "";
            ?>
                <select name="language">
                    <option value="" <?php if($selected == "") echo "selected='selected'; " ?>></option>
                    <optgroup label="<?php echo $this->lang->line("Nacionales"); ?>">
                        <option value="es" <?php if($selected == "es") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Español"); ?></option>
                        <option value="ca" <?php if($selected == "ca") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Catalan");?></option>
                        <option value="eu" <?php if($selected == "eu") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Euskera");?></option>
                        <option value="gl" <?php if($selected == "gl") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Gallego");?></option>
                    </optgroup>
                    <optgroup label="Internacionales">
                        <option value="de" <?php if($selected == "de") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Aleman");?></option>
                        <option value="fr" <?php if($selected == "fr") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Frances");?></option>
                        <option value="en" <?php if($selected == "en") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Ingles");?></option>
                        <option value="it" <?php if($selected == "it") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Italiano");?></option>
                        <option value="pt" <?php if($selected == "pt") echo "selected='selected'; " ?>
                        ><?php echo $this->lang->line("Portugues");?></option>
                     </optgroup>
                </select>
            </p>
            <p class="error"><?php echo $obj->getErrors("search"); ?></p>
        </div>
        <div class="standard">
           
            <ul>
                <li class="account-type"><h3><?php echo $this->lang->line("messagesFrom");?> <span class="myAccounts" style="display:none;">[+]</span></h3></li>
                <?php foreach ($accounts as $account) { ?>
                <li>
                    <input type="checkbox" name="plugins[]" value="<?php echo $account->getId(); ?>" />
                    <?php echo $account->getAlias(); ?> (<?php echo $account->getName(); ?>)
                </li>
                <?php }//endforeach ?>
    
            </ul>
            <ul>
                <li class="account-type">
                    <h3>
                        <?php echo $this->lang->line("messagesFilters");?> <span class="myAccounts">[+]</span>
                    </h3>
                </li>
                <li style="display:none;">
                    <?php echo $this->lang->line("filterInfo"); ?>
                </li>
                <li style="display:none;"><br /></li>
                <li style="display:none;">
                    <?php echo $this->lang->line("author");?><br />
                    <select name="filterByAuthor">
                        <option value=""><?php echo $this->lang->line("any");?></option>
                        <option value="me">
                            <?php echo $this->lang->line("me");?>
                        </option>
                        <option value="notMe">
                            <?php echo $this->lang->line("notMe");?>
                        </option>
                    </select>
                </li>
                <li style="display:none;"><br /></li>
                <li style="display:none;">
                    <?php echo $this->lang->line("filterContent");?><br />
                    <input type="text" name="filterByWord" style="width:600px;" value="<?php if(isset($filters["content"])) echo $filters["content"]; ?>" />
                </li>
            </ul>
            <p class="error"><?php echo $obj->getErrors("plugins"); ?></p>
        </div>
        <p class="error"><?php echo $obj->getErrors("form"); ?></p>
        <input type="submit" />
    <?php
        echo form_close();
    ?>
</div>
<?php
} else {
?>
    <div id="account-list" style="min-height:100px;margin-top:5px;">
<?php

    $this->lang->load("accounts");
    echo sprintf($this->lang->line("noAccount"),site_url("accounts/newaccount"));
}
?>
    </div>