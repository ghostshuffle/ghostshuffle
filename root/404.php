<?php
  http_response_code(404);
  $parameters = array(
    "title" => "Page Not Found"
  );
  require("/var/www/ghostproxies.com/includes/header.php");
?>
  <main>
    <h1>Page Not Found</h1>
    <p class="no-margin-bottom">The server returned a 404 error because this page doesn't exist.</p>
  </main>
  <?php
  require("/var/www/ghostproxies.com/includes/javascript.php");
  require("/var/www/ghostproxies.com/includes/footer.php");
?>
