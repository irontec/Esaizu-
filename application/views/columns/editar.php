<?php
    $this->load->view("sidebar");
    $this->load->helper("form");
    $this->lang->load("columns");
    $this->lang->load("help");
?>
<div id="msgs">
    <?php
        $this->load->view("columns/tabs");
        echo form_open("columns/update/".$obj->getId());
    ?>
        <p>
            <?php echo $this->lang->line("identifier");?>
            <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("columnNameHelp"); ?>" />:
        </p>
        <input type="text" name="identificador" value="<?php echo $obj->getIdentificador();?>" />
        <p class="error"><?php echo $obj->getErrors("identificador"); ?></p>
        <p>
            <?php
                $selected = $obj->getType();
                $filters = $obj->getFilters();
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
                <?php
                    $text = "";
                    if (isset($_POST["text"])) {

                        $text = $_POST["text"];

                    } else if (isset($filters["text"])) {

                        $text = $filters["text"];
                    }
                ?>
                <input type="text" name="text" value="<?php echo $text;  ?>" />
                <img src="<?php echo base_url()."img/help.png";?>" class="help" title="Tantos terminos como quieras, separados por comas." />
            </p>
            <p>Hashtags</p>
            <p>
                <?php
                    $hashtags = "";
                    if (isset($_POST["hashtags"])) {

                        $hashtags = $_POST["hashtags"];

                    } else if (isset($filters["hashtags"])) {

                        $hashtags = $filters["hashtags"];
                    }
                ?>
                <input type="text" name="hashtags" value="<?php echo $hashtags; ?>" />
                <img src="<?php echo base_url()."img/help.png";?>" class="help" title="Tantos como quieras, separados por comas. No es necesario escribir #" />
            </p>
            <p><?php echo $this->lang->line("user"); ?></p>
            <p>
                <?php
                    $username = "";
                    if (isset($_POST["username"])) {

                        $username = $_POST["username"];

                    } else if (isset($filters["user"])) {

                        $username = $filters["user"];
                    }
                ?>
                <input type="text" name="username" value="<?php echo $username; ?>" />
                <img src="<?php echo base_url()."img/help.png";?>" class="help" title="Recoger todos los mensajes de un usuario. Los filtros de texto o hashtag no se aplican en estos mensajes" />
            </p>
            <p><?php echo $this->lang->line("idioma"); ?></p>
            <p>
            <?php
                $selected = "";

                if (isset($_POST["language"])) {

                    $selected = $_POST["language"];

                } else if (isset($filters["language"])) {

                    $selected = $filters["language"];
                }
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
                    <?php
                        if (in_array($account->getId(), $activeUserPlugins)) {
    
                           $checked = 'checked="checked"';
    
                        } else {
    
                           $checked = "";
                        }
                    ?>
                    <input type="checkbox" name="plugins[]" value="<?php echo $account->getId(); ?>" <?php echo $checked; ?> />
                    <?php echo $account->getAlias(); ?> (<?php echo $account->getName(); ?>)
                </li>
                <?php }//endforeach ?>
    
            </ul>
            <?php
                $filters = $obj->getFilters();
            ?>
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
                        <option value="me" <?php if (isset($filters["autor"]) and $filters["autor"] == "me") echo "selected='selected' "; ?>>
                            <?php echo $this->lang->line("me");?>
                        </option>
                        <option value="notMe" <?php if (isset($filters["autor"]) and $filters["autor"] == "notMe") echo "selected='selected' "; ?>>
                            <?php echo $this->lang->line("notMe");?>
                        </option>
                    </select>
                </li>
                <li style="display:none;"><br /></li>
                <li style="display:none;">
                    <?php echo $this->lang->line("filterContent");?>
                    <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("filterContentInfo"); ?>" /><br />
                    <input type="text" name="filterByWord" style="width:600px;" value="<?php if(isset($filters["content"])) echo $filters["content"]; ?>" />
                </li>
            </ul>
            <br />
            <p class="error"><?php echo $obj->getErrors("plugins"); ?></p>
        </div>
        <p class="error"><?php echo $obj->getErrors("form"); ?></p>
        <input type="submit" />
    <?php
        echo form_close();
    ?>
</div>