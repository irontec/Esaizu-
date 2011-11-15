<?php
    $this->lang->load("admin");
?>
<div id="msgs">
    <div class="tabs">
        <ul>
            <li class="<?php echo isset($items) ? "selected" : ""; ?>">
                <a href="<?php echo site_url("admin"); ?>">
                    <?php echo $this->lang->line("admin_users");?>
                </a>
            </li>
            <li class="<?php echo (isset($plugins) or isset($plugin)) ? "selected" : ""; ?>">
                <a href="<?php echo site_url("admin/plugins"); ?>">
                    <?php echo $this->lang->line("admin_plugins");?>
                </a>
            </li>
        </ul>
    </div>
    
    <?php
    if (isset($items)) {
    ?>
    <div id="account-list">
        <table>
        <thead>
            <th><?php echo $this->lang->line("username");?></th>
            <th><?php echo $this->lang->line("email");?></th>
            <th></th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach($items as $user) { ?>
                <tr>
                    <td><?php echo $user->getUserName(); ?></td>
                    <td><?php echo $user->getEmail();?></td>
                    <td>
                        <?php  if($user->getActivated() == 0) { ?>
                            <a href="<?php echo site_url("admin/accept/")."/".$user->getId();?>">
                                <?php echo $this->lang->line("accept");?>
                            </a>
                        <?php }//endif ?>
                    </td>
                    <td>
                        <?php  if($user->getActivated() == 1) { ?>
                            <a href="<?php echo site_url("admin/deny/")."/".$user->getId();?>">
                                <?php echo $this->lang->line("deny");?>
                            </a>
                        <?php }//endif ?>
                    </td>
                </tr>
            <?php } //endforeach ?>
        </tbody>
        </table>
        
        <?php echo $this->pagination->create_links(); ?>
    </div>
    <?php } else if (isset($plugins)) { ?>
    <div id="account-list">
        <table width="50%" style="text-align:left;">
        <thead>
            <th><?php echo $this->lang->line("pluginName");?></th>
            <th><?php echo $this->lang->line("activated");?></th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach($plugins as $plugin) { ?>
                <tr>
                    <td><?php echo ucfirst($plugin->getName()); ?></td>
                    <td>
                        <?php echo $plugin->getActivated() == "1" ? $this->lang->line("yes") : $this->lang->line("no"); ?>
                    </td>
                    <td>
                        <a href="<?php echo site_url("admin/edit/".$plugin->getId()); ?>">
                            <?php echo $this->lang->line("edit"); ?>
                        </a>
                    </td>
                </tr>
            <?php } //endforeach ?>
        </tbody>
        </table>
    </div>
    <?php } else if (isset($plugin)) { ?>

        <form method="POST" action="<?php echo site_url("admin/update/".$plugin->getId());?>">
            <a href="<?php echo site_url("admin/plugins"); ?>"><?php echo $this->lang->line("Volver"); ?></a>
            <br />
            <table style="width:70%;margin-top:15px;" style="text-align:left;">
            <tbody>
                <tr>
                    <td> <?php echo $this->lang->line("pluginName"); ?> </td>
                    <td>
                        <?php echo ucfirst($plugin->getName()); ?>
                    </td>
                </tr>
                <tr>
                    <td> <?php echo $this->lang->line("activated"); ?> </td>
                    <td>
                        <select name="activated">
                            <option value="0" <?php if($plugin->getActivated() == 0) echo "selected"; ?>><?php echo $this->lang->line("no")?></option>
                            <option value="1" <?php if($plugin->getActivated() == 1) echo "selected"; ?>><?php echo $this->lang->line("yes"); ?></option>
                        </select>
                        <?php if (isset($errors["activated"])) echo '<p class="error">'.$errors["activated"].'</p>'; ?>
                    </td>
                </tr>
                <tr>
                    <td> <?php echo $this->lang->line("upFrecuency"); ?> </td>
                    <td>
                        <input type="text" name="updateFrecuency" value="<?php echo $plugin->getUpdateFrecuency(); ?>" />
                        <?php if (isset($errors["updateFrecuency"])) echo '<p class="error">'.$errors["updateFrecuency"].'</p>'; ?>
                    </td>
                </tr>
            </tbody>
            </table>
            <br />
            <input type="submit" value="<?php echo $this->lang->line("save"); ?>" />
        </form>

    <?php }//endif ?>
    </div>
</div>