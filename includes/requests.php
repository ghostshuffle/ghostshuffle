<?php
  $ip_address_file_path = str_replace(".", "/", $_SERVER["REMOTE_ADDR"]);
  $ip_address_directory_path = substr($ip_address_file_path, 0, strrpos($ip_address_file_path, "/")) . "/";

  if (
    file_exists("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) == true &&
    filesize("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) >= 30
  ) {
    echo "{\"messages\": {\"global\": \"Limit of 30 visits per IP address per minute is exceeded.\"}}";
    exit;
  }

  if (
    file_exists("/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . $ip_address_file_path . "/l") == true &&
    (filemtime("/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . $ip_address_file_path . "/l") > (time() - 1209600)) == true
  ) {
    http_response_code(403);
    echo "GhostProxies.com confirmed " . $_SERVER["REMOTE_ADDR"] . " is a high-risk IP address originating from a recently-opened proxy server. It was blocked automatically to mitigate malicious activity.";
    exit;
  }

  if (is_dir("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_directory_path) == false) {
    mkdir("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_directory_path, 0777, true);
    file_put_contents("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path, "1", FILE_APPEND);
  } elseif (
    file_exists("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) == false ||
    filesize("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) < 30
  ) {
    file_put_contents("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path, "1", FILE_APPEND);
  }
?>
