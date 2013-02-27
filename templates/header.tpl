<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
 <title><?php echo $lang['title']; ?></title>

 <meta charset="utf-8" />

 <link rel="search" type="application/opensearchdescription+xml" href="http://php.net/phpnetimprovedsearch.src" title="Add PHP.net search" />
 
 <link rel="stylesheet" type="text/css" href="<?php echo SERVER_PATH; ?>styles/reset.css" media="all" /> 
 <link rel="stylesheet" type="text/css" href="<?php echo SERVER_PATH; ?>styles/theme.css" media="screen" />
 <link rel="stylesheet" type="text/css" href="<?php echo SERVER_PATH; ?>styles/home.css" media="screen" />
 <link rel="stylesheet" type="text/css" href="<?php echo SERVER_PATH; ?>styles/news.css" media="screen" />
 
 <!--[if lte IE 7]>
 <style type="text/css">
  #layout { background: white; }
 </style>
 <![endif]-->
</head>
<body>

<nav id="headnav">
 <ul id="headmenu">
  <li id="headhome">
    <a href="<?php echo SERVER_PATH; ?>" class="menu-link"><?php echo $lang['home']; ?></a>
  </li>
  <li>
    <a href="<?php echo SERVER_PATH; ?>" class="menu-link"><?php echo $lang['news_groups']; ?></a>
  </li>
  <li>
    <a href="<?php echo SERVER_PATH; ?>preferences/" class="menu-link"><?php echo $lang['preferences']; ?></a>
  </li>
  <li>
    <a href="http://php.net/" class="menu-link">PHP.net</a>
  </li>
  <li>
    <a href="http://php.net/mailing-lists.php" class="menu-link"><?php echo $lang['mailing_lists']; ?></a>
  </li>
 </ul>
 <br style="clear: both;" />
</nav>

<?php if($prefs instanceof Preferences && ($prefs->popular_groups == 1 || ($prefs->popular_groups == 2 && SCRIPT_NAME == 'index.php'))): ?>
<div id="mega-drop-down">
 <div id="menu-container">
    <div class="children">
    <div class="children-1">
    <div class="children-2">
    <dl>
      <dt><a href="#" onclick="return false;"><?php echo $lang['popular_group_core']; ?></a></dt>
        <dd><a href="<?php echo SERVER_PATH; ?>php.announce/">php.announce</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.bugs/">php.bugs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.cvs/">php.cvs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.internals/">php.internals</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.internals.win/">php.internals.win</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.webmaster/">php.webmaster</a></dd>

      <dt><a href="#" onclick="return false;"><?php echo $lang['popular_group_documentation']; ?></a></dt>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc/">php.doc</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.bugs/">php.doc.bugs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.cvs/">php.doc.cvs</a></dd>
    </dl>
    <dl>
      <dt><a href="#" onclick="return false;"><?php echo $lang['popular_group_translations']; ?></a></dt>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.de/">php.doc.de</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.es/">php.doc.es</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.fa/">php.doc.fa</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.fr/">php.doc.fr</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.ja/">php.doc.ja</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.pl/">php.doc.pl</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.pt-br/">php.doc.pt-br</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.ro/">php.doc.ro</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.tr/">php.doc.tr</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.doc.zh/">php.doc.zh</a></dd>
    </dl>
    <dl>
      <dt><a href="#" onclick="return false;">PEAR</a></dt>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear/">php.pear</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.bugs/">php.pear.bugs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.core/">php.pear.core</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.cvs/">php.pear.cvs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.dev/">php.pear.dev</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.doc/">php.pear.doc</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.general/">php.pear.general</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.qa/">php.pear.qa</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pear.webmaster/">php.pear.webmaster</a></dd>
    </dl>
    <dl>
      <dt><a href="#" onclick="return false;">PECL</a></dt>
        <dd><a href="<?php echo SERVER_PATH; ?>php.apc.dev/">php.apc.dev</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pecl.cvs/">php.pecl.cvs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.pecl.dev/">php.pecl.dev</a></dd>

      <dt><a href="#" onclick="return false;">PHP-GTK</a></dt>
        <dd><a href="<?php echo SERVER_PATH; ?>php.gtk/">php.gtk</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.gtk.cvs/">php.gtk.cvs</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.gtk.dev/">php.gtk.dev</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.gtk.doc/">php.gtk.doc</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.gtk.general/">php.gtk.general</a></dd>
        <dd><a href="<?php echo SERVER_PATH; ?>php.gtk.webmaster/">php.gtk.webmaster</a></dd>
    </dl>
    <br style="clear: both;" />
    </div>
    </div>
    </div>
 </div>
</div>
<?php endif; ?>