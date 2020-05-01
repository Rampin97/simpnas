<?php 
    include("config.php");
    include("header.php");
    include("side_nav.php");
?>

<main class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
  <nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
    <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
    <li class="breadcrumb-item active">Install Transmission</li>
  </ol>
</nav>

  <h2>Install Transmission</h2>
  <ul>
  	<li>A group called download will be created.</li>
  	<li>We will create a share called downloads based on the volume you select.</li>
  	<li>You will need to assign users to the download group if you want users to access and write to the downloads share over the network.</li>
  	<li>You may also configure Transmission to connect to a VPN to hide your public IP from torrent users</li>
  	<li>When Installation is complete you can access Transmission by visiting http://<?php echo $_SERVER['HTTP_HOST']; ?>:9091</li>
  </ul>
 
  <form method="post" action="post.php">

	  <div class="form-group">
	    <label>Volume to create downloads share <strong class="text-danger">*</strong></label></label>
	    <select class="form-control" name="volume">
	  	<?php
			exec("ls /$config_mount_target", $volume_list);
			foreach ($volume_list as $volume) {
				$mounted = exec("df | grep $volume");
				if(!empty($mounted)){
			?>
				<option><?php echo "$volume"; ?></option>	
				<?php 
				} 
				?>
			<?php
			}
			?>

	  </select>
	  </div>
	  <div class="form-group">
	  	<div class="custom-control custom-checkbox">
		  <input type="checkbox" class="custom-control-input" name="enable_vpn" value="1" id="configVpn">
		  <label class="custom-control-label" for="configVpn">Configure VPN</label>
		</div>
	  </div>
	  <div id="vpnSettings">		  
		  <div class="form-group">
		    <label>VPN Provider <strong class="text-danger">*</strong></label>
		    <select class="form-control" name="vpn_provider">
		  		<option>PIA</option>
		  	</select>
		  	<small class="form-text text-muted">We only support PIA VPN for right now.</small>
		  </div>
		  <div class="form-group">
		    <label>VPN Region / Server <small class="text-secondary">(Optional)</small></label>
		    <input type="text" class="form-control" name="vpn_server" placeholder="Optional (Random by Default)">
		  </div>
		  <div class="form-group">
		    <label>VPN Username <strong class="text-danger">*</strong></label></label>
		    <input type="text" class="form-control" name="username" placeholder="Username">
		  </div>
		  <div class="form-group">
		    <label>VPN Password <strong class="text-danger">*</strong></label></label>
		    <input type="password" class="form-control" name="password" placeholder="Password">
		  </div>
		  <div class="form-group">
		    <label>DNS Server <small class="text-secondary">(Optional)</small></label>
		    <input type="text" class="form-control" name="dns" placeholder="1.1.1.1">
		  	<small class="form-text text-muted">Required if your current dns server isn't accessable outside its own network (If you have Comcast then user a public DNS) Common DNS Servers 8.8.8.8 (Google) 1.1.1.1 (Cloudflare) 9.9.9.9 (Quad9)</small>
		  </div>
	   </div>
 	<button type="submit" name="install_transmission" class="btn btn-primary">Submit</button>
	 
	</form>
</main>

<?php include("footer.php"); ?>
