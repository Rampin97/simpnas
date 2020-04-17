<?php 

  include("config.php");
  include("functions.php"); 

if(isset($_GET['upgrade_simpnas'])){
  exec("cd /simpnas");
  exec("git pull origin master");
  echo "<script>window.location = 'index.php'</script>";
}

if(isset($_POST['user_add'])){
  $username = $_POST['username'];
  $password = $_POST['password'];

  if(!file_exists("/$config_mount_target/$config_home_volume/$config_home_dir/")) {
    mkdir("/$config_mount_target/$config_home_volume/$config_home_dir/");
  }
 
  exec ("useradd -g users -m -d /$config_mount_target/$config_home_volume/$config_home_dir/$username $username -p $password");
  exec ("echo '$password\n$password' | smbpasswd -a $username");
  
  if(isset($_POST['group'])){
  	$group_array = $_POST['group'];
  	foreach($group_array as $group){
    	exec ("adduser $username $group");
  	}
  }
  
  exec ("chmod -R 700 /$config_mount_target/$config_home_volume/$config_home_dir/$username");
  
  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['user_edit'])){
  $username = $_POST['username'];
  $group_array = implode(",", $_POST['group']);
  
  //$group_count = count($group);
  if(!empty($_POST['password'])){
    $password = $_POST['password'];
    exec ("echo '$password\n$password' | passwd $username");
    exec ("echo '$password\n$password' | smbpasswd $username");
  }
  if(!empty($group_array)){
    exec ("usermod -G $group_array $username");
  }else{
    exec ("usermod -G users $username");
  }
  print_r($group_array);

  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['group_edit']))
{
  $old_group = $_POST['old_group'];
  $group = $_POST['group'];

  exec ("groupmod -n $group $old_group");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['group_modify_submit']))
{
  $group_id = check_input($_POST['group_id']);
  $group_name = check_input(ucwords($_POST['group_name']));
  $security = check_input($_POST['security']);

    $sql = "UPDATE groups SET group_name = '$group_name', security = '$security' WHERE group_id = '$group_id'";

    mysql_query($sql);
    echo "
    <script>
    window.location = '$document_root/group_list.php'
  </script>";
}

if(isset($_GET['delete_group']))
{
  $group = $_GET['delete_group'];

  exec("delgroup $group");

  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['general_edit']))
{
  $hostname = $_POST['hostname'];
  
  exec("echo $hostname > /etc/hostname");
  exec("echo '127.0.0.1     $hostname localhost.localdomain localhost' > /etc/hosts");
  exec("hostname $hostname");
  //exec("service networking restart");
  echo "<script>window.location = 'general.php'</script>";
}


