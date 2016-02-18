<?php
/*
Plugin Name: Theme Authenticity Checker (TAC)
Plugin URI: http://builtbackwards.com/projects/tac/
Description: Theme Authenticity Checker scans all of your theme files for potentially malicious or unwanted code.
Author: builtBackwards
Version: 1.5.2
Author URI: http://builtbackwards.com/
*/

/*  Copyright 2009 builtBackwards (William Langford and Sam Leavens) - (email : contact@builtbackwards.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function tac_check_theme($template_files, $theme_title) {
	$static_count = 0;
        $bad_lines = null;
        $static_urls = null;
        $static_count = 0;
        
	foreach ($template_files as $tfile)
	{	
		/*
		 * Check for base64 Encoding
		 * Here we check every line of the file for base64 functions.
		 * 
		 */
			
		$lines = file($tfile, FILE_IGNORE_NEW_LINES); // Read the theme file into an array

		$line_index = 0;
		$is_first = true;
		foreach($lines as $this_line)
		{
			if (stristr ($this_line, "base64")) // Check for any base64 functions
			{
				if ($is_first) {
						$bad_lines .= tac_make_edit_link($tfile, $theme_title); 
						$is_first = false;
					}
				$bad_lines .= "<div class=\"tac-bad\"><strong>Line " . ($line_index+1) . ":</strong> \"" . trim(htmlspecialchars(substr(stristr($this_line, "base64"), 0, 45))) . "...\"</div>";
			}
			$line_index++;
		}
		
		/*
		 * Check for Static Links
		 * Here we utilize a regex to find HTML static links in the file.
		 * 
		 */

		$file_string = file_get_contents($tfile);

		$url_re='([[:alnum:]\-\.])+(\\.)([[:alnum:]]){2,4}([[:blank:][:alnum:]\/\+\=\%\&\_\\\.\~\?\-]*)';
		$title_re='[[:blank:][:alnum:][:punct:]]*';	// 0 or more: any num, letter(upper/lower) or any punc symbol
		$space_re='(\\s*)'; 
				
		if (preg_match_all ("/(<a)(\\s+)(href".$space_re."=".$space_re."\"".$space_re."((http|https|ftp):\\/\\/)?)".$url_re."(\"".$space_re.$title_re.$space_re.">)".$title_re."(<\\/a>)/is", $file_string, $out, PREG_SET_ORDER))
		{
			$static_urls .= tac_make_edit_link($tfile, $theme_title); 
									  
			foreach( $out as $key ) {
				$static_urls .= "<div class=\"tac-ehh\">";
				$static_urls .= htmlspecialchars($key[0]);
				$static_urls .= "</div>";
				$static_count++;
			}			  
		}  
	} // End for each file in template loop
	
	// Assemble the HTML results for the completed scan of the current theme
	if (!isset($bad_lines)) {
		$summary = '<span class="tac-good-notice">Theme Ok!</span>';
	} else {
		$summary = '<span class="tac-bad-notice">Encrypted Code Found!</span>';
	}
	if(isset($static_urls)) {
		$summary .= '<span class="tac-ehh-notice"><strong>'.$static_count.'</strong> Static Link(s) Found...</span>';
	}
	
	return array('summary' => $summary, 'bad_lines' => $bad_lines, 'static_urls' => $static_urls, 'static_count' => $static_count);

}


function tac_make_edit_link($tfile, $theme_title) {
	// Assemble the HTML links for editing files with the built-in WP theme editor
	
	if ($GLOBALS['wp_version'] >= "2.9") {
		return "<div class=\"file-path\"><a href=\"theme-editor.php?file=/" . substr(stristr($tfile, "themes"), 0) . "&amp;theme=" . urlencode($theme_title) ."&amp;dir=theme\">" . substr(stristr($tfile, "wp-content"), 0) . " [Edit]</a></div>";	
	} elseif ($GLOBALS['wp_version'] >= "2.6") {
		return "<div class=\"file-path\"><a href=\"theme-editor.php?file=/" . substr(stristr($tfile, "themes"), 0) . "&amp;theme=" . urlencode($theme_title) ."\">" . substr(stristr($tfile, "wp-content"), 0) . " [Edit]</a></div>";
	} else {
		return "<div class=\"file-path\"><a href=\"theme-editor.php?file=" . substr(stristr($tfile, "wp-content"), 0) . "&amp;theme=" . urlencode($theme_title) ."\">" . substr(stristr($tfile, "wp-content"), 0) ." [Edit]</a></div>";
	}
	
}

function tac_get_template_files($template) {
	// Scan through the template directory and add all php files to an array
	
	$theme_root = get_theme_root();
	
	$template_files = array();
	$template_dir = @ dir("$theme_root/$template");
	if ( $template_dir ) {
		while(($file = $template_dir->read()) !== false) {
			if ( !preg_match('|^\.+$|', $file) && preg_match('|\.php$|', $file) )
				$template_files[] = "$theme_root/$template/$file";
		}
	}

	return $template_files;
}

