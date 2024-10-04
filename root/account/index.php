<?php
  $parameters = array(
    "no_index" => true,
    "title" => "Account"
  );
  require("/var/www/ghostproxies.com/includes/header.php");

  if (
    empty($_SESSION["id"]) == true ||
    file_exists("/var/www/ghostproxies.com/database/sessions/" . $_SESSION["id"]) == false ||
    file_exists("/var/www/ghostproxies.com/database/users/" . $_SESSION["user_id"] . "/name") == false
  ) {
    session_destroy();
    $data["redirect"] = "/sign-in/?invalid_session";
  } else {
    if (empty($_SESSION["message"]) == false) {
      $data["messages"]["global"] = $_SESSION["message"];
      unset($_SESSION["message"]);
    }

    if (isset($_GET["password_changed"]) == true) {
      $_SESSION["message"] = "Password changed successfully.";
      $data["redirect"] = "/account/";
    } else if (isset($_GET["paid"]) == true) {
      $_SESSION["message"] = "The purchased credits will apply to your account within a few minutes after the payment confirmation is processed.";
      $data["redirect"] = "/account/";
    }

    $user_directory_path = "/var/www/ghostproxies.com/database/users/" . $_SESSION["user_id"] . "/";
    $credits_count = file_get_contents($user_directory_path . "credits-count");
    $password_changed_timestamp = filemtime($user_directory_path . "password-hash");
    $username = file_get_contents($user_directory_path . "name");

    if (
      $credits_count === false ||
      $password_changed_timestamp == false ||
      $username == false
    ) {
      $data["messages"]["global"] = "There was an error processing the request.";
    }

    $decoded_tokens_data = array();

    if (file_exists("/var/www/ghostproxies.com/database/tokens/" . $_SESSION["user_id"] . "/data.json") == true) {
      $encoded_tokens_data = file_get_contents("/var/www/ghostproxies.com/database/tokens/" . $_SESSION["user_id"] . "/data.json");
      $decoded_tokens_data = json_decode($encoded_tokens_data, true);
    }

    if (empty($_GET["delete-token"]) == false) {
      $tokens_user_directory_path = "/var/www/ghostproxies.com/database/tokens/" . $_SESSION["user_id"] . "/";

      if (empty($_SESSION["user_id"]) == false) {
        if (
          is_dir($tokens_user_directory_path) == true &&
          ctype_alnum($_GET["delete-token"]) == true &&
          basename(readlink("/var/www/ghostproxies.com/database/tokens/" . $_GET["delete-token"])) == $_SESSION["user_id"]
        ) {
          $i = 0;
          $decoded_tokens_data_count = count($decoded_tokens_data);

          while ($i != $decoded_tokens_data_count) {
            if ($decoded_tokens_data[$i] == $_GET["delete-token"]) {
              unset($decoded_tokens_data[$i]);
              $decoded_tokens_data = array_values($decoded_tokens_data);
              $encoded_tokens_data = json_encode($decoded_tokens_data);

              if (
                $encoded_tokens_data == false ||
                file_put_contents($tokens_user_directory_path . "data.json", $encoded_tokens_data) == false ||
                unlink("/var/www/ghostproxies.com/database/tokens/" . $_GET["delete-token"]) == false
              ) {
                $_SESSION["message"] = "There was an error processing the request, try again.";
                unlink("/var/www/ghostproxies.com/database/tokens/" . $_GET["delete-token"]);
              }

              $i = $decoded_tokens_data_count;
            } else {
              $i++;
            }
          }
        } else {
          $_SESSION["message"] = "Token is invalid.";
        }
      }

      $data["redirect"] = "/account/";
    }

    if (isset($_GET["generate-token"]) == true) {
      if (file_exists("/var/www/ghostproxies.com/database/users/" . $_SESSION["user_id"] . "/has-transactions") == true) {
        if (count($decoded_tokens_data) <= 10) {
          $token = substr(hash("sha256", hrtime(true) . $_SESSION["user_id"] . hrtime(true)), 0, 64 - (hrtime(true) & 7));
          $decoded_tokens_data[] = $token;
          $tokens_user_directory_path = "/var/www/ghostproxies.com/database/tokens/" . $_SESSION["user_id"] . "/";

          if (
            is_dir($tokens_user_directory_path) == false &&
            (
              mkdir($tokens_user_directory_path) == false ||
              chmod($tokens_user_directory_path, 0777) == false
            )
          ) {
            $_SESSION["message"] = "There was an error processing the request, try again.";
            array_pop($decoded_tokens_data);
          } else {
            $encoded_tokens_data = json_encode($decoded_tokens_data);

            if (
              $encoded_tokens_data == false ||
              file_put_contents($tokens_user_directory_path . "data.json", $encoded_tokens_data) == false ||
              symlink("/var/www/ghostproxies.com/database/users/" . $_SESSION["user_id"] . "/", "/var/www/ghostproxies.com/database/tokens/" . $token) == false
            ) {
              $_SESSION["message"] = "There was an error processing the request, try again.";
              array_pop($decoded_tokens_data);
            }
          }
        } else {
          $_SESSION["message"] = "Limit of 10 tokens per user is exceeded, <a href=\"/contact/\">send a message</a> to request a limit increase.";
        }
      } else {
        $_SESSION["message"] = "Credits are required before generating a token.";
      }

      $data["redirect"] = "/account/";
    }
  }
?>
        <main class="full-width">
          <h1>Account</h1>
<?php if (empty($data["redirect"])): ?>
          <div class="section">
<?php if (empty($data["messages"]["global"]) == false): ?>
          <p class="message" name="global"><?php echo $data["messages"]["global"]; ?></p>
<?php endif; ?>
            <h2>User</h2>
            <h3 class="no-margin-bottom no-margin-top">Name</h3>
            <p><?php echo $username; ?></p>
            <h3 class="no-margin-bottom">Password</h3>
            <p>Changed on <?php echo date("F jS, Y", $password_changed_timestamp); ?><br><a href="/account/change-password/">Change</a></p>
            <h3 class="no-margin-bottom">Credits Remaining</h3>
            <p class="no-margin-bottom"><?php echo $credits_count; ?></p>
            <p class="no-margin-bottom"><a href="/account/purchase/" target="_blank">Refill Credits</a></p>
          </div>
          <div class="section" id="tokens">
            <h2>Tokens</h2>
<?php if (empty($decoded_tokens_data) == false): ?>
<?php $i = count($decoded_tokens_data); ?>
<?php while ($i != 0): ?>
<?php $i--; ?>
              <p class="no-margin-bottom"><?php echo $decoded_tokens_data[$i]; ?></p>
              <p><a href="/account/?delete-token=<?php echo $decoded_tokens_data[$i]; ?>" onclick="return confirm('Are you sure you want to delete this token?')">Delete Token</a></p>
<?php endwhile; ?>
<?php elseif ($decoded_tokens_data !== false): ?>
            <p>There are no tokens to display.</p>
<?php else: ?>
            <p>There was an error processing the request, try again.</p>
<?php endif; ?>
            <p class="no-margin-bottom"><a href="/account/?generate-token">Generate Token</a></p>
<?php else: ?>
          <div class="no-margin-bottom section">
            <p class="no-margin-bottom">Processing&hellip;</p>
<?php endif; ?>
          </div>
    		</main>
  <?php
  require("/var/www/ghostproxies.com/includes/javascript.php");
	require("/var/www/ghostproxies.com/includes/footer.php");
?>