if(isset($_GET['unmount_volume']))
{
  $vol = $_GET['unmount_volume'];
  exec ("umount /$config_mount_target/$vol");
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_GET['delete_volume']))
{
  $name = $_GET['delete_volume'];
  //check to make sure no shares are linked to the volume
  //if so then choose cancel or give the option to move them to a different volume if another one exists and it will fit onto the new volume
  //the code to do that here
  $hdd = exec("find$config_mount_target -n -o SOURCE --target /$config_mount_target/$name");
  
  exec ("umount /$config_mount_target/$name");
  exec ("rm -rf /$config_mount_target/$name");
  exec ("wipefs -a $hdd");
  
  deleteLineInFile("/etc/fstab","$hdd");
  
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_GET['mount_hdd']))
{
  $hdd = $_GET['mount_hdd'];
  $hdd = $hdd."1";
  $hdd_mount_to = "/$config_mount_target/".basename($hdd);
  if (!(file_exists($hdd_mount_to))) {
	     exec ("sudo mkdir $hdd_mount_to");
	} 

  exec ("sudo mount $hdd $hdd_mount_to");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_POST['volume_add']))
{
  $name = $_POST['name'];
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  exec ("wipefs -a $hdd");
  exec ("(echo o; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("mkdir /$config_mount_target/$name");
  if(!empty($_POST['encrypt'])){
    $password = $_POST['password'];
    exec ("echo -e '$password' | cryptsetup -q luksFormat $hdd_part");
    exec ("echo -e '$password' | cryptsetup open $hdd_part crypt$name");
    exec ("mkfs.ext4 /dev/mapper/crypt$name");    
    exec ("mount /dev/mapper/crypt$name /$config_mount_target/$name");
  }else{

  exec ("mkfs.ext4 $hdd_part");
  exec ("mount $hdd_part /$config_mount_target/$name");  
  
  $myFile = "/etc/fstab";
     $fh = fopen($myFile, 'a') or die("can't open file");
     $stringData = "$hdd_part    /$config_mount_target/$name      ext4    rw,relatime,data=ordered 0 2\n";
     fwrite($fh, $stringData);
     fclose($fh);
}
  echo "<script>window.location = 'volumes.php'</script>";
}

if(isset($_POST['share_add']))
{
  $volume = $_POST['volume'];
  $name = strtolower($_POST['name']);
  $description = $_POST['description'];
  $share_path = "/$config_mount_target/$volume/$name";
  $group = $_POST['group'];
  mkdir("$share_path");
  chgrp("$share_path", $group);
  chmod("$share_path", 0770);

  //exec ("mkdir '$share_path'");
  //exec ("chgrp root:$group '$share_path'");
  //exec ("chmod 2777 '$share_path'");
     
     //$myFile = "/etc/samba/smb.conf";
	   //$fh = fopen($myFile, 'a') or die("can't open file");
	   //$stringData = "\n[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770\n\n";
	   //fwrite($fh, $stringData);
	   //fclose($fh);

     $myFile = "/etc/samba/shares/$name";
     $fh = fopen($myFile, 'w') or die("not able to write to file");
     $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
     fwrite($fh, $stringData);
     fclose($fh);

     $myFile = "/etc/samba/shares.conf";
     $fh = fopen($myFile, 'a') or die("not able to write to file");
     $stringData = "\ninclude = /etc/samba/shares/$name";
     fwrite($fh, $stringData);
     fclose($fh);
  
       exec ("systemctl restart smbd");
  	   echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_POST['share_edit']))
{
  $volume = $_POST['volume'];
  $name = strtolower($_POST['name']);
  $description = $_POST['description'];
  $share_path = "/$config_mount_target/$volume/$name";
  $group = $_POST['group'];
  $current_volume = $_POST['current_volume'];
  $current_name = $_POST['current_name'];
  $current_description = $_POST['current_description'];
  $current_share_path = "/$config_mount_target/$current_volume/$current_name";
  $current_group = $_POST['current_group'];

  if($group != $current_group){
      chgrp("$current_share_path", $group);
  }
  if($volume != $current_volume){
    exec("mv /$config_mount_target/$current_volume/$current_name /$config_mount_target/$volume");
  }
  if($name != $current_name){
    exec("mv $current_share_path $share_path");
    exec("mv /etc/samba/shares/$current_name /etc/samba/shares/$name");
    deleteLineInFile("/etc/samba/shares.conf","$current_name");
    $myFile = "/etc/samba/shares.conf";
     $fh = fopen($myFile, 'w') or die("not able to write to file");
     $stringData = "\ninclude = /etc/samba/shares/$name";
     fwrite($fh, $stringData);
     fclose($fh);

  }

  $myFile = "/etc/samba/shares/$name";
   $fh = fopen($myFile, 'w') or die("not able to write to file");
   $stringData = "[$name]\n   comment = $description\n   path = $share_path\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @$group\n   force group = $group\n   create mask = 0660\n   directory mask = 0770";
   fwrite($fh, $stringData);
   fclose($fh);

   exec ("systemctl restart smbd");

   echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_GET['share_delete']))
{
  $name = $_GET['share_delete'];

  $path = exec("find /$config_mount_target/*/$name -name $name");

  exec ("rm -rf $path");
  exec ("rm -f /etc/samba/shares/$name");

  deleteLineInFile("/etc/samba/shares.conf","$name");

  exec ("systemctl restart smbd");
  
  echo "<script>window.location = 'shares.php'</script>";
}

