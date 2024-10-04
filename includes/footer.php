      <footer>
        <div class="no-margin-bottom">
          <p>Reveal public IP addresses with a haunted past of proxy usage.</p>
          <p class="button-container randomness-animation" style="margin-bottom: 48px !important;"><span class="row row-1"><span class="column column-1 delay-3 number-0">123</span><span class="column column-2 delay-2 number-1">0</span><span class="column column-3 delay-1">11</span><span class="column column-4 delay-1">79</span></span><span class="row row-2"><span class="column column-1 delay-2 number-2">58</span><span class="column column-2 delay-1 number-3">190</span><span class="column column-3 delay-3">100</span><span class="column column-4 delay-3">53</span></span><span class="row row-3"><span class="column column-1 delay-2 number-4">91</span><span class="column column-2 delay-1 number-5">242</span><span class="column column-3 delay-1">33</span><span class="column column-4 delay-2">31</span></span><input id="lookup-footer" name="lookup" placeholder="Public IPv4 Address" type="text"><a class="button" href="javascript:void(0)" name="lookup-footer">Lookup</a></p>
          <p><a class="ghostproxies-icon" href="/"><img alt="GhostProxies Icon" src="/ghostproxies.png"></a> &copy; 2024 GhostProxies</p>
          <ul>
            <li><a href="/#api">API</a></li><?php echo empty($_SESSION["id"]) == false ? "<li><a href=\"/account/\">Account</a></li>" : ""; ?><li><a href="/contact/">Contact</a></li><?php echo empty($_SESSION["id"]) == true ? "<li><a href=\"/create-an-account/\">Create an Account</a></li>" : ""; ?><li><a href="/privacy-policy/">Privacy Policy</a></li><li><a href="/sign-<?php echo empty($_SESSION["id"]) == true ? "in" : "out"; ?>/">Sign <?php echo empty($_SESSION["id"]) == true ? "In" : "Out"; ?></a></li><li><a href="/#spotlight">Spotlight</a></li><li><a href="/terms/">Terms</a></li>
          </ul>
          <p style="font-size: 12px; margin: 48px 0 0;">GhostProxies is a free tool built and maintained by <a href="https://eightomic.com/">Eightomic</a>.</p>
        </div>
<?php if (empty($data["redirect"]) == false): ?>
        <div class="hidden redirect"><?php echo $data["redirect"]; ?></div>
<?php endif; ?>
      </footer>
    </div>
  </body>
</html>
