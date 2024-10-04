<?php
  $parameters = array(
    "title" => "GhostProxies: IPv4 Address Proxy History and Risk Analysis"
  );
  require("/var/www/ghostproxies.com/includes/header.php");
  $ip_address_spotlight_proxy = file_get_contents("/var/www/ghostproxies.com/database/ip-addresses-proxies-history/s");

  if (
    $ip_address_spotlight_proxy != false &&
    substr_count($ip_address_spotlight_proxy, "-") == 3
  ) {
    $ip_address_spotlight = strstr($ip_address_spotlight_proxy, "-", true);
    $ip_address_spotlight_proxy = substr(strstr($ip_address_spotlight_proxy, "-"), 1);
    $ip_address_spotlight_proxy_seconds_ago = time() - strstr($ip_address_spotlight_proxy, "-", true);
    $ip_address_spotlight_proxy = substr(strstr($ip_address_spotlight_proxy, "-"), 1);
    $ip_address_spotlight_proxy_port = strstr($ip_address_spotlight_proxy, "-", true);
    $ip_address_spotlight_proxy_protocol = substr(strstr($ip_address_spotlight_proxy, "-"), 1);
  }
?>
      <main class="homepage">
        <div class="introduction">
          <h1>IPv4 Address Proxy History and Risk Analysis</h1>
          <p style="margin: 20px 0 15px;">GhostProxies scans the public internet and maintains a historical log of proxy servers to calculate IP-based risk levels that help block cyber attacks and reduce fraud with less false positives.</p>
          <p class="button-container randomness-animation"><span class="row row-1"><span class="column column-1 delay-3 number-0">123</span><span class="column column-2 delay-2 number-1">0</span><span class="column column-3 delay-1">11</span><span class="column column-4 delay-1">79</span></span><span class="row row-2"><span class="column column-1 delay-2 number-2">58</span><span class="column column-2 delay-1 number-3">190</span><span class="column column-3 delay-3">100</span><span class="column column-4 delay-3">53</span></span><span class="row row-3"><span class="column column-1 delay-2 number-4">91</span><span class="column column-2 delay-1 number-5">242</span><span class="column column-3 delay-1">33</span><span class="column column-4 delay-2">31</span></span><input autofocus id="lookup-header" name="lookup" placeholder="Public IPv4 Address" type="text"><a class="button" href="javascript:void(0)" name="lookup-header">Lookup</a></p>
        </div>
<?php if (empty($ip_address_spotlight) == false): ?>
        <div class="section">
          <h2 id="spotlight">Spotlight</h2>
          <div class="spotlight">
            <a href="/<?php echo $ip_address_spotlight; ?>/"><?php echo $ip_address_spotlight; ?>: A High-Risk IPv4 Address</a>
            <span class="seconds-ago"><span class="increment"><?php echo $ip_address_spotlight_proxy_seconds_ago; ?></span> Second<span class="plural"><?php echo $ip_address_spotlight_proxy_seconds_ago != 1 ? "s" : ""; ?></span> Ago</span>
            <h3 class="ip-address-label"><?php echo $ip_address_spotlight; ?>: A High-Risk IPv4 Address</h3>
            <p>GhostProxies confirmed an open <?php echo strtoupper($ip_address_spotlight_proxy_protocol); ?> proxy server was listening on port <?php echo $ip_address_spotlight_proxy_port; ?>.</p>
            <span class="arrow">-></span>
          </div>
        </div>
<?php endif; ?>
        <div class="section">
          <h2 id="api">API</h2>
          <h3>Request</h3>
          <p><span class="code no-margin-left">https://ghostproxies.com/api/</span> is the API endpoint URL.</p>
          <p>A <span class="code">token</span> field is required as either an HTTP header, a GET field or a POST field in left-to-right descending order of precedence. The value is an authentication token generated from a <a href="/create-an-account/">user account</a>.</p>
          <p>The API pricing is $0.0001 per credit to cover bandwidth and operational costs. Each input IP address included in <span class="code">input_ip_addresses</span> costs 1 credit.</p>
          <p>It accepts the following optional arguments as either GET or POST fields. If both are set, POST fields will overwrite GET fields.</p>
          <p><span class="code no-margin-left">input_ip_addresses</span> is the list of public IPv4 addresses to look up, each separated by a non-alphanumeric string that doesn't contain <span class="code">.</span> or <span class="code">/</span> characters. Each value must be formatted as either an <span class="code">x.x.x.x</span> IPv4 address or a <span class="code">x.x.x.x/y</span> IPv4 CIDR block. The suffix value <span class="code">y</span> must be greater than or equal to <span class="code">24</span> and less than or equal to <span class="code">32</span>. The maximum amount of IP addresses per request is 1024. The default value is the public IP address of the client.</p>
          <p>An example request is demonstrated with a fake <span class="code">token</span> and fake <span class="code">input_ip_addresses</span> using cURL with the following POST data.</p>
          <code>curl -H "token:0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef" \
