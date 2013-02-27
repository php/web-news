<li>
<?php if($prefs->miniflags): ?>
<?php $flag = end(explode('.', $group)); if(substr($group, 0, 8) == 'php.doc.' && is_file('images/flag_' . $flag . '.png')): ?>
 <img src="images/flag_<?php echo $flag; ?>.png" alt="" />
<?php else: ?>
 <img src="images/empty_flag.gif" alt="" />
<?php endif; ?>
<?php endif; ?>
 <a href="<?php echo SERVER_PATH . $group; ?>/"><?php echo $group; ?></a>

 <div style="float: right;">
  <?php echo number_format($high - $low + 1); ?>

  <a href="<?php echo SERVER_PATH; ?>rss/<?php echo $group; ?>/">
   <img src="<?php echo SERVER_PATH; ?>images/icon_rss.png" alt="RSS" /></a> 
  <a href="<?php echo SERVER_PATH; ?>rdf/<?php echo $group; ?>/">
   <img src="<?php echo SERVER_PATH; ?>images/icon_rdf.png" alt="RDF" /></a>
 </div>
 <div style="clear: right;"></div>
</li>