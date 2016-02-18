<?php
/*
Plugin Name: WP Antivirus Site Protection (by SiteGuarding.com)
Plugin URI: http://www.siteguarding.com/en/website-extensions
Description: Adds more security for your WordPress website. Server-side scanning. Performs deep website scans of all the files. Virus and Malware detection.
Version: 5.6
Author: SiteGuarding.com (SafetyBis Ltd.)
Author URI: http://www.siteguarding.com
License: GPLv2
TextDomain: plgavp
*/
define( 'SITEGUARDING_SERVER', 'http://www.siteguarding.com/ext/antivirus/index.php');
define( 'SITEGUARDING_SERVER_IP1', '185.72.156.128');
define( 'SITEGUARDING_SERVER_IP2', '185.72.156.129');

define( 'SGAVP_UPDATE', true);


if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
else define('DIRSEP', '/');

//error_reporting(E_ERROR | E_WARNING);
//error_reporting(E_ERROR);

// Cron check
if( !is_admin() ) 
{
	if ( isset($_GET['task']) && $_GET['task'] == 'upgrade' )
	{
		error_reporting(0);
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key && ($_SERVER["REMOTE_ADDR"] == SITEGUARDING_SERVER_IP1 || $_SERVER["REMOTE_ADDR"] == SITEGUARDING_SERVER_IP2))
		{
				include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
				
				if (!class_exists('SGAntiVirus_module'))
				{
					// Error module is not loaded
					exit;
				}
				
				$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $access_key);
				$version = $_GET['version'];
				
				// Download version from Wordpress.org
				$result = SGAntiVirus::DownloadFromWordpress($version);
				if ($result === false) die('Cant download new version from wordpress.org');
				
				// Update
				/*if (function_exists('system'))
				{
					$extract_path = ABSPATH.'plugins'.DIRSEP.'1'.DIRSEP;
					$cmd = "unzip ".dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'update.zip'." -d ".$extract_path;
					echo $cmd;
					system($cmd);
				}
				else */if (class_exists('ZipArchive'))
			    {
			    	$zip = new ZipArchive;
					if ($zip->open(dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'update.zip') === TRUE) {
						$extract_path = ABSPATH.'wp-content'.DIRSEP.'plugins'.DIRSEP;
						
						//echo "Extract path: ".$extract_path."<br>";
						
					    $unzip_status = $zip->extractTo($extract_path);
					    
					    /*for($i = 0; $i < $zip->numFiles; $i++) 
						{
							$filename = $zip->getNameIndex($i);
							echo $filename."<br>";
					        $zip->extractTo($extract_path, array($zip->getNameIndex($i)));
					    }*/
    
					    $zip->close();
					    if ($unzip_status === false) echo 'Unzip failed'."<br>";
					    echo 'Update finished'."<br>";
					} else {
					    echo 'Update failed'."<br>";
					}
		    	}
		}
		else die('access_key or IP is not correct');
		
		exit;
	}
	
	
	if ( isset($_GET['task']) && $_GET['task'] == 'cron' )
	{
		error_reporting(0);
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key)
		{
				include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
				
				if (!class_exists('SGAntiVirus_module'))
				{
					// Error module is not loaded
					exit;
				}
				
				$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $access_key);
				$session_report_key = md5($domain.'-'.rand(1,1000).'-'.time());
				
				SGAntiVirus_module::MembershipFile($license_info['membership'], $license_info['scans'], $params['show_protectedby']);
				
				// Prepare scan
				$_POST['scan_path'] = ABSPATH;
				$_POST['access_key'] = $access_key;
				$_POST['do_evristic'] = $params['do_evristic'];
				$_POST['domain'] = get_site_url();
				$_POST['email'] = get_option( 'admin_email' );
				$_POST['session_report_key'] = $session_report_key;
				$_POST['membership'] = $license_info['membership'];
				
				// Start scan
				SGAntiVirus_module::scan(false, false);
		}
		
		exit;
	}
	
	// Remote request malware files
	if ( isset($_GET['task']) && $_GET['task'] == 'get_malware_files' )
	{
		error_reporting(0);
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key)
		{
				include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
				
				if (!class_exists('SGAntiVirus_module'))
				{
					// Error module is not loaded
					echo 'Error module is not loaded';
					exit;
				}
				
				
				$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $params['access_key']);
	
				if ($license_info === false) { echo 'Wrong access_key'; exit; }
				
				
				if (intval($_GET['showcontent']) == 1)
				{
					SGAntiVirus::ShowFilesForAnalyze($license_info['last_scan_files']);
					exit;
				}
				

				$a = SGAntiVirus::SendFilesForAnalyze( $license_info['last_scan_files'], $license_info['email'] );
				if ($a === true)
				{
					$tmp_txt = 'Files sent for analyze. You will get report by email '.$license_info['email'].' Files:'.print_r( $license_info['last_scan_files'],true);
					
					$result_txt = array(
						'status' => 'OK',
						'description' => $tmp_txt
					);
					SGAntiVirus_module::DebugLog($tmp_txt);
				}
				else {
					$tmp_txt = 'Operation is failed. Nothing sent for analyze. Files:'.print_r( $license_info['last_scan_files'],true);
					
					$result_txt = array(
						'status' => 'ERROR',
						'description' => $tmp_txt
					);
					SGAntiVirus_module::DebugLog($tmp_txt);
				}
				
				echo json_encode($result_txt);
		}
		
		exit;
	}
	
	
	// Remote request malware files
	if ( isset($_GET['task']) && $_GET['task'] == 'remove_malware_files' )
	{
		error_reporting(0);
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key && ($_SERVER["REMOTE_ADDR"] == SITEGUARDING_SERVER_IP1 || $_SERVER["REMOTE_ADDR"] == SITEGUARDING_SERVER_IP2))
		{
				include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
				
				if (!class_exists('SGAntiVirus_module'))
				{
					// Error module is not loaded
					exit;
				}
				
				$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $params['access_key']);
	
				if ($license_info === false) { exit; }
				
				
				$a = SGAntiVirus::QuarantineFiles($license_info['last_scan_files']['main']);
				if ($a === true)
				{
					SGAntiVirus_module::DebugLog('Malware moved to quarantine and deleted from the server. Files:'.print_r( $license_info['last_scan_files'],true));
				}
				else {
					SGAntiVirus_module::DebugLog('Operation is failed. Some files are not moved to quarantine or not deleted. Files:'.print_r( $license_info['last_scan_files'],true) );
				}
				
				$a = SGAntiVirus::QuarantineFiles($license_info['last_scan_files']['heuristic']);
				if ($a === true)
				{
					SGAntiVirus_module::DebugLog('Malware moved to quarantine and deleted from the server. Files:'.print_r( $license_info['last_scan_files'],true));
				}
				else {
					SGAntiVirus_module::DebugLog('Operation is failed. Some files are not moved to quarantine or not deleted. Files:'.print_r( $license_info['last_scan_files'],true) );
				}
				
		}
		else die('access_key or IP is not correct');
		
		exit;
	}
	
	
	
	if ( isset($_GET['task']) && $_GET['task'] == 'status' )
	{
		error_reporting(0);
		
		include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key)
		{
			$a = array(
				'status' => 'ok',
				'answer' => md5($_GET['answer']),
				'version' => SGAntiVirus_module::$antivirus_version
			);
			
			echo json_encode($a);
		}
		
		exit;
	}
    
    
    
	if ( isset($_GET['task']) && $_GET['task'] == 'settings' )
	{
		error_reporting(0);
		
		include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key)
		{
			$settings_name = trim($_GET['settings_name']);
			$settings_value = trim($_GET['settings_value']);
            
            $settings = SGAntiVirus_module::UpdateSettungsValue($settings_name, $settings_value);
            
            echo print_r($settings, true);
		}
		
		exit;
	}
	
	
	if ( isset($_GET['task']) && $_GET['task'] == 'view_file' )
	{
		error_reporting(0);
		
		include_once(dirname(__FILE__).DIRSEP.'sgantivirus.class.php');
		
		$access_key = trim($_GET['access_key']);
	
		$params = plgwpavp_GetExtraParams();
	
		if ($params['access_key'] == $access_key && ($_SERVER["REMOTE_ADDR"] == SITEGUARDING_SERVER_IP1 || $_SERVER["REMOTE_ADDR"] == SITEGUARDING_SERVER_IP2))
		{
			$filename = $_GET['file'];
			
			switch ($filename)
			{
				case 'debug':
					$filename = dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'debug.log';
					break;
					
				case 'filelist':
					$filename = dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'filelist.txt';
					break;
					
				default:
					$filename = ABSPATH.$filename;
			}
			
			echo "\n\n";
			
			if (file_exists($filename)) echo 'File exists: '.$filename."\n";
			else {echo 'File is absent: '.$filename."\n\n"; exit;}
			
			echo 'File size: '.filesize($filename)."\n";
			echo 'File MD5: '.strtoupper(md5_file($filename))."\n\n";
			
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
			fclose($handle);
			echo '----- File Content [start] -----'."\n";
			echo $contents;
			echo '----- File Content [end] -----'."\n";
		}
		else die('access_key or IP is not correct');
		
		exit;
	}
	
	
	
	/*
	function plgavp_login_head_add_field()
	{
		$params = plgwpavp_GetExtraParams();

		if ( (isset($params['show_protectedby']) && $params['show_protectedby'] == 1) || $params['membership'] == 'free')
		{
		?>
			<div style="font-size:11px; padding:3px 0;position: fixed;bottom:0;z-index:10;width:100%;text-align:center;background-color:#F1F1F1">Protected with <a href="https://www.siteguarding.com/en/website-antivirus" target="_blank">antivirus</a> developed by <a href="https://www.siteguarding.com" target="_blank" title="SiteGuarding.com - Website Security. Website Antivirus Protection. Malware Removal services. Professional security services against hacker activity.">SiteGuarding.com</a></div>
		<?php
		}
		
	}
	add_action( 'login_head', 'plgavp_login_head_add_field' );
	*/

	// Show Protected by
	function plgavp_footer_protectedby() 
	{
		if ( file_exists( dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'membership.log'))
		{
			?>
				<div style="font-size:10px; padding:0 2px;position: fixed;bottom:0;right:0;z-index:1000;text-align:center;background-color:#F1F1F1;color:#222;opacity:0.8;">Protected with <a style="color:#4B9307" href="https://www.siteguarding.com/en/website-antivirus" target="_blank" title="Security service that protects your website against malware and hacker exploits. Website Antivirus protection.">SiteGuarding.com Antivirus</a></div>
			<?php
		}	
	}
	add_action('wp_footer', 'plgavp_footer_protectedby', 100);

}




