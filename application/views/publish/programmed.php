<?php
    $this->load->view("sidebar");
    $this->load->helper("form");
    $this->lang->load('publish');
?>
<div id="msgs">
<?php
    $this->load->view("publish/tabs");
?>
<div id="account-list">
    <ul>
    <?php
    if(count($msgs) == 0) {
    ?>
        <li>
            <?php echo $this->lang->line("noMsg"); ?>
        </li>
    <?php
    }

    foreach ($msgs as $msg) {

        $targetAccount = null;

        foreach($accounts as $account) {

            if ($account->getId() == $msg["idUP"]) {

                $targetAccount = $account;
            }
        }

        if (!is_null($targetAccount)) {
    ?>
        <li>
            <strong><?php echo $msg["title"]; ?></strong><br />
            <?php echo $this->lang->line("in"); ?> <i><strong><?php echo $targetAccount->getAlias(); ?></strong></i>
             <?php echo $this->lang->line("programmedTimestamp")." ".$msg["publishDate"]; ?>
            <div><br />
                <a href="<?php echo site_url("publish/edit/".$msg["id"]); ?>""><?php echo $this->lang->line("edit"); ?></a> |
                <a href="<?php echo site_url("publish/now/".$msg["id"]); ?>"><?php echo $this->lang->line("publishNow"); ?></a> |
                <a href="<?php echo site_url("publish/delete/".$msg["id"]); ?>"><?php echo $this->lang->line("delete");?></a>
            </div>
        </li>
    <?php
        }//end if
    } //endforeach ?>
    </ul>
    </div>
</div>