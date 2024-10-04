<?php
  if (empty($_POST["encoded_data"]) == false):
    header("Content-type: application/json");
    require("/var/www/ghostproxies.com/includes/requests.php");
    $decoded_data = json_decode($_POST["encoded_data"], true);

    if (empty($decoded_data) == false) {
      $decoded_data["messages"] = array();

      if (empty($decoded_data["input"]["email"]) == false) {
        if (strlen($decoded_data["input"]["email"]) < 200) {
          if (empty($decoded_data["input"]["message"]) == false) {
            if (strlen($decoded_data["input"]["message"]) < 700) {
              $message_filename = time() - (time() % 60);

              if (file_exists("/var/www/ghostproxies.com/database/messages/" . $message_filename . "-1") == false) {
                if (file_put_contents("/var/www/ghostproxies.com/database/messages/" . $message_filename . "-1", $decoded_data["input"]["email"] . "\n" . $decoded_data["input"]["message"]) != false) {
                  $decoded_data["messages"]["global"] = "Message sent successfully.";
                } else {
                  $decoded_data["messages"]["global"] = "There was an error processing the request.";
                }
              } else if (file_exists("/var/www/ghostproxies.com/database/messages/" . $message_filename . "-2") == false) {
                if (file_put_contents("/var/www/ghostproxies.com/database/messages/" . $message_filename . "-2", $decoded_data["input"]["email"] . "\n" . $decoded_data["input"]["message"]) != false) {
                  $decoded_data["messages"]["global"] = "Message sent successfully.";
                } else {
                  $decoded_data["messages"]["global"] = "There was an error processing the request.";
                }
              } else {
                $decoded_data["messages"]["global"] = "There's currently a large volume of contact requests right now. Use one of the social media platforms below.";
              }
            } else {
              $decoded_data["messages"]["message"] = "Message must be less than 700 bytes.";
            }
          } else {
            $decoded_data["messages"]["message"] = "Message is required.";
          }
        } else {
          $decoded_data["messages"]["email"] = "Email must be less than 200 bytes.";
        }
      } else {
        $decoded_data["messages"]["email"] = "Email is required.";
      }
    } else {
      $decoded_data = array(
        "messages" => array(
          "global" => "There was an error processing the request."
        )
      );
    }

    $encoded_data = json_encode($decoded_data);

    if ($encoded_data == false) {
      $encoded_data = "{\"messages\": {\"global\": \"There was an error processing the request.\"}}";
    }

    echo $encoded_data;
    exit;
  else:
    $parameters = array(
      "title" => "Contact"
    );
    require("/var/www/ghostproxies.com/includes/header.php");
?>
      <main class="form">
        <h1>Contact</h1>
        <p class="hidden message" name="global" style="margin-top: 22px;"></p>
        <div style="margin-top: 22px;">
          <p class="hidden message" name="email"></p>
          <label for="email">Email</label>
          <div class="text-input">
            <input autocomplete="email" autofocus id="email" name="email" type="text">
          </div>
        </div>
        <div>
          <p class="hidden message" name="message"></p>
          <label for="message">Message</label>
          <div class="text-input">
            <textarea id="message" name="message"></textarea>
          </div>
        </div>
        <button class="button">Send Message</button>
      </main>
  <?php
  require("/var/www/ghostproxies.com/includes/javascript.php");
  require("/var/www/ghostproxies.com/includes/footer.php");
  endif;
?>
