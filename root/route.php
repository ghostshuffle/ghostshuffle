<?php
  date_default_timezone_set("America/Los_Angeles");
  require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");
  $ip_address = substr($_SERVER["SCRIPT_URL"], 1, strlen($_SERVER["SCRIPT_URL"]) - 2);
  $a = strstr($ip_address, ".", true);
  $d = substr(strstr($ip_address, "."), 1);
  $b = strstr($d, ".", true);
  $d = substr(strstr($d, "."), 1);
  $c = strstr($d, ".", true);
  $d = substr(strstr($d, "."), 1);
  $ip_address_previous = ((($a << 24) + ($b << 16) + ($c << 8) + $d) - 1) & 0xFFFFFFFF;
  $ip_address_next = ($ip_address_previous + 2) & 0xFFFFFFFF;
  $ip_address_previous = ($ip_address_previous >> 24) . "." . (($ip_address_previous & 0x00FF0000) >> 16) . "." . (($ip_address_previous & 0x0000FF00) >> 8) . "." . ($ip_address_previous & 0x000000FF);
  $ip_address_next = ($ip_address_next >> 24) . "." . (($ip_address_next & 0x00FF0000) >> 16) . "." . (($ip_address_next & 0x0000FF00) >> 8) . "." . ($ip_address_next & 0x000000FF);
  $timestamp = time();

  if (validate_public_ip_address($ip_address) == true):
    $ip_address_proxy_history_directory_path = "/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . str_replace(".", "/", $ip_address) . "/";

    if (
      is_dir($ip_address_proxy_history_directory_path) == true &&
      file_exists($ip_address_proxy_history_directory_path . "f") == true
    ):
      $ip_address_first_confirmed_proxy = file_get_contents($ip_address_proxy_history_directory_path . "f");
      $ip_address_last_confirmed_proxy = file_get_contents($ip_address_proxy_history_directory_path . "l");

      if (
        $ip_address_first_confirmed_proxy !== false &&
        $ip_address_last_confirmed_proxy !== false &&
        substr_count($ip_address_first_confirmed_proxy, "-") == 2 &&
        substr_count($ip_address_last_confirmed_proxy, "-") == 2
      ):
        $ip_address_first_confirmed_proxy_timestamp = strstr($ip_address_first_confirmed_proxy, "-", true);
        $ip_address_first_confirmed_proxy_timestamp_days_ago = floor(($timestamp - $ip_address_first_confirmed_proxy_timestamp) / 86400);
        $ip_address_first_confirmed_proxy = substr(strstr($ip_address_first_confirmed_proxy, "-"), 1);
        $ip_address_first_confirmed_proxy_port = strstr($ip_address_first_confirmed_proxy, "-", true);
        $ip_address_first_confirmed_proxy_protocol = substr(strstr($ip_address_first_confirmed_proxy, "-"), 1);
        $ip_address_last_confirmed_proxy_timestamp = strstr($ip_address_last_confirmed_proxy, "-", true);
        $ip_address_last_confirmed_proxy_timestamp_days_ago = floor(($timestamp - $ip_address_last_confirmed_proxy_timestamp) / 86400);
        $ip_address_last_confirmed_proxy = substr(strstr($ip_address_last_confirmed_proxy, "-"), 1);
        $ip_address_last_confirmed_proxy_port = strstr($ip_address_last_confirmed_proxy, "-", true);
        $ip_address_last_confirmed_proxy_protocol = substr(strstr($ip_address_last_confirmed_proxy, "-"), 1);

        if ($ip_address_last_confirmed_proxy_timestamp > ($timestamp - 1209600)) {
          $ip_address_risk_level = 2;
        } else {
          $ip_address_risk_level = 1;
        }
      else:
        http_response_code(503);
        $parameters = array(
          "title" => "Server Error"
        );
        require("/var/www/ghostproxies.com/includes/header.php");
?>
  <main>
    <h1>Server Error</h1>
    <p class="no-margin-bottom">The server returned a 503 error and failed loading the <?php echo $ip_address; ?> proxy history and risk analysis page.</p>
  </main>
<?php
      endif;
    else:
      $ip_address_risk_level = 0;
    endif;

  if ($ip_address_risk_level == 2) {
    $ip_address_risk_level_word = "high";
  } elseif ($ip_address_risk_level == 1) {
    $ip_address_risk_level_word = "medium";
  } else {
    $ip_address_risk_level_word = "low";
  }

  $parameters = array(
    "title" => $ip_address . ": A " . ucfirst($ip_address_risk_level_word) . "-Risk IPv4 Address: GhostProxies"
  );
  require("/var/www/ghostproxies.com/includes/header.php");