if( is_admin() ) {
	
	error_reporting(0);
	
	
	function plgwpavp_activation()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgwpavp_config';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `var_name` char(255) CHARACTER SET utf8 NOT NULL,
                `var_value` char(255) CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
            

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
            
            // Notify user
   			include_once(dirname(__FILE__).'/sgantivirus.class.php');
            $message = 'Dear Customer!'."<br><br>";
			$message .= 'Thank you for installation of our security plugin. We will do the best to keep your website safe and secured.'."<br><br>";
			$message .= 'One more step to secure your website. Please login to Dashboard of your WordPress website. Find in menu "Antivirus", follow the instructions.'."<br><br>";
			$message .= 'Please visit <a href="https://www.siteguarding.com/en/website-extensions">SiteGuarding.com Extentions<a> and learn more about our security solutions.'."<br><br>";
			$subject = 'Antivirus Installation';
			$email = get_option( 'admin_email' );
			
			SGAntiVirus_module::SendEmail($email, $message, $subject);
		}
	}
	register_activation_hook( __FILE__, 'plgwpavp_activation' );
    
    
	function plgwpavp_uninstall()
	{
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgwpavp_config';
		$wpdb->query( 'DROP TABLE ' . $table_name );
		
	}
	register_uninstall_hook( __FILE__, 'plgwpavp_uninstall' );
	
	
    
	add_action( 'admin_init', 'plgavp_admin_init' );
	function plgavp_admin_init()
	{
		wp_register_style( 'plgavp_LoadStyle', plugins_url('css/antivirus.css', __FILE__) );	
	}



	$avp_params = plgwpavp_GetExtraParams();
	if (count($avp_params) && $avp_params !== false)
	{
		$avp_license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $avp_params['access_key']);
		
        /**
        * Global Alerts
        */
		// Save membership type
		$data = array('membership' => $avp_license_info['membership']);
		plgwpavp_SetExtraParams($data);
		
		$avp_alert_main = 0;
		if (count($avp_license_info['last_scan_files']['main']))
		{
			foreach ($avp_license_info['last_scan_files']['main'] as $tmp_file)
			{
				if (file_exists(ABSPATH.'/'.$tmp_file)) $avp_alert_main++;
			}
		}
		if ($avp_license_info['membership'] != 'pro') $avp_alert_main = $avp_license_info['last_scan_files_counters']['main'];
	
		$avp_alert_heuristic = 0;
		if (count($avp_license_info['last_scan_files']['heuristic']))
		{
			foreach ($avp_license_info['last_scan_files']['heuristic'] as $tmp_file)
			{
				if (file_exists(ABSPATH.'/'.$tmp_file)) $avp_alert_heuristic++;
			}
		}
		if ($avp_license_info['membership'] != 'pro') $avp_alert_heuristic = $avp_license_info['last_scan_files_counters']['heuristic'];
	
		if ($avp_alert_main > 0 || $avp_alert_heuristic > 0)
		{
			$avp_alert_txt = '<span class="update-plugins"><span class="update-count">'.$avp_alert_main.'/'.$avp_alert_heuristic.'</span></span>';	
		} 
		else $avp_alert_txt = '';
		
	
    if (isset($avp_license_info['membership']))	
    {
		if ($avp_alert_main > 0 || $avp_alert_heuristic > 0 || $avp_license_info['blacklist']['google'] != 'ok' )
		{
			$avp_eachpage_alert_txt = '<b>Antivirus Important Notice:</b>';
			if ($avp_alert_main > 0)
			{
				$avp_eachpage_alert_txt .= ' Virus code detected: '.$avp_alert_main.' file(s).'; 
			}
			if ($avp_alert_heuristic > 0)
			{
				$avp_eachpage_alert_txt .= ' Unsafe code detected: '.$avp_alert_heuristic.' file(s).'; 
			}
 			if (isset($avp_license_info['blacklist']['google']) && $avp_license_info['blacklist']['google'] != 'ok')
			{
				$avp_eachpage_alert_txt .= ' Blacklisted, Reason ['.$avp_license_info['blacklist']['google'].']'; 
			}
		} 
		else if ($avp_license_info['membership'] != 'pro' && $avp_license_info['membership'] != 'trial') $avp_eachpage_alert_txt .= '<b>Antivirus Important Notice:</b> Your license is expired and antivirus has limits. Some features are disabled.';
			else  $avp_eachpage_alert_txt = '';
		
		if ($avp_license_info['membership'] != 'pro' && $avp_license_info['membership'] != 'trial') $avp_eachpage_alert_txt .= '';
     }   
        /**
         * Global Updates and Restoring
         */ 
        if ($avp_params['last_core_update'] < date("Y-m-d") && SGAVP_UPDATE === true)  
        {
    		$data = array('last_core_update' => date("Y-m-d"));
    		plgwpavp_SetExtraParams($data);
            
			$result = SGAntiVirus::DownloadFromWordpress_Link($avp_license_info['update_url']);
			if ($result === true && class_exists('ZipArchive')) 
		    {
        		if (!defined('ABSPATH') || strlen(ABSPATH) < 8) 
        		{
        			$site_path = dirname(__FILE__);
        			$site_path = str_replace(DIRSEP.'wp-content'.DIRSEP.'plugins'.DIRSEP.'wp-antivirus-site-protection', DIRSEP, $site_path);
        		}
                else $site_path = ABSPATH;
                
		    	$zip = new ZipArchive;
				if ($zip->open(dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'update.zip') === TRUE) 
                {
					$extract_path = $site_path.'wp-content'.DIRSEP.'plugins'.DIRSEP;

				    $unzip_status = $zip->extractTo($extract_path);

				    $zip->close();
				    
				} 
	    	}
        }      
	}


	add_action('admin_menu', 'register_plgavp_settings_page');

	
	function antivirus_admin_notice() 
	{
		global $avp_eachpage_alert_txt;
		
		if ($avp_eachpage_alert_txt != '') 
		{
	    ?>
		    <div class="error">
		        <p style="color:#DD3D36;font-size:20px;"><?php echo $avp_eachpage_alert_txt; ?></p>
		        <p><a href="/wp-admin/admin.php?page=plgavp_Antivirus">View details</a></p>
		    </div>
		    <?php
	    }
	}
	add_action( 'admin_notices', 'antivirus_admin_notice' );




	function register_plgavp_settings_page() 
	{
		global $avp_alert_txt;
		
		add_menu_page('plgavp_Antivirus', 'Antivirus'.$avp_alert_txt, 'activate_plugins', 'plgavp_Antivirus', 'plgavp_settings_page_callback', plugins_url('images/', __FILE__).'antivirus-logo.png');
	}

	function plgavp_settings_page_callback() 
	{
		// PHP version check
        $php_version = explode('.', PHP_VERSION);
        $php_version = floatval($php_version[0].'.'.$php_version[1]);
        
		/*if ($php_version <= 5.2)
		{
			// Error class module is not loaded
			SGAntiVirus::ShowMessage('Your PHP version is too old ['.PHP_VERSION.']. Please ask your hoster to upgrade PHP.<br><br>This version for PHP 5.3 and older. If you want to use our scanner on your server please download WP Antivirus version 4.8.2. <a href="https://www.siteguarding.com/files/wp-antivirus-site-protection-4.8.2.zip">Click to download</a>');
			return;
		}*/
        
		// Load class
		if (!file_exists(dirname(__FILE__).'/sgantivirus.class.php'))
		{
			// Error class module is not loaded
			SGAntiVirus::ShowMessage('File '.dirname(__FILE__).'/sgantivirus.class.php is not exist.');
			return;
		}
		
		include_once(dirname(__FILE__).'/sgantivirus.class.php');
		
		if (!class_exists('SGAntiVirus_module'))
		{
			// Error module is not loaded
			SGAntiVirus::ShowMessage('Main antivirus scanner module is not loaded. Please try again.');
			return;
		}
		
		wp_enqueue_style( 'plgavp_LoadStyle' );
		
		?>
			<h2 class="avp_header icon_radar">WP Antivirus Site Protection</h2>
			
		<?php
		
		
		// Actions
		// Confirm Registration
		if (isset($_POST['action']) && $_POST['action'] == 'ConfirmRegistration' && check_admin_referer( 'name_254f4bd3ea8d' ))
		{
			$errors = SGAntiVirus::checkServerSettings(true);
			$access_key = md5(time().get_site_url());
			$email = trim($_POST['email']);
			$result = SGAntiVirus::sendRegistration(get_site_url(), $email, $access_key, $errors);
			if ($result === true)
			{
				$data = array('registered' => 1, 'email' => $email, 'access_key' => $access_key);
				plgwpavp_SetExtraParams($data);
				
				// Send access_key to user
				$message = 'Dear Customer!'."<br><br>";
				$message .= 'Thank you for registration your copy of WP Antivirus Site Protection. Please keep this email for your records, it contains your registration information and you will need it in the future.'."<br><br>";
				$message .= '<b>Registration information:</b>'."<br><br>";
				$message .= '<b>Domain:</b> '.get_site_url()."<br>";
				$message .= '<b>Email:</b> '.$email."<br>";
				$message .= '<b>Access Key:</b> '.$access_key."<br><br>";
				$subject = 'AntiVirus Registration Information';
				
				SGAntiVirus_module::SendEmail($email, $message, $subject);
			}
			else {
				// Show error
				SGAntiVirus::ShowMessage($result);
				return;
			}
		}
		
		// Start Scan
		if (isset($_POST['action']) && $_POST['action'] == 'StartScan' && check_admin_referer( 'name_254f4bd3ea8d' ))
		{
			$data = array('allow_scan' => intval($_POST['allow_scan']), 'do_evristic' => intval($_POST['do_evristic']));
			plgwpavp_SetExtraParams($data);
			
			$params = plgwpavp_GetExtraParams();
			
			// Check if something in progress
			$progress_info = SGAntiVirus::GetProgressInfo(get_site_url(), $params['access_key']);
			if ($progress_info['in_progress'] > 0)
			{
				$msg = 'Another scanning process is in progress. In 5-10 minutes you will get report by email or it will be available in Latest Reports section.';
				SGAntiVirus::ShowMessage($msg);
				return;
			} 
			
			global $avp_license_info;
			$session_id = md5(time().'-'.rand(1,10000));
			ob_start();
			session_start();
			ob_end_clean();
			$_SESSION['scan']['session_id'] = $session_id;
			SGAntiVirus::ScanProgress($session_id, ABSPATH, $params, $avp_license_info);
			return;
		}
		

		// Quarantine & Malware remove
		if (isset($_POST['action']) && $_POST['action'] == 'QuarantineFiles' && check_admin_referer( 'name_254f4bd3ea8d' ))
		{
			$params = plgwpavp_GetExtraParams();
			
			$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $params['access_key']);

			if ($license_info === false) { SGAntiVirus::page_ConfirmRegistration(); return; }
			
			if ($license_info['membership'] == 'pro')
			{ 
				if (isset($_POST['filelist']))
				{
					$filelist_type = trim($_POST['filelist']);
					switch($filelist_type)
					{
						case 'main':
						case 'heuristic':
							$a = SGAntiVirus::QuarantineFiles($license_info['last_scan_files'][$filelist_type]);
							break;
							
						default:
							die('filelist is not allowed');
							break;
					}	
				}
					
				if ($a === true)
				{
					SGAntiVirus::ShowMessage('Malware moved to quarantine and deleted from the server.');	
				}
				else {
					SGAntiVirus::ShowMessage('Operation is failed. Some files are not moved to quarantine or not deleted.', 'error');
				}
			}
		}
		
		
		// Send files to SiteGuarding.com
		if (isset($_POST['action']) && $_POST['action'] == 'SendFilesForAnalyze' && check_admin_referer( 'name_254f4bd3ea8d' ))
		{
			$params = plgwpavp_GetExtraParams();
			
			$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $params['access_key']);

			if ($license_info === false) { SGAntiVirus::page_ConfirmRegistration(); return; }
			
			if ($license_info['membership'] == 'pro')
			{ 
				$a = SGAntiVirus::SendFilesForAnalyze($license_info['last_scan_files'], $license_info['email'] );	
				if ($a === true)
				{
					SGAntiVirus::ShowMessage('Files sent for analyze. SiteGuarding.com support will contact with you within 24-48 hours. You will get report by email '.$license_info['email']);	
				}
				else {
					SGAntiVirus::ShowMessage('Operation is failed. Nothing sent for analyze.', 'error');
				}
			}
		}
		
		
		
		


		// Get params
		$params = plgwpavp_GetExtraParams();
		
		
		// Check if website is registered
		//SGAntiVirus::page_ConfirmRegistration(); return;
		if (!isset($params['registered']) || intval($params['registered']) == 0) { SGAntiVirus::page_ConfirmRegistration(); return; }
		
		// Get data from siteguading about number of scans and exp date
		$license_info = SGAntiVirus::GetLicenseInfo(get_site_url(), $params['access_key']);
		if ($license_info === false) { SGAntiVirus::page_ConfirmRegistration(); return; }
		
		// Check server settings
		if (!SGAntiVirus::checkServerSettings()) return;

		/*
		echo '<pre>';
		print_r($license_info);
		print_r($params);
		echo '</pre>';
		*/
		
		

		global $avp_license_info;
		SGAntiVirus_module::MembershipFile($avp_license_info['membership'], $avp_license_info['scans'], $params['show_protectedby']);



		foreach ($license_info as $k => $v)
		{
			$params[$k] = $v;	
		}
		
		
		SGAntiVirus::page_PreScan($params);

	}
	
	


	add_action('admin_menu', 'register_plgavp_results_subpage');

	function register_plgavp_results_subpage() {
		global $avp_alert_txt;
		add_submenu_page( 'plgavp_Antivirus', 'Scan Results', 'Scan Results'.$avp_alert_txt, 'manage_options', 'plgavp_Antivirus_results_page', 'plgavp_antivirus_results_page_callback' ); 
	}
	
	
	function plgavp_antivirus_results_page_callback()
	{
		wp_enqueue_style( 'plgavp_LoadStyle' );
		
		
		global $avp_license_info;
		
		$params = $avp_license_info;
		
		?>
			<h2 class="avp_header icon_radar">WP Antivirus Scan Results</h2>		


			<h3>Latest Scan Result</h3>	
			
			<div class="mod-box"><div>	
			
			<?php
			if ($params['membership'] == 'free') 
			{
				?>
				<span class="msg_box msg_error">Quarantine & Malware Removal feature is disabled. Available in PRO version only. <a href="https://www.siteguarding.com/en/buy-service/antivirus-site-protection?domain=<?php echo urlencode( get_site_url() ); ?>&email=<?php echo urlencode(get_option( 'admin_email' )); ?>" target="_blank">Upgrade to PRO version</a></span>
				<?php	
			}
			?>

			<?php
			if ( $params['last_scan_files_counters']['main'] == 0 || $params['last_scan_files_counters']['heuristic'] == 0 )
			{
				echo '<p>No files for review.</p>';
			}
			if (count($params['last_scan_files']['main']))
			{
				// Check files
				foreach ($params['last_scan_files']['main'] as $k => $tmp_file)
				{
					if (!file_exists(ABSPATH.'/'.$tmp_file)) unset($params['last_scan_files']['main'][$k]);
				}
				
				if (count($params['last_scan_files']['main']) > 0)
				{
					?>
					<div class="avp_latestfiles_block">
					<h4>Action is required</h4>
					
					<?php
					foreach ($params['last_scan_files']['main'] as $tmp_file)
					{
						echo '<p>'.$tmp_file.'</p>';
					}
					?>
	
					<br />
					
					<div class="divTable">
					<div class="divRow">
					<div class="divCell">
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Send Files to SiteGuarding.com">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Send Files to SiteGuarding.com" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					?>
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="SendFilesForAnalyze"/>
					</form>
					</div>
					
					<div class="divCell">&nbsp;</div>

					<div class="divCell">
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Quarantine & Remove malware" onclick="return confirm('Before use this feature, please make sure that you have sent the files for analyze and got reply from SiteGuarding.com\nMove files to quarantine?')">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Quarantine & Remove malware" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					?>
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="QuarantineFiles"/>
					</form>
					
					</div></div></div>
					* Please note: Hackers can inject malware codes inside of the normal files. If you delete these files, website can stop to work or will be not stable. We advice to send request to SiteGuarding.com for file review and analyze. 
					
					</div>
					<?php
				}

			}
			
			
			if (count($params['last_scan_files']['heuristic']))
			{
				// Check files
				foreach ($params['last_scan_files']['heuristic'] as $k => $tmp_file)
				{
					if (!file_exists(ABSPATH.'/'.$tmp_file)) unset($params['last_scan_files']['heuristic'][$k]);
				}
				
				if (count($params['last_scan_files']['heuristic']) > 0)
				{
					?>
					<div class="avp_latestfiles_block">
					<h4>Review is required</h4>
					<?php
					foreach ($params['last_scan_files']['heuristic'] as $tmp_file)
					{
						echo '<p>'.$tmp_file.'</p>';
					}
					
					?>
					<br />
					<?php
					
					if ($params['whitelist_filters_enabled'] == 1)
					{
						?>
						<span class="msg_box msg_warning">White List is enabled.</span><br /><br />
						<?php
					}
					?>
					
					
					<div class="divTable">
					<div class="divRow">
					<div class="divCell">
					
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Send Files to SiteGuarding.com">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Send Files to SiteGuarding.com" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="SendFilesForAnalyze"/>
					</form>
					
					</div>
					
					<div class="divCell">&nbsp;</div>

					<div class="divCell">
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Quarantine & Remove malware" onclick="return confirm('Before use this feature, please make sure that you have sent the files for analyze and got reply from SiteGuarding.com\nMove files to quarantine?')">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Quarantine & Remove malware" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					?>
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="QuarantineFiles"/>
					<input type="hidden" name="filelist" value="heuristic"/>
					</form>
					
					</div></div></div>
					* Please note: Hackers can inject malware codes inside of the normal files. If you delete these files, website can stop to work or will be not stable. We advice to send request to SiteGuarding.com for file review and analyze. 
					
					
					</div>
					<?php
				}
			}
			
		
			?>
			
			
			<img class="imgpos" alt="WP Antivirus Site Protection" src="<?php echo plugins_url('images/', __FILE__).'mid_box.png'; ?>" width="110" height="70">
						
			</div></div>
					
					<?php SGAntiVirus::HelpBlock();
		
	}
	
	
	add_action('admin_menu', 'register_plgavp_settings_subpage');

	function register_plgavp_settings_subpage() {
		add_submenu_page( 'plgavp_Antivirus', 'Settings', 'Settings', 'manage_options', 'plgavp_Antivirus_settings_page', 'plgavp_antivirus_settings_page_callback' ); 
	}
	
	
	function plgavp_antivirus_settings_page_callback()
	{
		wp_enqueue_style( 'plgavp_LoadStyle' );

		$img_path = plugins_url('images/', __FILE__);
		
		if (isset($_POST['action']) && $_POST['action'] == 'update' && check_admin_referer( 'name_AFAD78D85E01' ))
		{
			$data = array('access_key' => trim($_POST['access_key']));
			if (trim($_POST['access_key']) != '') 
			{
				$data['registered'] = 1;
				$data['email'] = get_option( 'admin_email' );
			}
			
			$data['show_protectedby'] = intval($_POST['show_protectedby']);
			
			global $avp_license_info;
			if ($avp_license_info['membership'] == 'free') $data['show_protectedby'] = 1;
			
			plgwpavp_SetExtraParams($data);
			
			SGAntiVirus::ShowMessage('Settings saved.');
		}
		
		$params = plgwpavp_GetExtraParams();
		
		?>
		<h2 class="avp_header icon_settings">WP Antivirus Settings</h2>
		
<form method="post" id="plgwpagp_settings_page" action="admin.php?page=plgavp_Antivirus_settings_page" onsubmit="return SG_CheckForm(this);">


			<table id="settings_page">


			<tr class="line_4">
			<th scope="row"><?php _e( 'Access Key', 'plgwpavp' )?></th>
			<td>
	            <input type="text" name="access_key" id="access_key" value="<?php echo $params['access_key']; ?>" class="regular-text">
	            <br />
	            <span class="description">This key is necessary to access to <a target="_blank" href="http://www.siteguarding.com">SiteGuarding API</a> features. Every website has uniq access key. Don't change it fo you don't know what is it.</span>
			</td>
			</tr>
			
			<tr class="line_4">
			<th scope="row"><?php _e( 'Show \'Protected by\'', 'plgwpavp' )?></th>
			<td>
	            <input <?php if ($params['membership'] == 'free') {echo 'disabled';$params['show_protectedby'] = 1;} ?> name="show_protectedby" type="checkbox" id="show_protectedby" value="1" <?php if (intval($params['show_protectedby']) == 1) echo 'checked="checked"'; ?>>
			</td>
			</tr>

			
			</table>

<?php
wp_nonce_field( 'name_AFAD78D85E01' );
?>			
<p class="submit">
  <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
</p>

<input type="hidden" name="page" value="plgavp_Antivirus_settings_page"/>
<input type="hidden" name="action" value="update"/>
</form>


		<h3>Cron Settings</h3>
		
		<p>
		If you want to enable daily scan of your website. Add this line in your hosting panel in cron settings.<br /><br />
		<b>Unix time settings:</b> 0 0 * * *<br />
		<b>Command:</b> wget -O /dev/null "<?php echo get_site_url(); ?>/index.php?task=cron&access_key=<?php echo $params['access_key']; ?>"
		</p>

		<?php
	}
	
	
	add_action('admin_menu', 'register_plgavp_extentions_subpage');

	function register_plgavp_extentions_subpage() {
		add_submenu_page( 'plgavp_Antivirus', 'Extentions', 'Extentions', 'manage_options', 'plgavp_Antivirus_extentions_page', 'plgavp_antivirus_extentions_page_callback' ); 
	}
	
	
	function plgavp_antivirus_extentions_page_callback()
	{
		wp_enqueue_style( 'plgavp_LoadStyle' );
		
		?>
		
		<h2 class="avp_header icon_settings">Security Extentions</h2>
		
		<div class="grid-box width25 grid-h" style="width: 250px;">
		  <div class="module mod-box widget_black_studio_tinymce">
		    <div class="deepest">
		      <h3 class="module-title">WordPress Admin Protection</h3>
		      <div class="textwidget">
		        <table class="table-val" style="height: 180px;">
		          <tbody>
		            <tr>
		              <td class="table-vat">
		                <ul style="list-style-type: circle;">
		                  <li>
		                    Prevents password brute force attack with strong 'secret key'
		                  </li>
		                  <li>
                    		White & Black IP list access
		                  </li>
		                  <li>
		                    Notifications by email about all not authorized actions
		                  </li>
		                  <li>
		                    Protection for login page with captcha code
		                  </li>
		                </ul>
		              </td>
		            </tr>
		            <tr>
		              <td class="table-vab">
		                <a class="button button-primary extbttn" href="https://www.siteguarding.com/en/wordpress-admin-protection">
		                  Learn More
		                </a>
		              </td>
		            </tr>
		          </tbody>
		        </table>
		        <p>
		          <img class="imgpos_ext" alt="WordPress Admin Protection" src="<?php echo plugins_url('images/wpAdminProtection-logo.png', __FILE__); ?>">
		        </p>
		      </div>
		    </div>
		  </div>
		</div>
		
		
		<div class="grid-box width25 grid-h" style="width: 250px;">
		  <div class="module mod-box widget_black_studio_tinymce">
		    <div class="deepest">
		      <h3 class="module-title">Graphic Captcha Protection</h3>
		      <div class="textwidget">
		        <table class="table-val" style="height: 180px;">
		          <tbody>
		            <tr>
		              <td class="table-vat">
		                <ul style="list-style-type: circle;">
		                  <li>
		                    Strong captcha protection
		                  </li>
		                  <li>
                    		Easy for human, complicated for robots
		                  </li>
		                  <li>
		                    Prevents password brute force attack on login page
		                  </li>
		                  <li>
		                    Blocks spam software
		                  </li>
		                  <li>
		                    Different levels of the security
		                  </li>
		                </ul>
		              </td>
		            </tr>
		            <tr>
		              <td class="table-vab">
		                <a class="button button-primary extbttn" href="https://www.siteguarding.com/en/wordpress-graphic-captcha-protection">
		                  Learn More
		                </a>
		              </td>
		            </tr>
		          </tbody>
		        </table>
		        <p>
		          <img class="imgpos_ext" alt="WordPress Graphic Captcha Protection" src="<?php echo plugins_url('images/wpGraphicCaptchaProtection-logo.png', __FILE__); ?>">
		        </p>
		      </div>
		    </div>
		  </div>
		</div>
		
		
		<div class="grid-box width25 grid-h" style="width: 250px;">
		  <div class="module mod-box widget_black_studio_tinymce">
		    <div class="deepest">
		      <h3 class="module-title">Admin Graphic Protection</h3>
		      <div class="textwidget">
		        <table class="table-val" style="height: 180px;">
		          <tbody>
		            <tr>
		              <td class="table-vat">
		                <ul style="list-style-type: circle;">
		                  <li>
		                    Good solution if you access to your website from public places or infected computers
		                  </li>
		                  <li>
		                    Prevent password brute force attack with strong "graphic password"
		                  </li>
		                  <li>
		                    Notifications by email about all not authorized actions
		                  </li>
		                </ul>
		              </td>
		            </tr>
		            <tr>
		              <td class="table-vab">
		                <a class="button button-primary extbttn" href="https://www.siteguarding.com/en/wordpress-admin-graphic-password">
		                  Learn More
		                </a>
		              </td>
		            </tr>
		          </tbody>
		        </table>
		        <p>
		          <img class="imgpos_ext" alt="WordPress Admin Graphic Protection" src="<?php echo plugins_url('images/wpAdminGraphicPassword-logo.png', __FILE__); ?>">
		        </p>
		      </div>
		    </div>
		  </div>
		</div>
		
		
		<div class="grid-box width25 grid-h" style="width: 250px;">
		  <div class="module mod-box widget_black_studio_tinymce">
		    <div class="deepest" >
		      <h3 class="module-title">User Access Notification</h3>
		      <div class="textwidget">
		        <table class="table-val" style="height: 180px;">
		          <tbody>
		            <tr>
		              <td class="table-vat">
		                <ul style="list-style-type: circle;">
		                  <li>
		                    Catchs successful and failed login actions
		                  </li>
		                  <li>
		                    Sends notifications to the user and to the administrator by email
		                  </li>
		                  <li>
		                    Shows Date/Time of access action, Browser, IP address, Location (City, Country)
		                  </li>
		                </ul>
		              </td>
		            </tr>
		            <tr>
		              <td class="table-vab">
		                <a class="button button-primary extbttn" href="https://www.siteguarding.com/en/wordpress-user-access-notification">
		                  Learn More
		                </a>
		              </td>
		            </tr>
		          </tbody>
		        </table>
		        <p>
		          <img class="imgpos_ext" alt="WordPress User Access Notification" src="<?php echo plugins_url('images/wpUserAccessNotification-logo.jpeg', __FILE__); ?>">
		        </p>
		      </div>
		    </div>
		  </div>
		</div>
		
		
		

				
		<?php
	}
	


}






