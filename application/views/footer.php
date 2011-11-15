<?php
    if(isset($renders)) {
    ?>
    <div class="pagination">
    <?php
    $i=0;
    foreach ($columns as $column ) {

    	if ($i== 0 and $column->getMinimized() == 1) {

    		$class = "hidden";

    	} else if ($i == 0) {

    		$class = "selected";

    	} else if ($column->getMinimized() != 1) {

            $class = "";

    	} else {

    		$class="hidden";
    	}
    ?>
        <a href="#" class="<?php echo $class; ?> <?php echo $column->getId(); ?>" rel="<?php echo $i; ?>"
        title="<?php echo $column->getIdentificador(); ?>" >&bull;</a>
    <?php
        $i++;
    }
    ?>
    </div>
    <?php
    }
?>

<span style="position: absolute; right: 10px; bottom: 4px;background-color:#77AA00;padding:2px;">
    by <a target="_blank" href="http://www.esle.eu/" style="color:white;">ESLE</a>
</span>
<span class="hidden">
Memory usage <?php echo $this->benchmark->memory_usage();?> | Elapsed time <?php echo $this->benchmark->elapsed_time();?>
</span>
