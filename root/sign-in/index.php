<?php
  if (empty($_POST["encoded_data"]) == false):
    header("Content-type: application/json");
    require("/var/www/ghostproxies.com/includes/requests.php");
    $decoded_data = json_decode($_POST["encoded_data"], true);

    if (empty($decoded_data) == false) {
      $decoded_data["messages"] = array();

      if (
        empty($decoded_data["session_id"]) == false &&
        strpos($decoded_data["session_id"], "/") === false
      ) {
        if (empty($decoded_data["input"]["username"]) == false) {
          if (empty($decoded_data["input"]["password"]) == false) {
            $password_hash_salt = file_get_contents("/var/www/ghostproxies.com/password-hash-salt");

            if ($password_hash_salt != false) {
              $user_id = hash("sha256", $decoded_data["input"]["username"] . $password_hash_salt);
              $user_directory_path = "/var/www/ghostproxies.com/database/users/" . $user_id . "/";

              if (is_dir($user_directory_path) == true) {
                $password_hash = file_get_contents($user_directory_path . "password-hash");

                if ($password_hash != false) {
                  if ($password_hash == hash("sha256", $decoded_data["input"]["password"] . $password_hash_salt)) {
                    $session_ids_directory_path = "/var/www/ghostproxies.com/database/sessions/";
                    $session_ids = file_get_contents($user_directory_path . "session-ids");

                    if ($session_ids !== false) {
                      $updated_session_ids = "";

                      while (empty($session_ids) == false) {
                        $i = strpos($session_ids, " ");
                        $session_id = substr($session_ids, 0, $i);

                        if (
                          strpos($updated_session_ids, $decoded_data["session_id"]) === false &&
                          is_link($session_ids_directory_path . $session_id) == true &&
                          (time() - filemtime($session_ids_directory_path . $session_id)) < 11111111
                        ) {
                          $updated_session_ids .= $session_id . " ";
                        }

                        $session_ids = substr($session_ids, $i + 1);
                      }

                      if (file_put_contents($user_directory_path . "session-ids", $updated_session_ids) !== false) {
                        if (substr_count($updated_session_ids, " ") <= 10) {
                          $session_id_file_path = $session_ids_directory_path . $decoded_data["session_id"];

                          if (file_exists($session_id_file_path) == true) {
                            unlink($session_id_file_path);
                          }

                          $temporary_session_id = hash("sha256", $user_id . time() . $password_hash_salt);
                          $temporary_session_id_file_path = $session_ids_directory_path . $temporary_session_id;

                          if (is_link($temporary_session_id_file_path) == true) {
                            unlink($temporary_session_id_file_path);
                          }

                          if (
                            link($user_directory_path . "id", $session_id_file_path) == true &&
                            symlink($session_id_file_path, $temporary_session_id_file_path) == true
                          ) {
                            $decoded_data["redirect"] = "/sign-in/?session_id=" . $temporary_session_id;
                          } else {
                            $decoded_data["messages"]["global"] = "There was an error processing the request...";

                            if (file_exists($session_id_file_path) == true) {
                              unlink($session_id_file_path);
                            }

                            if (is_link($temporary_session_id_file_path) == true) {
                              unlink($temporary_session_id_file_path);
                            }
                          }
                        } else {
                          $decoded_data["messages"]["global"] = "Limit of 10 user sessions per account is exceeded.";
                        }
                      } else {
                        $decoded_data["messages"]["global"] = "There was an error processing the request..";
                      }
                    } else {
                      $decoded_data["messages"]["global"] = "There was an error processing the request....";
                    }
                  } else {
                    $decoded_data["messages"]["global"] = "Either the password or the username is invalid.";
                  }
                } else {
                  $decoded_data["messages"]["global"] = "There was an error processing the request.......";
                }
              } else {
                $decoded_data["messages"]["global"] = "Either the password or the username is invalid.";
              }
            } else {
              $decoded_data["messages"]["global"] = "There was an error processing the request..........";
            }
          } else {
            $decoded_data["messages"]["password"] = "The password is required.";
          }
        } else {
          $decoded_data["messages"]["username"] = "The username is required.";
        }
      } else {
        $decoded_data["messages"]["global"] = "The user session is invalid.";
      }
    } else {
      $decoded_data = array(
        "messages" => array(
          "global" => "There was an error processing the request."
        )
      );
    }

    unset($decoded_data["input"]);
    $encoded_data = json_encode($decoded_data);

    if ($encoded_data == false) {
      $encoded_data = "{\"messages\": {\"global\": \"There was an error processing the request.\"}}";
    }

    echo $encoded_data;
    exit;
  else:
    $parameters = array(
      "title" => "Sign In"
    );
    require("/var/www/ghostproxies.com/includes/header.php");

    if (empty($_SESSION["id"]) == false) {
      $data["redirect"] = "/account/";
    } else if (empty($_SESSION["message"]) == false) {
      $data["messages"]["global"] = $_SESSION["message"];
      unset($_SESSION["message"]);
    }

    if (isset($_GET["invalid_session"]) == true) {
      $_SESSION["message"] = "User session is invalid.";
      $data["redirect"] = "/sign-in/";
    }

    if (
      empty($_GET["session_id"]) == false &&
      strpos($_GET["session_id"], "/") === false &&
      is_link("/var/www/ghostproxies.com/database/sessions/" . $_GET["session_id"]) == true
    ) {
      $temporary_session_id_file_path = "/var/www/ghostproxies.com/database/sessions/" . $_GET["session_id"];
      $session_id_file_path = readlink($temporary_session_id_file_path);

      if (
        $session_id_file_path != false &&
        unlink($temporary_session_id_file_path) != false
      ) {
        $session_id = basename($session_id_file_path);
        $user_id = file_get_contents($session_id_file_path);

        if (
          $user_id != false &&
          unlink($session_id_file_path) != false
        ) {
          $user_directory_path = "/var/www/ghostproxies.com/database/users/" . $user_id . "/";

          if (
            file_exists($user_directory_path . "session-ids") == true &&
            file_put_contents($user_directory_path . "session-ids", $session_id . " ", FILE_APPEND) != false &&
            symlink($user_directory_path, $session_id_file_path) == true
          ) {
            $_SESSION["id"] = $session_id;
            $_SESSION["user_id"] = $user_id;
            $data["redirect"] = "/account/";
          } else {
            $data["messages"]["global"] = "There was an error processing the request.";
          }
        } else {
          $data["messages"]["global"] = "There was an error processing the request.";
        }
      } else {
        $data["messages"]["global"] = "There was an error processing the request.";
      }
    }
?>
      <main class="form interior">
        <h1>Sign In</h1>
<?php if (empty($data["messages"]["global"]) == true): ?>
        <p class="hidden message" name="global"></p>
<?php else: ?>
        <p class="message" name="global"><?php echo $data["messages"]["global"]; ?></p>
<?php endif; ?>
        <div style="margin-top: 35px;">
          <p class="hidden message" name="username"></p>
          <label for="username">Username</label>
          <div class="text-input">
            <input autocomplete="username" autofocus id="username" name="username" type="text">
          </div>
        </div>
        <div>
          <p class="hidden message" name="password"></p>
          <label for="password">Password</label>
          <div class="text-input">
            <input autocomplete="current-password" id="password" name="password" type="password">
          </div>
        </div>
        <button>Sign In</button>
      </main>
<?php
  require("/var/www/ghostproxies.com/includes/javascript.php");
  require("/var/www/ghostproxies.com/includes/footer.php");
  endif;
?>
