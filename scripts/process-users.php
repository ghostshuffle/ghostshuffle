<?php
  $users = array_values(array_filter(array_diff(scandir("/var/www/ghostproxies.com/database/users/new/"), array("..", "."))));
  $i = count($users);

  while ($i != 0) {
    $i--;

    if (
      file_exists("/var/www/ghostproxies.com/database/users/new/" . $users[$i]) == true &&
      (time() - filectime("/var/www/ghostproxies.com/database/users/new/" . $users[$i])) >= 86400
    ) {
      $session_ids = file_get_contents("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/session-ids");

      if ($session_ids != false) {
        while (empty($session_ids) == false) {
          $j = strpos($session_ids, " ");
          $session_id = substr($session_ids, 0, $j);

          if (file_exists("/var/www/ghostproxies.com/database/sessions/" . $session_id) == true) {
            unlink("/var/www/ghostproxies.com/database/sessions/" . $session_id);
          }

          $session_ids = substr($session_ids, $j + 1);
        }
      }

      if (
        (
          file_exists("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/credits-count") == false ||
          unlink("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/credits-count") == true
        ) &&
        (
          file_exists("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/id") == false ||
          unlink("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/id") == true
        ) &&
        (
          file_exists("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/name") == false ||
          unlink("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/name") == true
        ) &&
        (
          file_exists("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/password-hash") == false ||
          unlink("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/password-hash") == true
        ) &&
        (
          file_exists("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/session-ids") == false ||
          unlink("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/session-ids") == true
        ) &&
        (
          is_dir("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/") == false ||
          rmdir("/var/www/ghostproxies.com/database/users/" . $users[$i] . "/") == true
        )
      ) {
        unlink("/var/www/ghostproxies.com/database/users/new/" . $users[$i]);
      }
    }
  }
?>
