<?php
    $this->load->view("sidebar");
    $this->load->helper("form");
    $this->lang->load('accounts');
?>
<div id="msgs">
<?php
    $this->load->view("publish/tabs");
    if(isset($accounts) and count($accounts) > 0 ) {

        echo form_open("publish/send", array("enctype" => "multipart/form-data"));
    ?>
        <p><?php echo $this->lang->line("pusblishIn"); ?></p>
        <p>
        <select name="publishIn">
            <?php
                foreach ($accounts as $account) {

                if (isset($_POST["publishIn"]) and $_POST["publishIn"] == $account->getId()) {

                    $selected = "selected = 'selected'";

                } else {

                    $selected = "";
                }
            ?>
                <option <?php echo $selected; ?> rel="<?php echo $account->getIdP(); ?>" value="<?php echo $account->getId(); ?>"><?php echo $account->getAlias(); ?> </option>
            <?php }//endforeach ?>
        </select>
        </p>
        <?php
        $i=0;
        foreach ($plugins as $plugin) {

            $style = "";
            if ($i !== 0) {
                $style = "display:none;";
            }

            echo '<div class="accounts '.$plugin["name"].'" rel="'.$plugin["id"].'" style="'.$style.'">';
                $this->load->view($plugin["publishView"]);
            echo "</div>";
            $i++;

        }//endforeach

        if(count($accounts) > 1) {
            echo "<p>".$this->lang->line("referenceIn")."</p>";
        }

        $i=0;
        foreach ($accounts as $account) {

            $reference = false;

            foreach ($plugins as $plugin) {

                if ($account->getName() == $plugin["name"]) {

                    $reference = $plugin["showReferenceButton"];
                    break;
                }
            }

            if ($reference === true) {
        ?>
            <div class="reference <?php echo $account->getId(); ?>">
                <input type="checkbox" value="<?php echo $account->getId(); ?>" name="referenceIn[]" />
                <?php echo $account->getAlias(); ?>
            </div>
        <?php
            } //end if
        }//endforeach
        ?>
        <br />
        <p>
        	<textarea class="hidden" id="datepickerOptions"><?php
        		$this->lang->load("datepicker");
        		$datepickerOptions = $this->lang->line("options");

        		$dateFormat = str_replace(
        		  array("dd","mm","yy"),
        		  array("d","m","Y"),
        		  $datepickerOptions["dateFormat"]
        		);

        		echo json_encode($datepickerOptions);
        	?></textarea>

            <input type="checkbox" name="envio_programado" value="1" <?php echo isset($_POST["envio_programado"]) ? ' checked="checked" ' : ''; ?>/>
            <?php echo $this->lang->line("envioProgramado"); ?>
            <input type="text" id="dia_programado" name="dia_programado"
            value="<?php echo isset($_POST["dia_programado"]) ? $_POST["dia_programado"] : date($dateFormat, time());?>" size="8" />
            a las <input type="text" name="hora_programada" id="hora_programada" value="<?php echo isset($_POST["hora_programada"]) ? $_POST["hora_programada"] : date("H:i", time()+60*60);?>"  size="6" />
            <input class="hidden" type="text" id="dia_programado_dbformat" name="dia_programado_dbformat"
            value="<?php echo isset($_POST["dia_programado_dbformat"]) ? $_POST["dia_programado_dbformat"] : date("Y-m-d", time());?>" size="8" />
        </p>
        <p class="error">
        	<?php echo $msg->getErrors("envio_programado"); ?>
        </p>
        <p class="error">
            <?php echo $msg->getErrors("form"); ?>
        </p>
        <p>
            <input type="submit" disabled="disabled" />
        </p>
        <?php
            echo form_close();
    
        } else {
        ?>
            <div id="account-list" style="min-height:100px;">
        <?php
        
            $this->lang->load("accounts");
            echo sprintf($this->lang->line("noAccount"),site_url("accounts/newaccount"));
        }
        ?>
            </div>

</div>