if(isset($_GET['wipe_hdd']))
{
  $hdd = $_GET['wipe_hdd'];
  $hdd_short_name = basename($hdd);

  exec ("sudo shred -v -n 1 $hdd 2> /tmp/shred-$hdd_short_name-progress&");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_GET['kill_pid']))
{
  $pid = $_GET['kill_pid'];

  exec ("sudo kill -9 $pid");
  
  echo "<script>window.location = 'ps.php'</script>";
}

if(isset($_GET['kill_wipe']))
{
  $hdd = $_GET['kill_wipe'];

  exec ("ps axu |grep 'shred -v -n 1 /dev/$hdd' | awk '{print $2}'", $pid);
  foreach ($pid as $pids) {
  exec ("sudo kill -9 $pids");
  echo "Killing<br>$pids<br>";
  }

  exec ("sudo rm -rf /tmp/shred-$hdd-progress");
  
  echo "<script>window.location = 'disk_list.php'</script>";
}

if(isset($_GET['delete_user']))
{
	$username = $_GET['delete_user'];

  exec("smbpasswd -x $username");
	exec("deluser --remove-home $username");
	
  echo "<script>window.location = 'users.php'</script>";
}

if(isset($_POST['group_add']))
{
	$group = $_POST['group'];

  exec ("addgroup $group");
  
  echo "<script>window.location = 'groups.php'</script>";
}

if(isset($_POST['group_modify_submit']))
{
	$group_id = check_input($_POST['group_id']);
	$group_name = check_input(ucwords($_POST['group_name']));
	$security = check_input($_POST['security']);

    $sql = "UPDATE groups SET group_name = '$group_name', security = '$security' WHERE group_id = '$group_id'";

    mysql_query($sql);
    echo "
    <script>
		window.location = '$document_root/group_list.php'
	</script>";
}

if(isset($_GET['delete_group']))
{
	$group = $_GET['delete_group'];

	exec("delgroup $group");

  echo "<script>window.location = 'groups.php'</script>";
}

//APP SECTION

