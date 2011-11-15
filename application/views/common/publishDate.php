<span class="time" rel="<?php echo strtotime($message->getPublishDate()); ?>">
<?php
    $this->lang->load("app");
    $timeDiff = (time()-strtotime($message->getPublishDate()));

    $oneMonth = 60*60*24*31;
    $oneDay = 60*60*24;
    $oneHour = 60*60;

    if ($timeDiff > $oneMonth) {

        echo $this->lang->line("moreThanMonth");

    } else if ($timeDiff > $oneDay) {

        $days = (int) ($timeDiff/$oneDay);

        if ($days == 1) {

            echo $days.$this->lang->line("day");

        } else {

            echo $days.$this->lang->line("days");
        }

    } else if ($timeDiff > $oneHour) {

        $hours = (int) ($timeDiff/$oneHour);

        if ($hours == 1) {

            echo $hours.$this->lang->line("hour");

        } else {

            echo $hours.$this->lang->line("hours");
        }

    } else {

       $minutes = (int) ($timeDiff/60);

       if ($minutes == 1) {

            echo $minutes.$this->lang->line("minute");

       } else {

            echo $minutes.$this->lang->line("minutes");
       }
    }
?>
</span>