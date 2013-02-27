<div id="layout">
 <section class="layout-content">
  <h1><?php echo $title; ?></h1>
  <p><?php echo $message; ?></p>
  <p align="right">
   <a href="<?php echo str_replace('"', '\"', $location); ?>"><?php echo $lang['redirect']; ?></a>
  </p>
 </section>
</div>

<script type="text/javascript">
function redirect() {
    location.href = '<?php echo str_replace('\'', '\\\'', $location); ?>';
}

setTimeout(redirect, 3000);
</script>