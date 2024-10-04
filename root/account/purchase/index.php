<?php
	$parameters = array(
    "no_index" => true,
		"title" => "Purchase"
	);
	require("/var/www/ghostproxies.com/includes/header.php");

  if (
    empty($_SESSION["id"]) == true ||
    file_exists("/var/www/ghostproxies.com/database/sessions/" . $_SESSION["id"]) == false ||
    file_exists("/var/www/ghostproxies.com/database/users/" . $_SESSION["user_id"] . "/name") == false
  ) {
    session_destroy();
		$data["redirect"] = "/create-an-account/";
	} else {
    $data = array(
      "amount" => "10.00",
      "business" => "MNLGEAD5LAGYE",
      "cancel_return" => "https://ghostproxies.com/account/",
      "cmd" => "_xclick",
      "item_name" => "100,000 Credits",
      "item_number" => $_SESSION["user_id"],
      "return" => "https://ghostproxies.com/account/?paid",
      "undefined_quantity" => true
    );
    $data = array(
      "redirect" => "https://www.paypal.com/cgi-bin/webscr?" . http_build_query($data) . "&notify_url=" . urlencode("https://ghostproxies.com/paypal-transactions/")
    );
  }
?>
          <main class="full-width">
            <p class="no-margin-bottom">Processing&hellip;</p>
          </main>
<?php
	require("/var/www/ghostproxies.com/includes/javascript.php");
	require("/var/www/ghostproxies.com/includes/footer.php");
?>
