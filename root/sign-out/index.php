<?php
  session_start();

  if (empty($_SESSION["id"]) == false) {
    unlink("/var/www/ghostproxies.com/database/sessions/" . $_SESSION["id"]);
    unset($_SESSION["id"]);
    unset($_SESSION["user_id"]);

    if (isset($_GET["invalid_session"]) == true) {
      header("Location: /sign-in/?invalid_session", true, 301);
    } else {
      header("Location: /sign-in/", true, 301);
    }
  } else {
    header("Location: /sign-in/", true, 301);
  }

  exit;
?>