?>
  <main>
    <h1><?php echo $ip_address . ": A " . ucfirst($ip_address_risk_level_word) . "-Risk IPv4 Address"; ?></h1>
    <div class="section">
<?php if ($ip_address_risk_level == 2): ?>
      <p class="no-margin-bottom">GhostProxies confirmed a functional proxy server was forwarding connections <a href="/<?php echo $ip_address; ?>/#history">recently</a> through <span class="code"><?php echo $ip_address; ?></span>, meaning there's <a href="/<?php echo $ip_address; ?>/#analysis">high risk</a> of malicious activity originating from this IPv4 address.</p>
<?php elseif ($ip_address_risk_level == 1): ?>
      <p class="no-margin-bottom">GhostProxies confirmed a functional proxy server was forwarding connections <a href="/<?php echo $ip_address; ?>/#history">a while ago</a> through <span class="code"><?php echo $ip_address; ?></span> with no recent activity, meaning there's a <a href="/<?php echo $ip_address; ?>/#analysis">medium risk</a> of malicious activity originating from this IPv4 address.</p>
<?php else: ?>
      <p class="no-margin-bottom">GhostProxies hasn't confirmed the existence of a functional forwarding proxy server through <span class="code"><?php echo $ip_address; ?></span>, meaning there's <a href="/<?php echo $ip_address; ?>/#analysis">low risk</a> of malicious activity originating from this IPv4 address.</p>
<?php endif; ?>
    </div>
    <div class="section">
      <h2 id="history">History</h2>
<?php if ($ip_address_risk_level > 0): ?>
      <h3>First Confirmed Proxy</h3>
      <p>A<?php echo $ip_address_first_confirmed_proxy_protocol == "http" ? "n" : ""; ?> <span class="code"><?php echo strtoupper($ip_address_first_confirmed_proxy_protocol); ?></span> proxy server using port <span class="code"><?php echo $ip_address_first_confirmed_proxy_port; ?></span> was confirmed on <span class="code"><?php echo date("F d, Y", $ip_address_first_confirmed_proxy_timestamp); ?></span> at <span class="code"><?php echo date("g:i a", $ip_address_first_confirmed_proxy_timestamp); ?> PDT</span>.</p>
      <h3>Last Confirmed Proxy</h3>
      <p class="no-margin-bottom">A<?php echo $ip_address_last_confirmed_proxy_protocol == "http" ? "n" : ""; ?> <span class="code"><?php echo strtoupper($ip_address_last_confirmed_proxy_protocol); ?></span> proxy server using port <span class="code"><?php echo $ip_address_last_confirmed_proxy_port; ?></span> was confirmed on <span class="code"><?php echo date("F d, Y", $ip_address_last_confirmed_proxy_timestamp); ?></span> at <span class="code"><?php echo date("g:i a", $ip_address_last_confirmed_proxy_timestamp); ?> PDT</span>.</p>
<?php else: ?>
      <h3>First Confirmed Proxy</h3>
      <p>There are no confirmed proxies yet.</p>
      <h3>Last Confirmed Proxy</h3>
      <p class="no-margin-bottom">There are no confirmed proxies yet.</p>
<?php endif; ?>
    </div>
    <div class="section">
      <h2 id="analysis">Analysis</h2>
      <p>The following real-time risk analysis of <span class="code"><?php echo $ip_address; ?></span> is unique and independent from any crowd-sourced IP blacklists or existing IP address scoring systems.</p>
<?php if ($ip_address_risk_level == 2): ?>
      <p>GhostProxies automatically processed the available proxy history data for this IP address and determined it has a <span class="code">high-level risk</span> for malicious fraud and hacking activity.</p>
      <p>The calculated risk level result was <span class="code">2</span>, the highest level of risk, instead of either the lower level <span class="code">1</span> or the minimum level <span class="code">0</span>.</p>
      <p>The risk level may decrease to <span class="code">1</span> if there aren't any more proxies confirmed after a short period of time.</p>
<?php
  if (
    $ip_address_first_confirmed_proxy_protocol == "socks" ||
    $ip_address_last_confirmed_proxy_protocol == "socks"
  ):