-d "input_ip_addresses=10.0.0.1/31-_-192.168.0.8" \
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
    {
      "first_confirmed_proxy": null,
      "label": "10.0.0.0",
      "last_confirmed_proxy": null,
      "risk_level": 0
    },
    {
      "first_confirmed_proxy": null,
      "label": "10.0.0.1",
      "last_confirmed_proxy": null,
      "risk_level": 0
    },
    {
      "first_confirmed_proxy": {
        "port": 80,
        "protocol": "http",
        "timestamp": "123456789"
      },
      "label": "192.168.0.8",
      "last_confirmed_proxy": {
        "port": 1080,
        "protocol": "socks",
        "timestamp": "<?php echo time(); ?>"
      },
      "risk_level": 2
    }
  ],
  "output_ip_addresses_count": 3,
  "status_message": "Success."
}</code>
        </div>
        <div class="no-margin-bottom section">
          <h2 id="explanation">Explanation</h2>
          <p>Are any of your IP addresses haunted by a ghost proxy?</p>
          <p>GhostProxies refines IP address blacklisting methodology and increases the value of IP addresses by acknowledging the evolution of abuse compliance and enforcement tactics.</p>
          <p>Illegal botnets on hacked user devices with unblockable residential IP addresses are being replaced with compliant, ethical, exclusive proxy networks that scrape website data for reputable businesses. ISPs and law enforcement are constantly taking down illegal botnets while allowing and monitoring compliant ones.</p>
          <p>Blacklisting a residential IP creates more problems than it solves with too many false positives.</p>
          <p>Furthermore, malicious VPN and authenticated proxy usage from hosting providers can be traced back to consumers through abuse reports, payment information and usage patterns, resulting in alternative enforcement based on RIR allocation policies, anti-bot technology, geolocation sanctions and vendor usage policies.</p>
          <p>Blocking and filtering these instances based on IP address history data instead of user-specific activity only creates false positives while ruining the integrity of scarce IP addresses.</p>
          <p>As a result, GhostProxies only focuses on IP addresses with a history of open proxy usage to ensure 100% accuracy and transparency during IP-based risk analyses.</p>
          <p>When an IP address is exposed to the public on an open proxy listening port, hundreds of malicious hackers can hypothetically use stolen or unauthenticated WiFi and connect through the listening open proxy port to conduct fraudulent activity without accountability.</p>
          <p>This all happens in a short period of time while new open proxy servers are created every minute.</p>
          <p>These proxy servers end up on frequently-updated proxy lists, which are occasionally inaccurate or spoofed to populate the proxy list as a sales effort to promote other premium proxy services.</p>
          <p>GhostProxies parses these proxy lists and performs a deep scan at various intervals with high timeout settings to confirm which IP addresses are actually hosting functional open proxy servers.</p>
          <p>From this information, GhostProxies calculates a proprietary risk level metric in real time and maintains a historical database of these "ghosts" left behind from proxies that appeared on any and all public IP addresses. Risk levels can fluctuate based on confirmation frequency and cooldown periods.</p>
          <p><span class="code no-margin-left">0</span> is low risk, <span class="code">1</span> is medium risk and <span class="code">2</span> is high risk. These 3 actionable risk levels are clearly-defined for practical implementations as opposed to percentage-based heuristics that require users to make their own complicated risk calculations without the source data.</p>
          <p class="no-margin-bottom">This unique data is <a href="/#api">available in an API</a> as a reliable "first line of defense" in automated website security and as a supplemental improvement to IP blacklist aggregation services that already provide their own metrics to consumers.</p>
        </div>
      </main>
  <?php require("/var/www/ghostproxies.com/includes/javascript.php"); ?>
<?php require("/var/www/ghostproxies.com/includes/footer.php"); ?>
