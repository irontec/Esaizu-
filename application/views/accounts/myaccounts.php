<?php $this->load->view("sidebar"); ?>
<div id="msgs">
    <?php
        $this->load->view("accounts/tabs");
        $this->lang->load('accounts');
    ?>
    <div id="account-list">
    <ul>
    <?php
    if (count($accounts) == 0) {
    ?>
        <p>Ninguna cuenta añadida</p>
    <?php
    } else {

        $lastPlugin = "";
        foreach ($accounts as $account) {

            if($lastPlugin == "" or $lastPlugin != $account->getName()) {
        ?>
            <li class="account-type">
                <h3><?php echo $account->getName(); ?> <span class="myAccounts">[+]</span></h3>
            </li>
            
        <?php
            } //end if
        ?>
        <li class="account">
            <h3><?php echo $account->getAlias(); ?></h3>
            <?php
                if($account->getEnabled() == 1) {
                ?>
                     <a href="<?php echo site_url("accounts/disable/".$account->getId());?>">
                        <?php echo $this->lang->line('disable'); ?>
                     </a>
                <?php
                } else {
                ?>
                    <a href="<?php echo site_url("accounts/enable/".$account->getId());?>">
                        <?php echo $this->lang->line('enable'); ?>
                    </a>
                    <a href="<?php echo site_url("accounts/delete/".$account->getId());?>">
                        <?php echo $this->lang->line('delete'); ?>
                    </a>
                <?php
                } //end if
            ?>
        </li>
        <?php
            $lastPlugin = $account->getName();
        } //endforeach
    }
    ?>
    </ul>
    </div>
</div>