?>
    <p>The <?php echo $ip_address_first_confirmed_proxy_protocol == "socks" ? "first" : ""; ?><?php echo $ip_address_first_confirmed_proxy_protocol == "socks" && $ip_address_last_confirmed_proxy_protocol == "socks" ? " and " : ""; ?><?php echo $ip_address_last_confirmed_proxy_protocol == "socks" ? "last" : ""; ?> confirmed prox<?php echo $ip_address_first_confirmed_proxy_protocol == "socks" && $ip_address_last_confirmed_proxy_protocol == "socks" ? "ies" : "y"; ?> on this IP address used the anonymizing <span class="code">SOCKS</span> networking protocol, so connections forwarded from this IP address are at a higher risk of click fraud, mail spamming and other illicit activity.</p>
<?php endif; ?>
<?php
  if (
    (
      $ip_address_first_confirmed_proxy_protocol == "http" &&
      $ip_address_first_confirmed_proxy_port == "80"
    ) ||
    (
      $ip_address_last_confirmed_proxy_protocol == "http" &&
      $ip_address_last_confirmed_proxy_port == "80"
    )
  ):
?>
      <p>There was a confirmed <span class="code">HTTP</span> proxy using the standard port <span class="code">80</span>, so this IP address is at a higher risk of malicious usage from a larger number of hackers using their own <span class="code">HTTP</span> port scanners.</p>
<?php endif; ?>
<?php
  if (
    (
      $ip_address_first_confirmed_proxy_protocol == "socks" &&
      $ip_address_first_confirmed_proxy_port == "1080"
    ) ||
    (
      $ip_address_last_confirmed_proxy_protocol == "socks" &&
      $ip_address_last_confirmed_proxy_port == "1080"
    )
  ):
?>
      <p>There was a confirmed <span class="code">SOCKS</span> proxy using the standard port <span class="code">1080</span>, so this IP address is at a higher risk of malicious usage from a larger number of hackers using their own <span class="code">SOCKS</span> port scanners.</p>
<?php endif; ?>
<?php if ($ip_address_first_confirmed_proxy_port != $ip_address_last_confirmed_proxy_port): ?>
      <p>There was more than 1 listening port to access an open, unauthenticated proxy server through this IP address, making it easier for proxy scanners to detect. These proxy ports may have been opened intentionally as either a honeypot for traffic analysis or to allow malicious blackhat activity.</p>
<?php endif; ?>
      <p>A proxy connection through <span class="code"><?php echo $ip_address; ?></span> is likely to happen again since the last proxy was confirmed only <span class="code"><?php echo $ip_address_last_confirmed_proxy_timestamp_days_ago; ?> day<?php echo $ip_address_last_confirmed_proxy_timestamp_days_ago != 1 ? "s" : ""; ?> ago</span>.</p>
<?php if ($ip_address_first_confirmed_proxy_timestamp_days_ago != $ip_address_last_confirmed_proxy_timestamp_days_ago): ?>
      <p>Multiple confirmed proxies have been opening and closing on this IP address for <span class="code"><?php echo $ip_address_first_confirmed_proxy_timestamp_days_ago; ?> day<?php echo $ip_address_first_confirmed_proxy_timestamp_days_ago != 1 ? "s" : ""; ?></span>, making it a highly-active proxy server. It's likely to appear on IP blacklists and public proxy lists across the internet.</p>
<?php endif; ?>
      <p>GhostProxies confirms all detected gateway IPs and proxy chains with non-persistent exit IPs when scanning both forwarding proxy servers and backconnect proxy servers.</p>
      <p>In other words, <span class="code"><?php echo $ip_address; ?></span> could have functioned as both an exit IP from a different proxy and as a gateway proxy on the open listening port<?php echo ($ip_address_first_confirmed_proxy_port != $ip_address_last_confirmed_proxy_port ? "s" : ""); ?> <span class="code"><?php echo $ip_address_first_confirmed_proxy_port; ?></span> <?php echo ($ip_address_first_confirmed_proxy_port != $ip_address_last_confirmed_proxy_port ? "and <span class=\"code\">" . $ip_address_last_confirmed_proxy_port . "</span> " : ""); ?>that forwards to another anonymized exit IP.</p>
      <p class="no-margin-bottom">To navigate adjacently from <span class="code"><?php echo $ip_address; ?></span>, the <?php echo validate_public_ip_address($ip_address_previous) == true ? "<a href=\"/" . $ip_address_previous . "/\">previous IP</a>" : "previous IP"; ?> is <span class="code"><?php echo $ip_address_previous; ?></span> and the <?php echo validate_public_ip_address($ip_address_next) == true ? "<a href=\"/" . $ip_address_next . "/\">next IP</a>" : "next IP"; ?> is <span class="code"><?php echo $ip_address_next; ?></span>.</p>