if(isset($_POST['install_jellyfin'])){
  $volume = $_POST['volume'];
  
  if(!file_exists("/$config_mount_target/$config_docker_volume/jellyfin")) {
    exec ("addgroup media");
    $group_id = exec("getent group media | cut -d: -f3");

    mkdir("/$config_mount_target/$volume/media");
    mkdir("/$config_mount_target/$volume/media/tvshows");
    mkdir("/$config_mount_target/$volume/media/movies");
    mkdir("/$config_mount_target/$volume/media/music");
    mkdir("/$config_mount_target/$config_docker_volume/docker/jellyfin");
    mkdir("/$config_mount_target/$config_docker_volume/docker/jellyfin/config");
    mkdir("/$config_mount_target/$config_docker_volume/docker/jellyfin/cache");

    chgrp("/$config_mount_target/$volume/media","media");
    chgrp("/$config_mount_target/$volume/media/tvshows","media");
    chgrp("/$config_mount_target/$volume/media/movies","media");
    chgrp("/$config_mount_target/$volume/media/music","media");
    chgrp("/$config_mount_target/$config_docker_volume/docker/jellyfin","media");
    chgrp("/$config_mount_target/$config_docker_volume/docker/jellyfin/config","media");
    chgrp("/$config_mount_target/$config_docker_volume/docker/jellyfin/cache","media");
    
    chmod("/$config_mount_target/$volume/media",0770);
    chmod("/$config_mount_target/$volume/media/tvshows",0770);
    chmod("/$config_mount_target/$volume/media/movies",0770);
    chmod("/$config_mount_target/$volume/media/music",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/jellyfin",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/jellyfin/config",0770);
    chmod("/$config_mount_target/$config_docker_volume/docker/jellyfin/cache",0770);
    
    $myFile = "/etc/samba/shares/media";
     $fh = fopen($myFile, 'w') or die("not able to write to file");
     $stringData = "[media]\n   comment = Media files used by Jellyfin\n   path = /$config_mount_target/$volume/media\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @media\n   force group = media\n   create mask = 0660\n   directory mask = 0770";
     fwrite($fh, $stringData);
     fclose($fh);

     $myFile = "/etc/samba/shares.conf";
     $fh = fopen($myFile, 'a') or die("not able to write to file");
     $stringData = "\ninclude = /etc/samba/shares/media";
     fwrite($fh, $stringData);
     fclose($fh);
    
    exec ("systemctl restart smbd");

  }

  exec("docker run -d --name jellyfin --net=host --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/jellyfin/config:/config -v /$config_mount_target/$volume/media/tvshows:/tvshows -v /$config_mount_target/$volume/media/movies:/movies -v /$config_mount_target/$volume/media/music:/music -v /$config_mount_target/$config_docker_volume/docker/jellyfin/cache:/cache jellyfin/jellyfin");
  
  echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['update_jellyfin'])){

  $group_id = exec("getent group media | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/media -name 'media'");

  exec("docker pull jellyfin/jellyfin");
  exec("docker stop jellyfin");
  exec("docker rm jellyfin");
  
  exec("docker run -d --name jellyfin --net=host --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/jellyfin/config:/config -v $volume_path/tvshows:/tvshows -v $volume_path/movies:/movies -v $volume_path/music:/music -v /$config_mount_target/$config_docker_volume/docker/jellyfin/cache:/cache jellyfin/jellyfin");

  exec("docker image prune");
  
  echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['uninstall_jellyfin'])){
    //stop and delete docker container
    exec("docker stop jellyfin");
    exec("docker rm jellyfin");
    //delete media group
    exec ("delgroup media");
    //get path to media directory
    $path = exec("find /$config_mount_target/*/media -name media");
    //delete media directory
    exec ("rm -rf $path"); //Delete
    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/jellyfin");
    //delete samba share
    exec ("rm -f /etc/samba/shares/media");
    deleteLineInFile("/etc/samba/shares.conf","media");
    //restart samba
    exec ("systemctl restart smbd");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_POST['install_lychee']))
{
  $volume = $_POST['volume'];
  
  exec ("addgroup photos");
  $group_id = exec("getent group photos | cut -d: -f3");

  mkdir("/$config_mount_target/$volume/photos");
  mkdir("/$config_mount_target/$config_docker_volume/docker/lychee");
  mkdir("/$config_mount_target/$config_docker_volume/docker/lychee/config");

  chgrp("/$config_mount_target/$volume/photos","photos");
  
  chmod("/$config_mount_target/$volume/photos",0770);
     
  $myFile = "/etc/samba/shares/photos";
     $fh = fopen($myFile, 'w') or die("not able to write to file");
     $stringData = "[photos]\n   comment = Photos for Lychee\n   path = /$config_mount_target/$volume/photos\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @photos\n   force group = photos\n   create mask = 0660\n   directory mask = 0770";
     fwrite($fh, $stringData);
     fclose($fh);

     $myFile = "/etc/samba/shares.conf";
     $fh = fopen($myFile, 'a') or die("not able to write to file");
     $stringData = "\ninclude = /etc/samba/shares/photos";
     fwrite($fh, $stringData);
     fclose($fh);
    
    exec ("systemctl restart smbd");     

       exec("docker run -d --name lychee -p 4560:80 --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/lychee/config:/config -v /$config_mount_target/$volume/photos:/pictures linuxserver/lychee");
       echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['update_lychee'])){

  $group_id = exec("getent group photos | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/photos -name 'photos'");

  exec("docker pull linuxserver/lychee");
  exec("docker stop lychee");
  exec("docker rm lychee");

  exec("docker run -d --name lychee -p 4560:80 --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/lychee/config:/config -v $volume_path:/pictures linuxserver/lychee");

  exec("docker image prune");
  
  echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['uninstall_lychee'])){
    //stop and delete docker container
    exec("docker stop lychee");
    exec("docker rm lychee");
    //delete media group
    exec ("delgroup photos");
    //get path to media directory
    $path = exec("find /$config_mount_target/*/photos -name photos");
    //delete media directory
    exec ("rm -rf $path"); //Delete
    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/lychee");
    //delete samba share
    exec ("rm -f /etc/samba/shares/photos");
    deleteLineInFile("/etc/samba/shares.conf","photos");
    //restart samba
    exec ("systemctl restart smbd");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['install_nextcloud']))
{

  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud");
  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud/appdata");
  mkdir("/$config_mount_target/$config_docker_volume/docker/nextcloud/data");

  mkdir("/$config_mount_target/$config_docker_volume/docker/mariadb");

  exec("docker run -d --name mariadb -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=nextcloud -e MYSQL_USER=nextcloud -e MYSQL_PASSWORD=password -p 3306:3306 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/mariadb:/config linuxserver/mariadb");
     
  exec("docker run -d --name nextcloud -p 443:443 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/nextcloud/appdata:/config -v /$config_mount_target/$config_docker_volume/docker/nextcloud/data:/data -v /$config_mount_target:/$config_mount_target linuxserver/nextcloud");

  echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['update_nextcloud'])){

  exec("docker pull linuxserver/nextcloud");
  exec("docker stop nextcloud");
  exec("docker rm nextcloud");

  exec("docker run -d --name nextcloud -p 443:443 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/nextcloud/appdata:/config -v /$config_mount_target/$config_docker_volume/docker/nextcloud/data:/data -v /$config_mount_target:/$config_mount_target linuxserver/nextcloud");

  exec("docker image prune");
  
  echo "<script>window.location = 'packages.php'</script>";

}

