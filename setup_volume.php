<?php

	include("setup_header.php");
	
	//$os_disk = exec("findmnt -n -o SOURCE --target / | cut -c -8");
	$os_disk = exec("lsblk -n -o pkname,MOUNTPOINT | grep -w / | awk '{print $1}'");


?>

<main class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
  <nav>
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item"><a href="setup.php">Setup</a></li>
	    <li class="breadcrumb-item"><a href="setup.php">Timezone</a></li>
	    <li class="breadcrumb-item"><a href="setup_network.php">Network</a></li>
	    <li class="breadcrumb-item active">Volume</li>
	  </ol>
	</nav>
  
  <h2>Volume Creation</h2>
  <hr>

  <?php include("alert_message.php"); ?>

  <form method="post" action="post.php" autocomplete="off">

	  <div class="form-group">
	    <label>Volume Name</label>
	    <input type="text" class="form-control" name="volume_name" required autofocus>
	  </div>

	  <div class="form-group">
	    <label>Select Disk</label>
	    <select class="form-control" name="disk" required>
		  	<option value=''>--Select A Disk--</option>
		  	<?php
				exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | grep -v $os_disk | awk '{print $1}'", $disk_list_array);
				foreach ($disk_list_array as $disk) {
	        $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Model Family:' | awk '{print $3,$4,$5}'");
				  if(empty($disk_vendor)){
				    $disk_vendor = exec("smartctl -i /dev/$disk | grep 'Device Model:' | awk '{print $3,$4,$5}'");
				  }
				  if(empty($disk_vendor)){
				    $disk_vendor = exec("lsblk -n -o kname,type,vendor /dev/$disk | grep disk  | awk '{print $3}'");
				  }
			    $disk_model = exec("lsblk -n -o kname,type,model /dev/$disk | grep disk  | awk '{print $3}'");
			    $disk_serial = exec("lsblk -n -o kname,type,serial /dev/$disk | grep disk  | awk '{print $3}'");
			    $disk_size = exec("lsblk -n -o kname,type,size /dev/$disk | grep disk | awk '{print $3}'");
				?>
				<option value="<?php echo $disk; ?>"><?php echo "$disk - $disk_vendor ($disk_size)"; ?></option>

				<?php
				}
				?>

	  	</select>
	  	<small class="form-text text-muted">This volume will house your docker configs and user home Directories</small>
	  </div>
	  
	  <button type="submit" name="setup_volume" class="btn btn-primary">Next <span data-feather="arrow-right"></span></button>
	</form>
</main>

<?php include("footer.php"); ?>