<?php elseif ($ip_address_risk_level == 1): ?>
      <p>GhostProxies automatically processed the available proxy history data for this IP address and determined it has a <span class="code">medium-level risk</span> for malicious fraud and hacking activity.</p>
      <p>The calculated risk level result was <span class="code">1</span> instead of either the minimum level <span class="code">0</span> or the maximum level <span class="code">2</span>.</p>
      <p>GhostProxies hasn't detected a new proxy since the last proxy was confirmed.</p>
      <p>This risk level may increase to <span class="code">2</span> if there's another proxy confirmed on this IP address at any point in the future.</p>
<?php
  if (
    $ip_address_first_confirmed_proxy_protocol == "socks" ||
    $ip_address_last_confirmed_proxy_protocol == "socks"
  ):
?>
      <p>The <?php echo $ip_address_first_confirmed_proxy_protocol == "socks" ? "first" : ""; ?><?php echo $ip_address_first_confirmed_proxy_protocol == "socks" && $ip_address_last_confirmed_proxy_protocol == "socks" ? " and " : ""; ?><?php echo $ip_address_last_confirmed_proxy_protocol == "socks" ? "last" : ""; ?> confirmed prox<?php echo $ip_address_first_confirmed_proxy_protocol == "socks" && $ip_address_last_confirmed_proxy_protocol == "socks" ? "ies" : "y"; ?> on this IP address used the anonymizing <span class="code">SOCKS</span> networking protocol, so connections forwarded from this IP address are at a higher risk of click fraud, mail spamming and other illicit activity.</p>
<?php endif; ?>
<?php
  if (
    (
      $ip_address_first_confirmed_proxy_protocol == "http" &&
      $ip_address_first_confirmed_proxy_port == "80"
    ) ||
    (
      $ip_address_last_confirmed_proxy_protocol == "http" &&
      $ip_address_last_confirmed_proxy_port == "80"
    )
  ):
?>
      <p>There was a confirmed <span class="code">HTTP</span> proxy using the standard port <span class="code">80</span>, so this IP address is at a higher risk of malicious usage from a larger number of hackers using their own <span class="code">HTTP</span> port scanners.</p>
<?php endif; ?>
<?php
  if (
    (
      $ip_address_first_confirmed_proxy_protocol == "socks" &&
      $ip_address_first_confirmed_proxy_port == "1080"
    ) ||
    (
      $ip_address_last_confirmed_proxy_protocol == "socks" &&
      $ip_address_last_confirmed_proxy_port == "1080"
    )
  ):
?>
      <p>There was a confirmed <span class="code">SOCKS</span> proxy using the standard port <span class="code">1080</span>, so this IP address is at a higher risk of malicious usage from a larger number of hackers using their own <span class="code">SOCKS</span> port scanners.</p>
<?php endif; ?>
<?php if ($ip_address_first_confirmed_proxy_port != $ip_address_last_confirmed_proxy_port): ?>
      <p>There was more than 1 listening port to access an open, unauthenticated proxy server through this IP address, making it easier for proxy scanners to detect. These proxy ports may have been opened intentionally as either a honeypot for traffic analysis or to allow malicious blackhat activity.</p>
<?php endif; ?>
      <p>The previous risk level was <span class="code">2</span>, so it's possible that connections from <span class="code"><?php echo $ip_address; ?></span> are still originating from an open proxy server, but it hasn't been confirmed for over <span class="code"><?php echo $ip_address_last_confirmed_proxy_timestamp_days_ago; ?> days</span>.</p>
<?php if ($ip_address_first_confirmed_proxy_timestamp_days_ago != $ip_address_last_confirmed_proxy_timestamp_days_ago): ?>
      <p>Multiple confirmed proxies have been opening and closing on this IP address for <span class="code"><?php echo $ip_address_first_confirmed_proxy_timestamp_days_ago; ?> day<?php echo $ip_address_first_confirmed_proxy_timestamp_days_ago != 1 ? "s" : ""; ?></span>, making it a long-term, active proxy server. It's likely to appear on IP blacklists and public proxy lists across the internet.</p>
