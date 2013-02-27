<tr>
 <td nowrap="nowrap" class="column_previous">
<?php if($start > $first): ?>
  <a href="<?php echo SERVER_PATH . $group; ?>/start/<?php echo max($start - 20, $first); ?>/">&laquo <?php echo $lang['previous']; ?></a>
<?php else: ?>
  &nbsp;
<?php endif; ?>
 </td>
 <td colspan="3">
  <?php echo $lang->format('showing_x_y_of_z', $start, min($start + 19, $last), $last - $first + 1); ?>
 </td>
 <td nowrap="nowrap" class="column_next">
<?php if($start + 20 <= $last): ?>
  <a href="<?php echo SERVER_PATH . $group; ?>/start/<?php echo min($start + 20, $last - 19); ?>/"><?php echo $lang['next']; ?> &raquo;</a>
<?php else: ?>
  &nbsp;
<?php endif; ?>
 </td>
</tr>