if(isset($_GET['uninstall_nextcloud'])){
    //stop and delete docker container
    exec("docker stop nextcloud");
    exec("docker rm nextcloud");
    exec("docker stop mariadb");
    exec("docker rm mariadb");

    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/nextcloud");
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/mariadb");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_POST['install_dokuwiki']))
{
  $volume = $_POST['volume'];

  mkdir("/$config_mount_target/$config_docker_volume/docker/dokuwiki/");
  mkdir("/$config_mount_target/$config_docker_volume/docker/dokuwiki/config");

       exec("docker run -d --name dokuwiki -p 85:80 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/dokuwiki/config:/config linuxserver/dokuwiki");
       echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['update_dokuwiki'])){

  exec("docker pull linuxserver/dokuwiki");
  exec("docker stop dokuwiki");
  exec("docker rm dokuwiki");

  exec("docker run -d --name dokuwiki -p 85:80 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/dokuwiki/config:/config linuxserver/dokuwiki");

  exec("docker image prune");
  
  echo "<script>window.location = 'packages.php'</script>";

}

if(isset($_GET['uninstall_dokuwiki'])){
    //stop and delete docker container
    exec("docker stop dokuwiki");
    exec("docker rm dokuwiki");

    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/dokuwiki");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['install_syncthing']))
{
      mkdir("/$config_mount_target/$config_docker_volume/docker/syncthing/");
      mkdir("/$config_mount_target/$config_docker_volume/docker/syncthing/config");

      exec("docker run -d --name syncthing -p 8384:8384 -p 22000:22000 -p 21027:21027/udp --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/syncthing/config:/config -v /$config_mount_target/$config_docker_volume/$config_home_dir/johnny:/$config_mount_target/johnny -e PGID=100 -e PUID=1000 linuxserver/syncthing");
      echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['install_unifi']))
{
      mkdir("/$config_mount_target/$config_docker_volume/docker/unifi/");
      mkdir("/$config_mount_target/$config_docker_volume/docker/unifi/config");

      exec("docker run -d --name unifi -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/unifi/config:/config linuxserver/unifi-controller > /dev/null &");
      echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['update_unifi'])){

  exec("docker pull linuxserver/unifi-controller");
  exec("docker stop unifi");
  exec("docker rm unifi");

  exec("docker run -d --name unifi -p 3478:3478/udp -p 10001:10001/udp -p 8080:8080 -p 8081:8081 -p 8443:8443 -p 8843:8843 -p 8880:8880 -p 6789:6789 --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/unifi/config:/config linuxserver/unifi-controller");

  exec("docker image prune");
  
  echo "<script>window.location = 'packages.php'</script>";

}

