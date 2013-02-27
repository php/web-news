<div id="layout">
 <form action="<?php echo SERVER_PATH; ?>preferences/" method="post" class="preferences">
 <aside class="tips">
  <h3><?php echo $lang['preferences']; ?></h3>
  <p>
   <?php echo $lang['prefs_paragraph_1']; ?>
  </p>
  <p>
   <?php echo $lang['prefs_paragraph_2']; ?>
  </p>
  <p>
   &nbsp;
  </p>
  <p align="center">
   <input type="submit" name="save" value="<?php echo $lang['prefs_save']; ?>" />
  </p>
<?php if(isset($_POST['save'])): ?>
  <div id="save_success">
   <p>
    &nbsp;
   </p>
   <p align="center" class="box">
    <?php echo $lang['prefs_saved']; ?>
   </p>
  </div>

  <script type="text/javascript">
  function save_success() {
    var p = document.getElementById('save_success');
    p.parentNode.removeChild(p);
  }

  setTimeout(save_success, 3000);
  </script>
<?php endif; ?>
 </aside>
 <section class="layout-content">
  <h1><?php echo $lang['prefs_language']; ?></h1>
  <p>
   <?php echo $lang['prefs_language_paragraph']; ?>
  </p>
  <p>
   <select name="pref_language" onchange="change_flag(this);">
<?php foreach($lang->getAvailableLanguages() as $code): ?>
    <option value="<?php echo $code; ?>"<?php if($prefs->language == $code){ echo ' selected="selected"'; } ?>><?php echo $lang['lang_' . $code]; ?></option>
<?php endforeach; ?>
   </select>
   <img src="<?php echo SERVER_PATH; ?>images/flag_<?php echo $prefs->language; ?>.png" alt="<?php echo $lang['lang_' . $prefs->language]; ?>" id="flag" />
  </p>

  <script type="text/javascript">
  function change_flag(selector) {
      document.getElementById('flag').src = '<?php echo SERVER_PATH; ?>images/flag_' + selector.options[selector.selectedIndex].value + '.png';
  }
  </script>

  <h1><?php echo $lang['prefs_miniflags']; ?></h1>
  <p>
   <?php echo $lang['prefs_miniflags_paragraph']; ?>
  </p>
  <p>
   <select name="pref_miniflags">
    <option value="0"<?php if($prefs->miniflags == 0){ echo ' selected="selected"'; } ?>><?php echo $lang['prefs_disabled']; ?></option>
    <option value="1"<?php if($prefs->miniflags == 1){ echo ' selected="selected"'; } ?>><?php echo $lang['prefs_enabled']; ?></option>
   </select>
  </p>

  <h1><?php echo $lang['prefs_display_popular_groups']; ?></h1>
  <p>
   <?php echo $lang['prefs_groups_paragraph']; ?>
  </p>
  <p>
   <select name="pref_popular_groups">
    <option value="0"<?php if($prefs->popular_groups == 0){ echo ' selected="selected"'; } ?>><?php echo $lang['prefs_disabled']; ?></option>
    <option value="1"<?php if($prefs->popular_groups == 1){ echo ' selected="selected"'; } ?>><?php echo $lang['prefs_enabled']; ?></option>
    <option value="2"<?php if($prefs->popular_groups == 2){ echo ' selected="selected"'; } ?>><?php echo $lang['prefs_enabled_index_only']; ?></option>
   </select>
  </p>

  <h1><?php echo $lang['email']; ?></h1>
  <p>
   <?php echo $lang['prefs_email_paragraph']; ?>
  </p>
  <p>
   <input type="text" name="pref_email_address" value="<?php echo htmlspecialchars($prefs->email_address, ENT_QUOTES); ?>" />
  </p>
 </section>
 </form>
</div>