<?php endif; ?>
      <p>GhostProxies confirms all detected gateway IPs and proxy chains with non-persistent exit IPs when scanning both forwarding proxy servers and backconnect proxy servers.</p>
      <p>In other words, <span class="code"><?php echo $ip_address; ?></span> could have functioned as both an exit IP from a different proxy and as a gateway proxy on the open listening port<?php echo ($ip_address_first_confirmed_proxy_port != $ip_address_last_confirmed_proxy_port ? "s" : ""); ?> <span class="code"><?php echo $ip_address_first_confirmed_proxy_port; ?></span> <?php echo ($ip_address_first_confirmed_proxy_port != $ip_address_last_confirmed_proxy_port ? "and <span class=\"code\">" . $ip_address_last_confirmed_proxy_port . "</span> " : ""); ?>that forwards to another anonymized exit IP.</p>
      <p class="no-margin-bottom">To navigate adjacently from <span class="code"><?php echo $ip_address; ?></span>, the <?php echo validate_public_ip_address($ip_address_previous) == true ? "<a href=\"/" . $ip_address_previous . "/\">previous IP</a>" : "previous IP"; ?> is <span class="code"><?php echo $ip_address_previous; ?></span> and the <?php echo validate_public_ip_address($ip_address_next) == true ? "<a href=\"/" . $ip_address_next . "/\">next IP</a>" : "next IP"; ?> is <span class="code"><?php echo $ip_address_next; ?></span>.</p>
<?php else: ?>
      <p>There's no <a href="/#explanation" target="_blank">ghost proxy</a> history data to analyze for this IP address as of <span class="code"><?php echo date("M d, Y", $timestamp); ?></span> at <span class="code"><?php echo date("g:i a", $timestamp); ?> PDT</span>.</p>
      <p>Therefore, there's a <span class="code">low-level risk</span> of malicious activity originating from the IPv4 address <span class="code"><?php echo $ip_address; ?></span>.</p>
      <p>The calculated risk level result was <span class="code">0</span>, the lowest level of risk, instead of either the higher level <span class="code">1</span> or the maximum level <span class="code">2</span>.</p>
      <p>GhostProxies is increasing proxy scanning efforts constantly to detect open proxy servers faster than the malicious fraudsters and hackers can, so the risk level may increase from to <span class="code">0</span> to <span class="code">2</span> at any time in the near future.</p>
      <p class="no-margin-bottom">To navigate adjacently from <span class="code"><?php echo $ip_address; ?></span>, the <?php echo validate_public_ip_address($ip_address_previous) == true ? "<a href=\"/" . $ip_address_previous . "/\">previous IP</a>" : "previous IP"; ?> is <span class="code"><?php echo $ip_address_previous; ?></span> and the <?php echo validate_public_ip_address($ip_address_next) == true ? "<a href=\"/" . $ip_address_next . "/\">next IP</a>" : "next IP"; ?> is <span class="code"><?php echo $ip_address_next; ?></span>.</p>