if(isset($_GET['uninstall_unifi'])){
    //stop and delete docker container
    exec("docker stop unifi");
    exec("docker rm unifi");

    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/unifi");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}


if(isset($_POST['install_transmission']))
{
  $volume = $_POST['volume'];
  
  exec ("addgroup download");
  $group_id = exec("getent group download | cut -d: -f3");

  mkdir("/$config_mount_target/$volume/downloads");
  mkdir("/$config_mount_target/$volume/downloads/complete");
  mkdir("/$config_mount_target/$volume/downloads/incomplete");
  mkdir("/$config_mount_target/$volume/downloads/watch");
  mkdir("/$config_mount_target/$config_docker_volume/docker/transmission");
  mkdir("/$config_mount_target/$config_docker_volume/docker/transmission/config");

  chgrp("/$config_mount_target/$volume/downloads","download");
  chgrp("/$config_mount_target/$volume/downloads/complete","download");
  chgrp("/$config_mount_target/$volume/downloads/incomplete","download");
  chgrp("/$config_mount_target/$volume/downloads/watch","download");
  chgrp("/$config_mount_target/$config_docker_volume/docker/transmission","download");
  chgrp("/$config_mount_target/$config_docker_volume/docker/transmission/config","download");

  chmod("/$config_mount_target/$volume/downloads",0770);
  chmod("/$config_mount_target/$volume/downloads/complete",0770);
  chmod("/$config_mount_target/$volume/downloads/incomplete",0770);
  chmod("/$config_mount_target/$volume/downloads/watch",0770);
  chmod("/$config_mount_target/$config_docker_volume/docker/transmission",0770);
  chmod("/$config_mount_target/$config_docker_volume/docker/transmission/config",0770);
  
  $myFile = "/etc/samba/shares/downloads";
     $fh = fopen($myFile, 'w') or die("not able to write to file");
     $stringData = "[downloads]\n   comment = Torrent Downloads used by Transmission\n   path = /$config_mount_target/$volume/downloads\n   browsable = yes\n   writable = yes\n   guest ok = yes\n   read only = no\n   valid users = @download\n   force group = download\n   create mask = 0660\n   directory mask = 0770";
     fwrite($fh, $stringData);
     fclose($fh);

     $myFile = "/etc/samba/shares.conf";
     $fh = fopen($myFile, 'a') or die("not able to write to file");
     $stringData = "\ninclude = /etc/samba/shares/downloads";
     fwrite($fh, $stringData);
     fclose($fh);
    
    exec ("systemctl restart smbd");

       exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/transmission/config:/config -v /$config_mount_target/$volume/downloads/watch:/watch -v /$config_mount_target/$volume/downloads:/downloads -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");
       echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['update_transmission'])){

  $group_id = exec("getent group download | cut -d: -f3");
  $volume_path = exec("find /$config_mount_target/*/downloads -name 'downloads'");

  exec("docker pull linuxserver/transmission");
  exec("docker stop transmission");
  exec("docker rm transmission");

  exec("docker run -d --name transmission --restart=unless-stopped -e PGID=$group_id -e PUID=0 -v /$config_mount_target/$config_docker_volume/docker/transmission/config:/config -v /$config_mount_target/$config_docker_volume/docker/transmission/watch:/watch -v $volume_path:/downloads -p 9091:9091 -p 51413:51413 -p 51413:51413/udp linuxserver/transmission");

  exec("docker image prune");
  
  echo "<script>window.location = 'packages.php'</script>";

}

if(isset($_GET['uninstall_transmission'])){
    //stop and delete docker container
    exec("docker stop transmission");
    exec("docker rm transmission");
    //delete group
    exec ("delgroup download");
    //get path to media directory
    $path = exec("find /$config_mount_target/*/downloads -name downloads");
    //delete directory
    exec ("rm -rf $path"); //Delete
    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/transmission");
    //delete samba share
    exec ("rm -f /etc/samba/shares/downloads");
    deleteLineInFile("/etc/samba/shares.conf","downloads");
    //restart samba
    exec ("systemctl restart smbd");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['install_openvpn']))
{

  mkdir("/$config_mount_target/$config_docker_volume/docker/openvpn");
  mkdir("/$config_mount_target/$config_docker_volume/docker/openvpn/config");

  exec("docker run -d --name openvpn --restart=unless-stopped -v /$config_mount_target/$config_docker_volume/docker/openvpn/config:/config -p 943:943 -p 9443:9443 -p 1194:1194/udp linuxserver/openvpn-as");
  echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_GET['uninstall_openvpn'])){
    //stop and delete docker container
    exec("docker stop openvpn");
    exec("docker rm openvpn");

    //delete docker config
    exec ("rm -rf /$config_mount_target/$config_docker_volume/docker/openvpn");
    //redirect back to packages
    echo "<script>window.location = 'packages.php'</script>";
}

