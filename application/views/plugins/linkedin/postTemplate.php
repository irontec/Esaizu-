<share>
  <comment><?php echo $comment; ?></comment>
  <?php if (isset($title)) { ?>
  <content>
     <title><?php echo $title; ?></title>
     <submitted-url><?php echo $url; ?></submitted-url>
     <?php if (isset($img)) { ?>
        <submitted-image-url><?php echo $img; ?></submitted-image-url>
     <?php } //endif ?>
     <description><?php echo $description; ?></description>
  </content>
  <?php } //end if ?>
  <visibility>
     <code><?php echo $private; ?></code>
  </visibility>
</share>