/**
 * Functions
 */



function plgwpavp_GetExtraParams()
{
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'plgwpavp_config';
    
    $ppbv_table = $wpdb->get_results("SHOW TABLES LIKE '".$table_name."'" , ARRAY_N);
    if(!isset($ppbv_table[0])) return false;
    
    $rows = $wpdb->get_results( 
    	"
    	SELECT *
    	FROM ".$table_name."
    	"
    );
    
    $a = array();
    if (count($rows))
    {
        foreach ( $rows as $row ) 
        {
        	$a[trim($row->var_name)] = trim($row->var_value);
        }
    }

    return $a;
}


function plgwpavp_SetExtraParams($data = array())
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'plgwpavp_config';

    if (count($data) == 0) return;   
    
    foreach ($data as $k => $v)
    {
        $tmp = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $table_name . ' WHERE var_name = %s LIMIT 1;', $k ) );
        
        if ($tmp == 0)
        {
            // Insert    
            $wpdb->insert( $table_name, array( 'var_name' => $k, 'var_value' => $v ) ); 
        }
        else {
            // Update
            $data = array('var_value'=>$v);
            $where = array('var_name' => $k);
            $wpdb->update( $table_name, $data, $where );
        }
    } 
}







class SGAntiVirus {
	
	public static function DownloadFromWordpress_Link($link)
	{
		$dst = fopen(dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'update.zip', 'w');
		$ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, $link );
		 curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		 //curl_setopt($ch, CURLOPT_HEADER, true);
		 curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		 curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3600000);
		 curl_setopt($ch, CURLOPT_FILE, $dst);
		 //!dont need curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 sec
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 10000); // 10 sec
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		//*** maybe need */curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		 $a = curl_exec($ch);
		 curl_close($ch);
         if ($a === false) return false;
         else return true;
	}
    
	public static function DownloadFromWordpress($version)
	{
		$dst = fopen(dirname(__FILE__).DIRSEP.'tmp'.DIRSEP.'update.zip', 'w');
		$ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, 'https://downloads.wordpress.org/plugin/wp-antivirus-site-protection.'.$version.'.zip' );
		 curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		 //curl_setopt($ch, CURLOPT_HEADER, true);
		 curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
		 curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3600000);
		 curl_setopt($ch, CURLOPT_FILE, $dst);
		 //!dont need curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 sec
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 10000); // 10 sec
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		//*** maybe need */curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		 $a = curl_exec($ch);
		 curl_close($ch);
         if ($a === false) return false;
         else return true;
	}
	
	public static function ShowFilesForAnalyze($files_array = array())
	{
		$files = array();
		
		if (count($files_array['main']))
		{
			foreach ($files_array['main'] as $k => $filename)
			{
				$files[$filename] = $filename;	
			}
		}
		if (count($files_array['heuristic']))
		{
			foreach ($files_array['heuristic'] as $k => $filename)
			{
				$files[$filename] = $filename;	
			}
		}
		
		sort($files);
		
		echo '<pre>';
		print_r($files);
		echo '</pre>';
		
		echo '<br><br>';
		
		if (count($files))
		{
			foreach ($files as $file)
			{
				$file_full_path = ABSPATH.'/'.$file;
				echo $file_full_path.' Filesize: '.filesize($file_full_path).' bytes<br><br>';
				$handle = fopen($file_full_path, "r");
				$content =  fread($handle, filesize($file_full_path));
				echo 'Content: <pre>'.$content.'</pre>';
				fclose($handle);
				
				echo '<br><br><hr><br><br>';
			}
		}
		
		
	}
	
	public static function SendFilesForAnalyze($files_array = array(), $email_from = 'dontreply@siteguarding.com')
	{
		if (trim($email_from) == '') $email_from = 'dontreply@siteguarding.com';
	
		$result = false;
		$files = array();
		
		if (count($files_array['main']))
		{
			foreach ($files_array['main'] as $k => $filename)
			{
				$files[$filename] = $filename;	
			}
		}
		if (count($files_array['heuristic']))
		{
			foreach ($files_array['heuristic'] as $k => $filename)
			{
				$files[$filename] = $filename;	
			}
		}
		sort($files);
		
		
		if (count($files))
		{
			$md5_list = array();
			
			$attachments = $files;
			$message_files = '';
			foreach ($attachments as $k => $v)
			{
				$attachments[$k] = ABSPATH.DIRSEP.$v;
				$message_files .= $v."<br>";
				$md5_list[] = array(
					'File' => $v,
					'md5' => strtoupper(md5_file(ABSPATH.'/'.$v))
				);
			}

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			
			// Additional headers
			$headers .= "From: ".get_site_url()." <".$email_from.">" . "\r\n";
			
			// Mail it
			$mailto = 'review@siteguarding.com';
			$subject = 'Antivirus Files Review ('.get_site_url().')';
			$body_message = 'Files for review. Domain: '.get_site_url()."<br>";
			$body_message .= 'Platform: '.SGAntiVirus_module::$antivirus_platform."<br>";
			$body_message .= 'Antivirus Version: '.SGAntiVirus_module::$antivirus_version."<br>";
			$body_message .= "<br><br>Files:<br><br>".$message_files;
			$body_message .= "<br><br>MD5:<br><pre>".print_r($md5_list, true)."</pre>";

			if (function_exists('wp_mail') === false) 
			{
				require_once(ABSPATH.'wp-includes'.DIRSEP.'pluggable.php');
			}

			$result = wp_mail($mailto, $subject, $body_message, $headers, $attachments);
			$result = wp_mail($mailto, $subject, $body_message, $headers);
		}
		
		return $result;
	}
	
	
	public static function QuarantineFiles($files = array())
	{
		$fp = fopen(dirname(__FILE__).'/tmp/quarantine.log', 'a');
		
		$result = true;
		
		$quarantine_path = dirname(__FILE__).'/tmp/';
		
		//print_r($files);
		if (count($files))
		{
			foreach ($files as $file)
			{
				if (file_exists(ABSPATH.'/'.$file))
				{
					$f_from = ABSPATH.'/'.$file;
					$f_to = $quarantine_path.md5($file).'.tmp';
					
					//echo ABSPATH.'/'.$file."<br>";
					
					$a = date("Y-m-d H:i:s")." File ".$file."\n";
					fwrite($fp, $a);
					
					// Move to quarantine
					if (copy($f_from, $f_to) === false) 
					{
						$result = false;
						
						$a = date("Y-m-d H:i:s")." File is not moved to quarantine ".$file."\n";
						fwrite($fp, $a);
					}
					else {
						$a = date("Y-m-d H:i:s")." Moved to quarantine as ".$f_to."\n";
						fwrite($fp, $a);
					}
					
					// Delete from the server
					if (unlink($f_from) === false)
					{
						$result = false;
						
						$a = date("Y-m-d H:i:s")." File is not deleted ".$file."\n";
						fwrite($fp, $a);
					}
					else {
						$a = date("Y-m-d H:i:s")." File deleted ".$file."\n";
						fwrite($fp, $a);
					}
				}
			}
		}
		
		fclose($fp);
		
		return $result;
	}



	public static function page_ConfirmRegistration()
	{
		?>
		<script>
		function form_ConfirmRegistration(form)
		{
			if ( jQuery('#registered').is(':checked') ) return true;
			else {
				alert('Confirmation is not checked.');	
				return false;
			}
		}
		</script>
        <div class="registration_box">
		<form method="post" action="admin.php?page=plgavp_Antivirus" onsubmit="return form_ConfirmRegistration(this);">
		
			<h3 class="apv_header">Registration</h3>
			
			<p>Click "Confirm Registration" button to complete registration process. Your website will be automatically registered on <a href="http://www.siteguarding.com">www.SiteGuarding.com</a>.<br></p>
			
			<p>Already registered? Go to <a href="admin.php?page=plgavp_Antivirus_settings_page">Antivirus Settings</a> page and enter your Access Key.</p>
		
			<table id="settings_page">

			<tr class="line_4">
			<th scope="row">Domain</th>
			<td>
	            <input disabled type="text" name="domain" id="domain" value="<?php echo get_site_url(); ?>" class="regular-text reg_input_box">
			</td>
			</tr>
			    
			<tr class="line_4">
			<th scope="row">Email</th>
			<td>
	            <input type="text" name="email" id="email" value="<?php echo get_option( 'admin_email' ); ?>" class="regular-text reg_input_box">
			</td>
			</tr>
            
			<tr class="line_4">
			<th scope="row"></th>
			<td>
	            <span class="msg_alert">Email address must be valid. You will get your registration access key and reports by email.</span>  
			</td>
			</tr>
			
			<tr class="line_4">
			<th scope="row">Confirmation</th>
			<td>
	            <input name="registered" type="checkbox" id="registered" value="1"> I confirm to register my website on <a href="http://www.siteguarding.com">www.SiteGuarding.com</a>
			</td>
			</tr>
			
			</table>
			
		<?php
		wp_nonce_field( 'name_254f4bd3ea8d' );
		?>			
		<p class="submit startscanner">
		  <input type="submit" name="submit" id="submit" class="button button-primary" value="Confirm Registration">
		</p>
		
		<input type="hidden" name="page" value="plgavp_Antivirus"/>
		<input type="hidden" name="action" value="ConfirmRegistration"/>
		</form>
        </div>
		
		<?php self::HelpBlock(); ?>
			
		<?php
	}	
	
	
	public static function page_PreScan($params)
	{
	    // Check for extra security plugins
		if (!defined('ABSPATH') || strlen(ABSPATH) < 8) 
		{
			$scan_path = dirname(__FILE__);
			$scan_path = str_replace(DIRSEP.'wp-content'.DIRSEP.'plugins'.DIRSEP.'wp-antivirus-site-protection', DIRSEP, $scan_path);
    		//echo TEST;
		}
        else $scan_path = ABSPATH;
        
        $tmp_htaccess = $scan_path.DIRSEP.'wp-content'.DIRSEP.'plugins'.DIRSEP.'.htaccess';
        if (file_exists($tmp_htaccess)) unlink($tmp_htaccess);
        
        
		
		if (intval($params['scans']) == 30)
		{
			$txt = 'Congratulation. One more step to protect your website. Click START SCANNER button and get your security report.';
			self::ShowMessage($txt);
		}
	 
		?>
		<form method="post" action="admin.php?page=plgavp_Antivirus">

<?php
if (intval($params['scans']) == 0 || $params['membership'] == 'free') {
?>
	<p class="avp_attention msg_box msg_error msg_icon">Your version of antivirus has limits. Some features are disabled and available in PRO version only. Get PRO version. <a href="https://www.siteguarding.com/en/buy-service/antivirus-site-protection?domain=<?php echo urlencode( get_site_url() ); ?>&email=<?php echo urlencode(get_option( 'admin_email' )); ?>" target="_blank">Upgrade to PRO version</a>.</p>
<?php
}
?>


<div class="divTable">
<div class="divRow">
<div class="divCell">

<p>
You have: <b><?php echo ucwords($params['membership']); ?> version</b> (ver. <?php echo SGAntiVirus_module::$antivirus_version; ?>)<br />

Available Scans: <?php echo $params['scans']; ?><br />
Valid till: <?php echo $params['exp_date']."&nbsp;&nbsp;"; 
if ($params['exp_date'] < date("Y-m-d")) echo '<span class="label_red">Expired</span>';
if ($params['exp_date'] < date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"))) && $params['exp_date'] >= date("Y-m-d") ) echo '<span class="label_red">Will Expired Soon</span>';
?><br />
Google Blacklist Status: <?php if ($params['blacklist']['google'] != 'ok') echo '<span class="label_red">Blacklisted ['.$params['blacklist']['google'].']</span> [<a href="https://www.siteguarding.com/en/services/malware-removal-service" target="_blank">Remove From Blacklist</a>]'; else echo '<span class="label_green">Not blacklisted</span>'; ?><br />
File Change Monitoring: <?php if ($params['filemonitoring']['status'] == 0) echo '<span class="label_red">Disabled</span> [<a href="https://www.siteguarding.com/en/protect-your-website" target="_blank">Subscribe</a>]'; else echo '<span class="label_green">'.$params['filemonitoring']['plan'].'</span> ['.$params['filemonitoring']['exp_date'].']'; ?><br />
Website Firewall: <?php if (!SGAntiVirus_module::CheckFirewall()) echo '<span class="label_red">Not Installed</span> [<a href="https://www.siteguarding.com/en/buy-service/security-package-premium" target="_blank">Subscribe</a>]'; else echo '<span class="label_green">Installed</span>'; ?><br />
<?php
if (count($params['reports']) > 0) 
{
    if ($params['last_scan_files_counters']['main'] == 0 && $params['last_scan_files_counters']['heuristic'] == 0) echo 'Website Status: <span class="label_green">Clean</span>';
    if ($params['last_scan_files_counters']['main'] > 0) echo 'Website Status: <span class="label_red">Infected</span> [<a href="https://www.siteguarding.com/en/services/malware-removal-service" target="_blank">Clean My Website</a>]';
    else if ($params['last_scan_files_counters']['heuristic'] > 0)  echo 'Website Status: <span class="label_red">Review is required</span> [<a href="https://www.siteguarding.com/en/services/malware-removal-service" target="_blank">Review My Website</a>]';
}
else {
    echo 'Website Status: <span class="label_red">Never Analyzed</span>';
}
?>
</p>

<?php
if ($params['membership'] == 'pro') $account_type_txt = 'You have PRO version';
else $account_type_txt = 'Get PRO version of WP Antivirus Site Protection';
?>
<p class="avp_getpro"><a href="https://www.siteguarding.com/en/buy-service/antivirus-site-protection?domain=<?php echo urlencode( get_site_url() ); ?>&email=<?php echo urlencode(get_option( 'admin_email' )); ?>" target="_blank"><?php echo $account_type_txt; ?></a></p>



<div class="mod-box"><div>	
<h3 class="apv_header">Antivirus Scanner</h3>		
<p>To start the scan process click "Start Scanner" button.</p>
<p>Scanner will automatically collect and analyze the files of your website. The scanning process can take up to 10 mins (it depends of speed of your server and amount of the files to analyze). The copy of the report we will send by email for your records.</p>
		
		<?php
		wp_nonce_field( 'name_254f4bd3ea8d' );
		?>			
		<p class="submit startscanner">
		  <input type="submit" name="submit" id="submit" class="button button-primary" value="Start Scanner">
		</p>
		
		<input name="allow_scan" type="hidden" id="allow_scan" value="1">
		<input type="hidden" name="page" value="plgavp_Antivirus"/>
		<input type="hidden" name="action" value="StartScan"/>
		</form>

<p class="msg_alert"><b>Please note:</b> Some other security plugins can block Antivirus scanning process. Disable them or <a href="https://www.siteguarding.com/en/contacts" target="_blank">contact SiteGuarding.com support</a> for more information.</p>
<p><b>Found suspicious file on your website?</b> Analyze it for free with our online tool antivirus. <a target="_blank" href="https://www.siteguarding.com/en/website-antivirus">Click here</a></p>

<h3 class="apv_header">Extra Options</h3>	

	<div class="divTable avpextraoption">
	
	<div class="divRow">
	<div class="divCell avpextraoption_txt">Your website got hacked and blacklisted by Google? This is really bad, you are going to lose your visitors. We will help you to clean your website and remove from all blacklists.</div>
	<div class="divCell">
		<form method="post" action="https://www.siteguarding.com/en/services/malware-removal-service">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Clean My Website">
		</form>
	</div>
	</div>
	
	<div class="divRow"><div class="divCell">&nbsp;</div><div class="divCell"></div><div class="divCell"></div><div class="divCell"></div></div>
	
	<div class="divRow">
	<div class="divCell avpextraoption_txt">Found suspicious files on your website? Send us request for free analyze. Our security experts will review your files and explain what to do.</div>
	<div class="divCell">
		<form method="post" action="admin.php?page=plgavp_Antivirus">
		<?php
		if ($params['membership'] == 'pro') 
		{
			?>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Send Files For Analyze">
			<?php
		} else {
			?>
			<input type="button" class="button button-primary" value="Send Files For Analyze" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
			<?php
		}
		?>	
		
		<?php
		wp_nonce_field( 'name_254f4bd3ea8d' );
		?>
		<input type="hidden" name="page" value="plgavp_Antivirus"/>
		<input type="hidden" name="action" value="SendFilesForAnalyze"/>
		</form>
	</div>
	</div>
	
	<div class="divRow"><div class="divCell">&nbsp;</div><div class="divCell"></div><div class="divCell"></div><div class="divCell"></div></div>
	
	<div class="divRow">
	<div class="divCell avpextraoption_txt">Remove viruses from your website with one click.<br><span class="msg_alert">Please note: Hackers can inject malware codes inside of the normal files. We advice to send request to SiteGuarding.com for file review and analyze.</span></div>
	<div class="divCell">
		<form method="post" action="admin.php?page=plgavp_Antivirus">
		<?php
		if ($params['membership'] == 'pro') 
		{
			?>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Quarantine malware" onclick="return confirm('Before use this feature, please make sure that you have sent the files for analyze and got reply from SiteGuarding.com\nMove files to quarantine?')">
			<?php
		} else {
			?>
			<input type="button" class="button button-primary" value="Quarantine malware" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
			<?php
		}
		?>	
		
		<?php
		wp_nonce_field( 'name_254f4bd3ea8d' );
		?>
		<input type="hidden" name="page" value="plgavp_Antivirus"/>
		<input type="hidden" name="action" value="QuarantineFiles"/>
		</form>
	</div>
	</div>
	
	<div class="divRow"><div class="divCell">&nbsp;</div><div class="divCell"></div><div class="divCell"></div><div class="divCell"></div></div>
	
	<div class="divRow">
	<div class="divCell avpextraoption_txt">Select Security Package for Your Website. Server-side scanning & file change monitoring. Daily analyze of all the changes on your website. Malware removal from already hacked website and much more</div>
	<div class="divCell">
		<form method="post" action="https://www.siteguarding.com/en/protect-your-website">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Select Security Package">
		</form>
	</div>
	</div>
	</div>
    
    <h3 class="howitworks">Do you need clean and protected website? Please learn how it works.</h3>
    <p class="howitworks"><a href="https://www.siteguarding.com/en/protect-your-website" target="_blank">Our security packages</a> cover all your needs. Focus on your business and leave security to us.</p>
    
<p class="center">

<iframe src="https://player.vimeo.com/video/140200465" width="100%" height="430" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>

</p>

	<form class="howitworks" method="post" action="https://www.siteguarding.com/en/protect-your-website">
		<p class="submit startscanner">
		  <input type="submit" name="submit" class="button button-primary greenbg" value="Protect My Website">
		</p>
	</form>
	
	



<?php
if ($params['membership'] != 'pro') 
{
	?>
	<p><span class="msg_box msg_error">Quarantine & Malware Removal feature is disabled. Available in PRO version only. <a href="https://www.siteguarding.com/en/buy-service/antivirus-site-protection?domain=<?php echo urlencode( get_site_url() ); ?>&email=<?php echo urlencode(get_option( 'admin_email' )); ?>" target="_blank">Upgrade to PRO version</a></span></p>
	<?php	
}

if ( ($params['last_scan_files_counters']['main'] > 0 || $params['last_scan_files_counters']['heuristic'] > 0) && $params['membership'] != 'free' )
{
	?>
	<br /><h3>Latest Scan Result</h3>
	<?php
}
			if (count($params['last_scan_files']['main']))
			{
				// Check files
				foreach ($params['last_scan_files']['main'] as $k => $tmp_file)
				{
					if (!file_exists(ABSPATH.'/'.$tmp_file)) unset($params['last_scan_files']['main'][$k]);
				}
				
				if (count($params['last_scan_files']['main']) > 0)
{
					?>
					<div class="avp_latestfiles_block">
					<h4>Action is required</h4>
					
					<?php
					foreach ($params['last_scan_files']['main'] as $tmp_file)
					{
						echo '<p>'.$tmp_file.'</p>';
					}
					?>
	
					<br />
					
					<div class="divTable">
					<div class="divRow">
					<div class="divCell">
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Send Files to SiteGuarding.com">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Send Files to SiteGuarding.com" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					?>
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="SendFilesForAnalyze"/>
					</form>
					</div>
					
					<div class="divCell">&nbsp;</div>

					<div class="divCell">
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Quarantine & Remove malware" onclick="return confirm('Before use this feature, please make sure that you have sent the files for analyze and got reply from SiteGuarding.com\nMove files to quarantine?')">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Quarantine & Remove malware" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					?>
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="QuarantineFiles"/>
					<input type="hidden" name="filelist" value="main"/>
					</form>
					
					</div></div></div>
					* Please note: Hackers can inject malware codes inside of the normal files. If you delete these files, website can stop to work or will be not stable. We advice to send request to SiteGuarding.com for file review and analyze. 
					
					</div>
					<?php
				}

			}
			
			if (count($params['last_scan_files']['heuristic']))
			{
				// Check files
				foreach ($params['last_scan_files']['heuristic'] as $k => $tmp_file)
				{
					if (!file_exists(ABSPATH.'/'.$tmp_file)) unset($params['last_scan_files']['heuristic'][$k]);
				}
				
				if (count($params['last_scan_files']['heuristic']) > 0)
				{
					?>
					<div class="avp_latestfiles_block">
					<h4>Review is required</h4>
					<?php
					foreach ($params['last_scan_files']['heuristic'] as $tmp_file)
					{
						echo '<p>'.$tmp_file.'</p>';
					}
					
					?>
					<br />
					<?php
					
					if ($params['whitelist_filters_enabled'] == 1)
					{
						?>
						<span class="msg_box msg_warning">White List is enabled.</span><br /><br />
						<?php
					}
					?>
					
					
					<div class="divTable">
					<div class="divRow">
					<div class="divCell">
					
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Send Files to SiteGuarding.com">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Send Files to SiteGuarding.com" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="SendFilesForAnalyze"/>
					</form>
					
					</div>
					
					<div class="divCell">&nbsp;</div>

					<div class="divCell">
					<form method="post" action="admin.php?page=plgavp_Antivirus">
					<?php
					if ($params['membership'] == 'pro') 
					{
						?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Quarantine & Remove malware" onclick="return confirm('Before use this feature, please make sure that you have sent the files for analyze and got reply from SiteGuarding.com\nMove files to quarantine?')">
						<?php
					} else {
						?>
						<input type="button" class="button button-primary" value="Quarantine & Remove malware" onclick="javascript:alert('Available in PRO version only. Please Upgrade to PRO version.');">
						<?php
					}
					?>	
					
					<?php
					wp_nonce_field( 'name_254f4bd3ea8d' );
					?>
					<input type="hidden" name="page" value="plgavp_Antivirus"/>
					<input type="hidden" name="action" value="QuarantineFiles"/>
					<input type="hidden" name="filelist" value="heuristic"/>
					</form>
					
					</div></div></div>
					* Please note: Hackers can inject malware codes inside of the normal files. If you delete these files, website can stop to work or will be not stable. We advice to send request to SiteGuarding.com for file review and analyze. 
					
					
					</div>
					<?php
				}
			}

?>


<img class="imgpos" alt="WP Antivirus Site Protection" src="<?php echo plugins_url('images/', __FILE__).'mid_box.png'; ?>" width="110" height="70">
			
</div></div>




<div class="mod-box"><div>
<h3 class="apv_header">Latest Reports</h3>	
<?php
$reports = $params['reports'];
//print_r($params);
if (count($reports)) {
	?>
	<p>
	<?php
		foreach ($reports as $report_info) {
	?>
			<a href="<?php echo $report_info['report_link']; ?>" target="_blank">Click to view report for <?php echo $report_info['domain']; ?>. Date: <?php echo $report_info['date']; ?></a><br />
	<?php
		}
	?>
	</p>
	<?php
} else {
?>
	<p>You don't have any available report yet. Please scan your website.</p>
<?php
}
?>

<img class="imgpos" alt="WP Antivirus Site Protection" src="<?php echo plugins_url('images/', __FILE__).'left_box.png'; ?>" width="110" height="70">
			
</div></div>

		
		<?php self::HelpBlock(); ?>

</div>
<?php
if ($params['membership'] != 'pro') {
?>
<div class="divCell divCellReka">
	<div class="RekaBlock">
		<a href="https://www.siteguarding.com/en/website-extensions">
		<img class="effect7" src="<?php echo plugins_url('images/rek1.png', __FILE__); ?>" />
		</a>
	</div>
	
	<div class="RekaBlock">
		<a href="http://www.safetybis.com/">
		<img class="effect7" src="<?php echo plugins_url('images/rek2.png', __FILE__); ?>" />
		</a>
	</div>
	
	<div class="RekaBlock">
		<a href="https://www.siteguarding.com/en/prices">
		<img class="effect7" src="<?php echo plugins_url('images/rek3.png', __FILE__); ?>" />
		</a>
	</div>
	
	<div class="RekaBlock">
		<a href="https://www.siteguarding.com/en/sitecheck">
		<img class="effect7" src="<?php echo plugins_url('images/rek4.png', __FILE__); ?>" />
		</a>
	</div>
	
	<div class="RekaBlock">
		<a href="https://www.siteguarding.com/en/buy-service/antivirus-site-protection">
		<img class="effect7" src="<?php echo plugins_url('images/rek5.png', __FILE__); ?>" />
		</a>
	</div>
	
	<div class="RekaBlock">
		Remove these ads?<br />
		<a href="https://www.siteguarding.com/en/buy-service/antivirus-site-protection">Upgrade to PRO version</a>
	</div>
	
</div>
<?php
}
?>
</div>
</div>	
		
		<?php
	}
	
	
	
	public static function ScanProgress($session_id = '', $wp_path = '/', $params = array(), $avp_license_info = array())
	{
		$domain = get_site_url();
		$session_report_key = md5($domain.'-'.rand(1,1000).'-'.time());
		?>
		
        <script>
            jQuery(document).ready(function(){
            	
            	var refreshIntervalId;
            	
         		<?php
               	$ajax_url = plugins_url('/ajax.php', __FILE__);
               	?>
               	var link = "<?php echo $ajax_url; ?>";

				jQuery.post(link, {
					    action: "StartScan_AJAX",
					    scan_path: "<?php echo base64_encode($wp_path); ?>",
						session_id: "<?php echo $session_id; ?>",
						access_key: "<?php echo $params['access_key']; ?>",
						session_report_key: "<?php echo $session_report_key; ?>",
						do_evristic: "<?php echo $params['do_evristic']; ?>",
						domain: "<?php echo get_site_url(); ?>",
						email: "<?php echo get_option( 'admin_email' ); ?>",
						membership: "<?php echo $avp_license_info['membership']; ?>"
					},
					function(data){
						
                       if (data!='') ShowReportText(data);
						
						/*if (data=='') 
						{
							alert('Your server lost connection. You will be redirected to SiteGuarding.com to view your report.');
							document.location.href = 'https://www.siteguarding.com/antivirus/viewreport?report_id=<?php echo $session_report_key; ?>';
							return;
						}
						
						ShowReportText(data);*/
					}
				);
				
				
				function GetProgress()
				{
	         		<?php
	               	$ajax_url = plugins_url('/ajax.php', __FILE__);
	               	?>
	               	var link = "<?php echo $ajax_url; ?>";
	
					jQuery.post(link, {
						    action: "GetScanProgress_AJAX",
							session_id: "<?php echo $session_id; ?>"
						},
						function(data){
						    var tmp_data = data.split('|');
						    jQuery("#progress_bar_txt").html(tmp_data[0]+'% - '+tmp_data[1]);
						    jQuery("#progress_bar_process").css('width', parseInt(tmp_data[0])+'%');
						    if (parseInt(tmp_data[2]) == 1)
						    {
						    	// Try to load report directly from SiteGuarding.com
						    	TryToGetReport();
						    }
						}
					);	
				}
				
				
				function TryToGetReport()
				{
	         		<?php
	               	$ajax_url = plugins_url('/ajax.php', __FILE__);
	               	?>
	               	var link = "<?php echo $ajax_url; ?>";
	
					jQuery.post(link, {
						    action: "GetScanReport_AJAX",
							session_report_key: "<?php echo $session_report_key; ?>"
						},
						function(data){
							if (data == '') return;
							ShowReportText(data);
						}
					);
				}
				
				function ShowReportText(data)
				{
					jQuery("#progress_bar_process").css('width', '100%');
					jQuery("#progress_bar").hide();
					
					clearInterval(refreshIntervalId);
					
                    jQuery("#report_area").html(data);
                    jQuery("#back_bttn").show();
                    jQuery("#help_block").show();
                    jQuery("#rek_block").hide();	
                    jQuery(".avp_reviewreport_block").hide();	
				}
				
				refreshIntervalId =  setInterval(GetProgress, 3000);
				
            });
        </script>
        
        <p class="msg_box msg_info avp_reviewreport_block">If the scanning process takes too long. Get the results using the link<br /><a href="https://www.siteguarding.com/antivirus/viewreport?report_id=<?php echo $session_report_key; ?>" target="_blank">https://www.siteguarding.com/antivirus/viewreport?report_id=<?php echo $session_report_key; ?></a></p>
        
        <div id="progress_bar"><div id="progress_bar_process"></div><div id="progress_bar_txt">Scanning process started...</div></div>
        
        <div id="report_area"></div>
        
        <div id="help_block" style="display: none;">
		
		<a href="http://www.siteguarding.com" target="_blank">SiteGuarding.com</a> - Website Security. Professional security services against hacker activity.
		
		</div>
        
        <a id="back_bttn" style="display: none;" class="button button-primary" href="admin.php?page=plgavp_Antivirus">Back</a>
        
        <div id="rek_block">
			<a href="https://www.siteguarding.com" target="_blank">
				<img class="effect7" src="<?php echo plugins_url('images/rek_scan.jpg', __FILE__); ?>">
			</a>
		</div>


		
		<?php
	}
	
	


	public static function GetLicenseInfo($domain, $access_key)
	{
		$link = SITEGUARDING_SERVER.'?action=licenseinfo&type=json&data=';
		
	    $data = array(
			'domain' => $domain,
			'access_key' => $access_key,
            'product_type' => 'wp'
		);
	    $link .= base64_encode(json_encode($data));
	    //$msg = file_get_contents($link);
		include_once(dirname(__FILE__).'/HttpClient.class.php');
		$HTTPClient = new HTTPClient();
		
		$msg = $HTTPClient->get($link);
	    
	    $msg = trim($msg);
	    if ($msg == '') return false;
	    
	    return (array)json_decode($msg, true);
	}
	
	
	public static function GetProgressInfo($domain, $access_key)
	{
		$link = SITEGUARDING_SERVER.'?action=progressinfo&type=json&data=';
		
	    $data = array(
			'domain' => $domain,
			'access_key' => $access_key
		);
	    $link .= base64_encode(json_encode($data));
	    //$msg = file_get_contents($link);
		include_once(dirname(__FILE__).'/HttpClient.class.php');
		$HTTPClient = new HTTPClient();
		
		$msg = $HTTPClient->get($link);
	    
	    $msg = trim($msg);
	    if ($msg == '') return false;
	    
	    return (array)json_decode($msg, true);
	}

	
	public static function sendRegistration($domain, $email, $access_key = '', $errors = '')
	{
		// Send data
	    $link = SITEGUARDING_SERVER.'?action=register&type=json&data=';
	    
	    $data = array(
			'domain' => $domain,
			'email' => $email,
			'access_key' => $access_key,
			'errors' => $errors
		);
	    $link .= base64_encode(json_encode($data));
	    //$msg = trim(file_get_contents($link));
		include_once(dirname(__FILE__).'/HttpClient.class.php');
		$HTTPClient = new HTTPClient();
		
		$msg = $HTTPClient->get($link);
	    
	    if ($msg == '') return true;
	    else return $msg;
	}

	
	public static function checkServerSettings($return_error_names = false)
	{
		$error_name = array();
		$error = 0;
		
		// Check tmp folder is writable
		if (!is_writable(dirname(__FILE__).'/tmp/'))
		{
			chmod ( dirname(__FILE__).'/tmp/' , 0777 ); 
			if (!is_writable(dirname(__FILE__).'/tmp/'))
			{
				$error = 1;
				$error_name[] = 'tmp is not writable';
				self::ShowMessage('Folder '.dirname(__FILE__).'/tmp/'.' is not writable.');
				
				?>
				Please change folder <?php echo dirname(__FILE__).'/tmp/'; ?>permission to 777 to make it writable.
				<?php
			}
		}
		
		
		// Check ssh
		if ( !function_exists('exec') ) 
		{
		    if (!class_exists('ZipArchive'))
		    {
				$error = 1;
				$error_name[] = 'exec & ZipArchive';
				self::ShowMessage('ZipArchive class is not installed on your server.');
				
				?>
				Please ask your hoster support to install or enable PHP ZipArchive class for your server. More information about ZipArchive class please read here <a href="http://bd1.php.net/manual/en/class.ziparchive.php" target="_blank">http://bd1.php.net/manual/en/class.ziparchive.php</a>
				<?php
			}
		}
		
		
		// Check CURL
		if ( !function_exists('curl_init') ) 
		{
			$error = 1;
			$error_name[] = 'CURL';
			self::ShowMessage('CURL is not installed on your server.');
			
			?>
			Please ask your hoster support to install or enable CURL for your server.
			<?php
		}
		
		
		if ($return_error_names) return json_encode($error_name);
		if ($error == 1) return false;
		else return true;
	}
	
	public static function ShowMessage($txt)
	{
		echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>'.$txt.'</strong></p></div>';
	}
	
	
	public static function HelpBlock()
	{
		?>
		<h3 class="avp_header icon_contacts">Support</h3>
		
		<p>
		For malware removal services please <a target="_blank" href="https://www.siteguarding.com/en/services/malware-removal-service">click here</a>.
		</p>
		<p>
		For more information and details about Antivirus Site Protection please <a target="_blank" href="https://www.siteguarding.com/en/antivirus-site-protection">click here</a>.<br /><br />
		<a href="http://www.siteguarding.com/livechat/index.html" target="_blank">
			<img src="<?php echo plugins_url('images/livechat.png', __FILE__); ?>"/>
		</a><br />
		For any questions and support please use LiveChat or this <a href="https://www.siteguarding.com/en/contacts" rel="nofollow" target="_blank" title="SiteGuarding.com - Website Security. Professional security services against hacker activity. Daily website file scanning and file changes monitoring. Malware detecting and removal.">contact form</a>.<br>
		<br>
		<a href="https://www.siteguarding.com/" target="_blank">SiteGuarding.com</a> - Website Security. Professional security services against hacker activity.<br />
		</p>
		<?php
	}
	
}


?>