<?php endif; ?>
    </div>
    <div class="section no-margin-bottom">
      <h2 id="api">API</h2>
      <h3>Request</h3>
      <p><span class="code no-margin-left">https://ghostproxies.com/api/</span> is the API endpoint URL.</p>
      <p>A <span class="code">token</span> field is required as either an HTTP header, a GET field or a POST field in left-to-right descending order of precedence. The value is an authentication token generated from a <a href="/create-an-account/">user account</a>.</p>
      <p>The API pricing is $0.0001 per credit to cover bandwidth and operational costs. Each input IP address included in <span class="code">input_ip_addresses</span> costs 1 credit.</p>
      <p>It accepts the following optional arguments as either GET or POST fields. If both are set, POST fields will overwrite GET fields.</p>
      <p><span class="code no-margin-left">input_ip_addresses</span> is the list of public IPv4 addresses to look up, each separated by a non-alphanumeric string that doesn't contain <span class="code">.</span> or <span class="code">/</span> characters. Each value must be formatted as either an <span class="code">x.x.x.x</span> IPv4 address or a <span class="code">x.x.x.x/y</span> IPv4 CIDR block. The suffix value <span class="code">y</span> must be greater than or equal to <span class="code">24</span> and less than or equal to <span class="code">32</span>. The maximum amount of IP addresses per request is 1024. The default value is the public IP address of the client.</p>
      <p>An example request for <span class="code"><?php echo $ip_address; ?></span> data is demonstrated with a fake <span class="code">token</span> using cURL with the following POST data.</p>
      <code>curl -H "token:0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef" \
  -d "input_ip_addresses=<?php echo $ip_address; ?>" \
  https://ghostproxies.com/api/</code>
      <h3>Response</h3>
      <p>Each response contains the following fields.</p>
      <p><span class="code no-margin-left">output_ip_addresses</span> is the array of <span class="code">output_ip_addresses_count</span> IP address results.</p>
      <p><span class="code no-margin-left">label</span> is the associated public IPv4 address in <span class="code">x.x.x.x</span> format.</p>
      <p><span class="code no-margin-left">first_confirmed_proxy</span> contains the <span class="code">port</span>, <span class="code">protocol</span> and <span class="code">timestamp</span> of the oldest proxy confirmed on the adjacent <span class="code">label</span> public IPv4 address. If there are no confirmed proxies, the value is <span class="code">null</span>.</p>
      <p><span class="code no-margin-left">last_confirmed_proxy</span> contains the <span class="code">port</span>, <span class="code">protocol</span> and <span class="code">timestamp</span> of the most-recent confirmed proxy on the adjacent <span class="code">label</span> public IPv4 address. If there are no confirmed proxies, the value is <span class="code">null</span>.</p>
      <p><span class="code no-margin-left">risk_level</span> is the calculated risk level number of the adjacent <span class="code">label</span> public IPv4 address.</p>
      <p><span class="code no-margin-left">0</span> is a low <span class="code">risk_level</span>, meaning there are no confirmed proxies yet.</p>
      <p><span class="code no-margin-left">1</span> is a medium <span class="code">risk_level</span>, meaning there were confirmed proxies with no recent activity.</p>
      <p><span class="code no-margin-left">2</span> is a high <span class="code">risk_level</span>, meaning there are confirmed proxies with recent activity.</p>
      <p>Each possible HTTP response status code with a corresponding message in <span class="code">status_message</span> is listed in the following table with <span class="code">{x}</span> and <span class="code">{y}</span> representing dynamic values.</p>
      <code>200   Successful.
400   The input_ip_addresses value count of elements must be less than or equal
      to 1024.
400   The input_ip_addresses value element {x} suffix {y} must be less than or
      equal to 32.
400   The input_ip_addresses value element {x} suffix {y} must be greater than
      or equal to 24.
400   The input_ip_addresses value element {x} suffix {y} must be a number.
400   The input_ip_addresses value element {x} must be a public IPv4 address.
400   The input_ip_addresses value element {x} must be either a public IPv4 address
      or an IPv4 CIDR block.
401   Authentication with a token is required.
402   Not enough credits.
500   Server error.</code>
      <p>An example response from the aforementioned example request is demonstrated with the following data in JSON format.</p>
      <code class="no-margin-bottom">{
  "output_ip_addresses": [
<?php if ($ip_address_risk_level == 0): ?>
    {
      "first_confirmed_proxy": null,
      "label": "<?php echo $ip_address; ?>",
      "last_confirmed_proxy": null,
      "risk_level": 0
    }
<?php else: ?>
    {
      "first_confirmed_proxy": {
        "port": <?php echo $ip_address_first_confirmed_proxy_port; ?>,
        "protocol": "<?php echo $ip_address_first_confirmed_proxy_protocol; ?>",
        "timestamp": "<?php echo $ip_address_first_confirmed_proxy_timestamp; ?>"
      },
      "label": "<?php echo $ip_address; ?>",
      "last_confirmed_proxy": {
        "port": <?php echo $ip_address_last_confirmed_proxy_port; ?>,
        "protocol": "<?php echo $ip_address_last_confirmed_proxy_protocol; ?>",
        "timestamp": "<?php echo $ip_address_last_confirmed_proxy_timestamp; ?>"
      },
      "risk_level": <?php echo $ip_address_risk_level; ?>

    }
<?php endif; ?>
  ],
  "output_ip_addresses_count": 1,
  "status_message": "Success."
}</code>
    </div>
  </main>
<?php
  else:
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
  endif;
  require("/var/www/ghostproxies.com/includes/javascript.php");
  require("/var/www/ghostproxies.com/includes/footer.php");
?>
