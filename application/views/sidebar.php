<?php
    $this->lang->load("app");
?>
<div id="sidebar">
    <ul>
        <li <?php if( in_array($this->uri->segment(1), array("","app"))) echo 'class="selected" '; ?>>
            <a href="<?php echo site_url();?>">
                <img src="<?php echo base_url()."img/inicio.png";?>"
                alt="Inicio" title="Inicio" />
                <?php echo $this->lang->line("inicio"); ?>
            </a>
        </li>
        <li <?php if(in_array($this->uri->segment(1), array("columns"))) echo 'class="selected" '; ?>>
            <a href="<?php echo site_url("columns");?>">
                <img src="<?php echo base_url()."img/columnas.png";?>"
                alt="Columnas" title="Columnas" />
                <?php echo $this->lang->line("columnas"); ?>
            </a>
        </li>
        <li <?php if(in_array($this->uri->segment(1), array("accounts"))) echo 'class="selected" '; ?>>
            <a href="<?php echo site_url("accounts/");?>">
                <img src="<?php echo base_url()."img/cuentas.png";?>"
                alt="<?php echo $this->lang->line("manageAccounts");?>"
                title="Cuentas" />
                <?php echo $this->lang->line("cuentas"); ?>
            </a>
        </li>
        <li <?php if(in_array($this->uri->segment(1), array("publish"))) echo 'class="selected" '; ?>>
            <a href="<?php echo site_url("publish");?>">
                <img src="<?php echo base_url()."img/publicar.png";?>"
                alt="Publicar" title="Publicar" />
                <?php echo $this->lang->line("publicar"); ?>
            </a>
        </li>
        <li class="separator hidden">
            <hr />
        </li>
        <?php
        if (isset($columns) and in_array($this->uri->segment(1), array("", "app")) ) {

            foreach ($columns as $column) {
                          
                if($column->getMinimized() == 1) {

                    if ($column->getType() == "search") {

                        $unreadCounter = 0;
                        
                    } else {
                        
                        $lastCheck = strtotime($column->getLastUserCheck());
                        $unreadCounter = 0;
    
                        foreach ($column->getMessages() as $message) {
    
                             if ($lastCheck < strtotime($message->getPublishDate())) {
    
                                 $unreadCounter++;
                             }
                        }
                    }

                    $class = $unreadCounter == 0 ? " hidden" : "";
            ?>
                <li class="minimized texto">
                    <a class="<?php echo $column->getid(); ?>" href="<?php echo site_url("app/restore/".$column->getid());?>" title="<?php echo $column->getIdentificador(); ?>">
                        <span class="unread<?php echo $class; ?>"><?php echo $unreadCounter; ?></span>
                        <p class="columnName" ><?php echo $column->getIdentificador(); ?></p>
                    </a>
                    
                </li>
            <?php
                } //end if
            }//endforeach
        }//endif
        ?>
        <li class="minimized texto prototype hidden">
            <a class="#id#" href="<?php echo site_url("app/restore/");?>" title="#title#">
                <span class="unread hidden">0</span>
                <p class="columnName" >#title#</p>
            </a>
            
        </li>
    </ul>
</div>