function tac_init() {
	if ( function_exists('add_submenu_page') )
		$page = add_submenu_page('themes.php',__('Theme Authenticity Checker (TAC)'), __('TAC'), 'update_plugins', 'tac.php', 'tac');
}

add_action('admin_menu', 'tac_init');

function tac() {

	?>
<script type="text/javascript">
	function toggleDiv(divid){
	  if(document.getElementById(divid).style.display == 'none'){
		document.getElementById(divid).style.display = 'block';
	  }else{
		document.getElementById(divid).style.display = 'none';
	  }
	}
</script>	
<h2>
    <?php _e('Theme Authenticity Checker (TAC)'); ?>
</h2>
<div class="pinfo">
    Theme Authenticity Checker checks themes for malicious or potentially unwanted code.<br/>
    For more info please go to the plugin page: <a href="http://builtbackwards.com/projects/tac/">http://builtbackwards.com/projects/tac/</a><br/><br/>
	To submit bugs, suggestions, or comments please post in the <a href="http://wordpress.org/tags/tac">WordPress.org Forum</a>.
</div>
<div id="wrap">
    <?php
	$themes = get_themes();
	$theme_names = array_keys($themes);
	natcasesort($theme_names);
	foreach ($theme_names as $theme_name) {
		$template_files = tac_get_template_files($themes[$theme_name]['Template']);
		$title = $themes[$theme_name]['Title'];
		$version = $themes[$theme_name]['Version'];
		$author = $themes[$theme_name]['Author'];
		$screenshot = $themes[$theme_name]['Screenshot'];
		$stylesheet_dir = $themes[$theme_name]['Stylesheet Dir'];
		
		if ($GLOBALS['wp_version'] >= "2.9") {
			$theme_root_uri = $themes[$theme_name]['Theme Root URI'];
			$template = $themes[$theme_name]['Template'];
		}

		$results = tac_check_theme($template_files, $title);
	?>
    <div id="tacthemes">
        <?php if ( $screenshot ) : 
			if ($GLOBALS['wp_version'] >= "2.9") : ?>
				<img src="<?php echo $theme_root_uri.'/'.$template.'/'.$screenshot.'"'."alt=\"$title Screenshot\""; ?> />
			<?php else : ?>
				<img src="<?php echo get_option('siteurl') . '/wp-content' . str_replace('wp-content', '', $stylesheet_dir) . '/' . $screenshot.'"'."alt=\"$title Screenshot\""; ?> />			
			<?php endif; ?>
        <?php else : ?>
        	<div class="tacnoimg">No Screenshot Found</div>
        <?php endif; ?>

		<?php echo '<div class="t-info">'."<strong>$title</strong> $version by $author"; ?>
		
		<?php if ($results['bad_lines'] != '' || $results['static_urls'] != '') : ?>
			<input type="button" value="Details" class="button-primary" id="details" name="details" onmousedown="toggleDiv('<?php echo $title; ?>');" href="javascript:;"/>
		<?php endif; ?>
			</div>
			
		<?php echo $results['summary']; ?>	
			
        <div class="tacresults" id="<?php echo $title; ?>" style="display:none;">
            <?php echo $results['bad_lines'].$results['static_urls']; ?>
        </div>
		
    </div>
		
    <?php
	}
	echo '</div>';
}

// CSS to format results of themes check
function tac_css() {
echo '
<style type="text/css">
<!--

#wrap {
	background-color:#FFF;
	margin-right:5px;
}

.tac-bad,.tac-ehh {
	border:1px inset #000;
	font-family:"Courier New", Courier, monospace;
	margin-bottom:10px;
	margin-left:10px;
	padding:5px;
	width:90%;
}

.tac-bad {
	background:#FFC0CB;
}

.tac-ehh {
	background:#FFFEEB;
}

span.tac-good-notice, span.tac-bad-notice, span.tac-ehh-notice {
	float:left;
	font-size:120%;
	margin: 25px 10px 0 0;
	padding:10px;
}

span.tac-good-notice {
	background:#3fc33f;
	border:1px solid #000;
	width:90px;
	vertical-align: middle;
}

span.tac-bad-notice {
	background:#FFC0CB;
	border:1px solid #000;
	width:195px;
}

span.tac-ehh-notice {
	background:#FFFEEB;
	border:1px solid #ccc;
	width:210px;
}

.file-path {
	color:#666;
	font-size:12px;
	padding-bottom:1px;
	padding-top:5px;
	text-align:right;
	width:92%;
}

.file-path a {
	text-decoration:none;
}

.pinfo {
	background:#DCDCDC;
	margin:5px 5px 40px;
	padding:5px;
}

#tacthemes {
	border-top:1px solid #ccc;
	margin:10px;
	min-height:100px;
	padding-bottom:20px;
	padding-top:20px;
}

#tacthemes img,.tacnoimg {
	border:1px solid #000;
	color:#DCDCDC;
	float:left;
	font-size:16px;
	height:75px;
	margin:10px;
	text-align:center;
	width:100px;
}

.tacresults {
	clear:left;
	margin-left:130px;
	
}
-->
</style>
';
	}

add_action('admin_head', 'tac_css');
    ?>