if(isset($_POST['setup']))
{
  $volume_name = $_POST['volume_name'];
  $hdd = $_POST['disk'];
  $hdd_part = $hdd."1";
  $hostname = $_POST['hostname'];  
  $username = $_POST['username'];
  $password = $_POST['password'];

  $os_disk = exec("findmnt -n -o SOURCE --target /");

  //Create config.php file
  
  $myfile = fopen("config.php", "w");

  $txt = "<?php\n\n\$config_os_disk = \"$os_disk\";\n\$config_mount_target = 'mnt';\n\$config_docker_volume = \"$volume_name\";\n\$config_home_volume = \"$volume_name\";\n\$config_home_dir = 'homes';\n\n?>";

  fwrite($myfile, $txt);

  fclose($myfile);

  include("config.php");

  exec("echo $hostname > /etc/hostname");
  //exec("echo '127.0.0.1     $hostname localhost.localdomain localhost' > /etc/hosts");
  exec("hostname $hostname");
  //exec("service networking restart");

  exec ("wipefs -a $hdd");
  exec ("(echo o; echo n; echo p; echo 1; echo; echo; echo w) | fdisk $hdd");
  exec ("mkdir /$config_mount_target/$volume_name");
  exec ("mkfs.ext4 $hdd_part");
  exec ("mount $hdd_part /$config_mount_target/$volume_name");

  exec ("mkdir /$config_mount_target/$volume_name/docker");
  exec ("mkdir /$config_mount_target/$volume_name/homes");

  exec ("useradd -g users -m -d /$config_mount_target/$config_home_volume/$config_home_dir/$username $username -p $password");
  exec ("echo '$password\n$password' | smbpasswd -a $username");

  exec ("chmod -R 700 /$config_mount_target/$volume_name/$config_home_dir/$username");

  exec("service smbd restart");
  
  $myFile = "/etc/fstab";
  $fh = fopen($myFile, 'a') or die("can't open file");
  $stringData = "$hdd_part    /$config_mount_target/$volume_name      ext4    rw,relatime,data=ordered 0 2\n";
  fwrite($fh, $stringData);
  fclose($fh);

  echo "<script>window.location = 'dashboard.php'</script>";
}

if(isset($_GET['reset']))
{
  //Stop Samba
  exec("service smbd stop");
  exec("service nmbd stop");

  //Remove and stop all Dockers and docker images
  exec ("docker stop $(docker ps -aq)");
  exec ("docker rm $(docker ps -aq)");
  exec ("docker rmi $(docker images -q)");
  
  //Remove all created groups
  exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nobody | grep -v nogroup", $group_array);
  foreach ($group_array as $group) {
    exec("delgroup $group");
  }

  //Remove all created users
  exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $username_array);
  foreach ($username_array as $username) {
    exec("smbpasswd -x $username");
    exec("deluser --remove-home $username");
  }

  //Remove all Volumes and remove from fstab.conf to prevent automounting on boot
  exec("ls /$config_mount_target", $volume_array);
  foreach ($volume_array as $volume) {
    $hdd = exec("find /$config_mount_target -n -o SOURCE --target /$config_mount_target/$volume");
    exec ("umount /$config_mount_target/$volume");
    exec("rm -rf /$config_mount_target/$volume");
    deleteLineInFile("/etc/fstab","$hdd");
  }

  //Wipe Each Disk
  exec("smartctl --scan|awk '{ print $1 '}", $drive_list);
  foreach ($drive_list as $disk) {
    exec("wipefs -a /dev/$disk");
  }

  //Remove Samba conf and replace it with the default
  exec ("rm /etc/samba/smb.conf");
  exec ("cp /simpnas/smb.conf /etc/samba/");

  exec("service smbd start");
  exec("service nmbd start");

  echo "<script>window.location = 'dashboard.php'</script>";
}

?>