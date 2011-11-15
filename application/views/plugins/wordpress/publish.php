<?php
$this->lang->load("wordpress");
$this->lang->load("help");
?>
<p><?php echo $this->lang->line("titular");?></p>
<p>
    <input type="text" name="wp_titular" value="<?php echo isset($post["wp_titular"]) ? $post["wp_titular"] : ""; ?>" />
    <p class="error"><?php $msg->getErrors("titular");?></p>
</p>
<p><?php echo $this->lang->line("mensaje");?></p>
<p>
    <textarea name="wp_content" class="html"><?php echo isset($post["wp_content"]) ? $post["wp_titular"] : ""; ?></textarea>
    <p class="error"><?php $msg->getErrors("wp_content");?></p>
</p>
<p>
    <?php echo $this->lang->line("publicarEnCategoria");?>:

    <?php
    foreach ($accounts as $account) {

        if ($account->getClassName() != "Wordpress" or !is_array($account->getMetadata("categories"))) {
            continue;
        }
    ?>

        <select name="wp_categories" rel="<?php echo $account->getId(); ?>">
        <?php foreach ($account->getMetadata("categories") as $category) { ?>
            <option value="<?php echo $category["categoryId"]; ?>"><?php echo $category["categoryName"]; ?></option>
        <?php }//endforeach ?>
        </select>
    <?php }//endforeach ?>
</p>
<p>
    <?php echo $this->lang->line("tags");?>:
    <input type="text" name="tags" value="<?php echo $account->getMetadata("tags"); ?>" />
    <img src="<?php echo base_url()."img/help.png";?>" class="help" title="<?php echo $this->lang->line("msgTagsHelp");?>" />
</p>