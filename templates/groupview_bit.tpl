<tr>
 <td class="column_id"><a href="http://<?php echo SERVER_NAME . SERVER_PATH . $group; ?>/<?php echo $id; ?>/"><?php echo $id; ?></a></td>
 <td class="column_subject"><a href="http://<?php echo SERVER_NAME . SERVER_PATH . $group; ?>/<?php echo $id; ?>/"><?php echo format_subject($subject); ?></a></td>
 <td class="column_author"><?php echo format_author($author); ?></td>
 <td class="column_date"><?php echo format_date($date); ?></td>
 <td class="column_lines"><?php echo $lines; ?></td>
</tr>