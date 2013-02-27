<div id="layout">
 <section class="layout-content">
  <h1>
   <?php echo $group; ?>

   <div style="float: right;">
    <a href="<?php echo SERVER_PATH; ?>rss/<?php echo $group; ?>/">
     <img src="<?php echo SERVER_PATH; ?>images/icon_rss_big.png" alt="RSS" /></a>
    <a href="<?php echo SERVER_PATH; ?>rdf/<?php echo $group; ?>/">
     <img src="<?php echo SERVER_PATH; ?>images/icon_rdf_big.png" alt="RDF" /></a>
   </div>
   <div style="clear: right;"></div>
  </h1>
  <div class="table-wrapper">
   <table>
    <thead>
     <?php echo $navigation; ?>
    </thead>
    <tbody>
     <tr class="heading">
      <td class="column_id">#</td>
      <td class="column_subject"><?php echo $lang['subject']; ?></td>
      <td class="column_author"><?php echo $lang['author']; ?></td>
      <td class="column_date"><?php echo $lang['date']; ?></td>
      <td class="column_lines"><?php echo $lang['lines']; ?></td>
     </tr>
     <?php echo $groups; ?>
    </tbody>
    <tfoot>
     <?php echo $navigation; ?>
    </tfoot>
   </table>
  </div>

  <h1><?php echo $lang['groups_subscription_options']; ?></h1>
  <form action="<?php echo SERVER_PATH; ?>subscribe/<?php echo $group; ?>/" method="post">
  <p>
   <?php echo $lang['groups_subscription_info']; ?>
  </p>
  <p>
   <input type="text" name="subscription_id" value="<?php echo htmlspecialchars($prefs->email_address, ENT_QUOTES); ?>" />
   <input type="submit" name="subscribe" value="<?php echo $lang['subscribe']; ?>" />
   <input type="submit" name="unsubscribe" value="<?php echo $lang['unsubscribe']; ?>" />
  </p>
  </form>
 </section>
</div>