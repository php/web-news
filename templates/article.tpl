<div id="layout">
 <aside class="tips">
  <table>
   <tbody>
    <tr>
     <td width="30%">
      <div class="info"><?php echo $lang['author']; ?></div>
     </td>
     <td>
      <?php echo format_author($headers['from']); ?>
     </td>
    </tr>
    <tr>
     <td nowrap="nowrap">
      <div class="info"><?php echo $lang['news_groups']; ?></div>
     </td>
     <td>
      <?php echo format_newsgroups($headers['newsgroups']); ?>
     </td>
    </tr>
    <tr>
     <td>
      <div class="info"><?php echo $lang['date']; ?></div>
     </td>
     <td>
      <?php echo format_date($headers['date']); ?>
     </td>
    </tr>
<?php if($references): ?>
    <tr>
     <td>
      <div class="info"><?php echo $lang['references']; ?></div>
     </td>
     <td>
      <?php echo format_references($references); ?>
     </td>
    </tr>
<?php endif; ?>
   </tbody>
  </table>
<?php if($attachments): ?>
  <h3><?php echo $lang['attachments']; ?></h3>
  <ul>
   <?php echo $attachment_list; ?>
  </ul>
<?php endif; ?>
 </aside>
 <section class="layout-content">
  <h1><?php $svn = false; echo format_subject($headers['subject'], $svn); ?></h1>
<?php if($svn): ?>
  <pre><?php echo $message . $signature; ?></pre>
<?php else: ?>
  <p><?php echo format_message($message); ?></p>
  <p><?php echo nl2br($signature); ?></p>
<?php endif; ?>

<?php if($prev || $next): ?>
  <div class="navigation">
<?php if($prev): ?>
   <div class="left">
    <a href="<?php echo SERVER_PATH . $group; ?>/<?php echo $id - 1; ?>/">&laquo; <?php echo $lang['previous']; ?></a>
   </div>
<?php endif; ?>
<?php if($next): ?>
   <div class="right">
    <a href="<?php echo SERVER_PATH . $group; ?>/<?php echo $id + 1; ?>/"><?php echo $lang['next']; ?> &raquo;</a>
   </div>
<?php endif; ?>
   <div class="clear"></div>
  </div>
<?php endif; ?>
 </section>
</div>