<?php
/*
Plugin Name: GB-UserList
Plugin URI: http://www.gbourgneuf.com/gbuserlist
Description: Display list of your wordpress users into your sidebar. Display as list or using avatar's user. You could only display recent users or select users randomly. French translation include.
Author: Gaetan Bourgneuf
Version: 0.2
Author URI:  http://www.gbourgneuf.com/gbuserlist
*/

class GB_UserList {
	var $domain = 'gb-userlist';
	var $version = '0.2'; //Changer pour correspondre à la version courante
	var $option_ns = 'gbuserlist';
	var $options = array();

	// Raccourci interne pour ajouter des actions
	function add_action($nom, $num = 0) {
		$hook = $nom;
		$fonction = $nom;
		if(!$num) { $fonction .= $num; }
		add_action($hook, array(&$this, 'action_'.$nom));
	}

	function GB_UserList() {
		// Initialisation des variables
		if ($this->domain == '') $this->domain = get_class($this);
		if ($this->option_ns == '') $this->option_ns = get_class($this);
		// Récupération des options
		$this->options = get_option($this->option_ns);
	
		// Doit-on lancer l'installation ?
		if(!isset($this->options['install']) or ($this->options['install'] != $this->version))
			$this->install();
	
		//Charger les données de localisation
		load_plugin_textdomain($this->domain); 
	
		// gestion automatique des actions
		foreach(get_class_methods(get_class($this)) as $methode) {
			if(substr($methode, 0, 7) == 'action_') {
				$this->add_action(substr($methode, 7));
			}
		}		
	}      

	function action_admin_menu() {
		// Ajout du menu administrateur
		if (function_exists('add_options_page')) {
			add_options_page(__('GB-UserList', $this->domain), __('GB-UserList', $this->domain), 3, basename(__FILE__), array(&$this, 'AdminHelpPage'));
		}
	}

	// Ajout d'une option
	function set($option, $value) {
		$this->options[$option] = $value;
	}

	// Récupération d'une option
	function get($option) {
		if (isset($this->options[$option])) {
			return $this->options[$option];
		} else {
			return false;
		}
	}

	// Mise à jour des options en base de données
	function update_options() {
		return update_option($this->option_ns, $this->options);
	}

//---------------------------------------------
// Editez à partir d'ici
//---------------------------------------------

	function install() {
		// Fonction permettant l'installation de votre plugin (création de tables, de paramètres...)
		$this->set('install', $this->version);
		$this->set('page', 0);
		$this->set('nbdisplay', 8);
		$this->set('displaymode', 0);
		$this->set('displaystyle', 0);
		$this->set('avatarsize', 48);
		$this->set('userdistinction', 0);
		$this->update_options();
	}

	function AdminHelpPage() {
		if ($_POST) {
			$this->set('nbdisplay', stripslashes($_POST['nbdisplay']));
			$this->set('displaymode', stripslashes($_POST['displaymode']));
			$this->set('displaystyle', stripslashes($_POST['displaystyle']));
			$this->set('avatarsize', stripslashes($_POST['avatarsize']));
			if (isset($_POST['userdistinction']))
				$this->set('userdistinction', 1);
			else
				$this->set('userdistinction', 0);
			$this->update_options();
			echo '<div id="message"class="updated fade">';	
			_e('<p>Changes saved</p>',"gb-userlist");			
			echo '</div>';
		}
		$displaymode = $this->get('displaymode');
		$displaystyle = $this->get('displaystyle');
		$nbdisplay = $this->get('nbdisplay');
		$avatarsize = $this->get('avatarsize');
		$userdistinction = $this->get('userdistinction');
		echo '<div class="wrap">';
		echo '<div class="icon32" id="icon-themes"><br/></div>
			<h2>' . __('GB-UserList Options', $this->domain) . '</h2>';
		?>
        <br class="a_break" style="clear: both;"/>
        <form action="options-general.php?page=gb-userlist.php" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Nb. Members to display', $this->domain); ?>: </th>

					<td><label><input type="text" name="nbdisplay" value="<?php echo $nbdisplay; ?>" /></label></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Display Mode', $this->domain); ?>: </th>

					<td><label>
						<select name="displaymode">
							<option value="0" <?php if ($displaymode == 0) echo "selected"; ?>><?php _e('Recent users', $this->domain);?></option>
							<option value="1" <?php if ($displaymode == 1) echo "selected"; ?>><?php _e('Randomly', $this->domain);?></option>
						</select>
						</label></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Display Style', $this->domain); ?>: </th>

					<td><label>
						<select name="displaystyle">
							<option value="0" <?php if ($displaystyle == 0) echo "selected"; ?>><?php _e('Avatar', $this->domain);?></option>
							<option value="1" <?php if ($displaystyle == 1) echo "selected"; ?>><?php _e('List', $this->domain);?></option>
						</select>
						</label></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Avatar Size', $this->domain); ?>: </th>

					<td><label><input type="text" name="avatarsize" value="<?php echo $avatarsize; ?>" /></label></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Distinguish users by role', $this->domain); ?>: </th>

					<td><label><input type="checkbox" name="userdistinction" <?php if ($userdistinction == 1) echo 'checked'; ?> /><br /><i><?php _e('This option will use color code to ditinguish users by role. These colors must be defined into the CSS file gb-userlist.css by settings gbuserlsit-administrator, gbuserlist-author, gbuserlist-editor, gbuserlsit-contributor, gbuserlist-subscriber', $this->domain); ?></i></label></td>
				</tr>
				<tr>
					<th scope="row"></th>

					<td><label><input type="submit" value="<?php _e('Update', $this->domain); ?>" /></label></td>
				</tr>
			</table>			
		</form>
		<br /><center><i>
		<?php
		_e('You can modify template by editing the gb-userlsit.css file into the GB-UserList plugin directory', $this->domain);
		?>
		</i></center>
		<?php
		echo '</div>';
	}
	
	function action_wp_print_styles() {
		$myStyleFile = WP_PLUGIN_URL . '/gb-userlist/gb-userlist.css';
		wp_register_style('wp_gbuserlist_css_styles', $myStyleFile);	
		wp_enqueue_style( 'wp_gbuserlist_css_styles');
	}

	function GB_UserList_Widget($args) {
		global $wpdb;
		$opt = array();
		extract($args);
		$title = 'Membres';
		// Nous créons une requête
		$opt = get_option('gbuserlist');
		$mode = $opt['displaymode'];
		$style = $opt['displaystyle'];
		$nbdisplay = $opt['nbdisplay'];
		$avatarsize = $opt['avatarsize'];
		$userdistinction = $opt['userdistinction'];
		if ($mode == 0) {
			$query = "SELECT * from $wpdb->users WHERE user_status= '0' " .
				"ORDER BY user_registered DESC LIMIT $nbdisplay";
		}
		else {
			$query = "SELECT * from $wpdb->users WHERE user_status= '0' " .
				"ORDER BY RAND() LIMIT $nbdisplay";
		}
		// Et nous stockons le résultat exécuté dans $userlist
		$userlist = $wpdb->get_results($query);
		// si nous avons des résultats
		if ($userlist) {
			// alors on extrait les données
			foreach ($userlist as $user) {
				if ($userdistinction == 1) {
					$user_tmp = new WP_User($user->ID);
					$css_style = '';
					if ($user_tmp->wp_capabilities['administrator'] == 1) {
						$css_style = 'gbuserlist-administrator';
					}
					if ($user_tmp->wp_capabilities['contributor'] == 1) {
						$css_style = 'gbuserlist-contributor';
					}
					if ($user_tmp->wp_capabilities['editor'] == 1) {
						$css_style = 'gbuserlist-editor';
					}
					if ($user_tmp->wp_capabilities['subscriber'] == 1) {
						$css_style = 'gbuserlist-subscriber';
					}
					if ($user_tmp->wp_capabilities['author'] == 1) {
						$css_style = 'gbuserlist-auhtor';
					}
				}
				else {
					if ($style == 0)
						$css_style = 'gbuserlistimg';
					else
						$css_style = 'gbuserlistsimple';
				}
				if ($style == 0)
					$output .= '<li class="' . $css_style . '"><a href="/wp-admin/profile.php?user_id=' . $user->ID . '">' . get_avatar($user->ID, $size = $avatarsize) . '</a></li>';
				else
					$output .= '<li class="' . $css_style . '"><a href="/wp-admin/profile.php?user_id=' . $user->ID . '">' . $user->display_name . '</a></li>';
			}
		// sinon si $userlist ne contient pas d'utilisateur
		} else {
			$output .= "\n";
		}
		// enfin nous affichons notre résultat
		echo $before_widget;
		echo $before_title;
		_e('Members', 'gb-userlist');
		echo $after_title;
		echo '<ul class="gbuserlist">' . $output . '</ul>';
		echo $after_widget;
	}
	
	function action_init() {
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( 'gb-userlist', ABSPATH.'wp-content/plugins/'. $plugin_dir.'/languages/', $plugin_dir.'/languages/' );
		register_sidebar_widget("Members List", array('GB_UserList', 'GB_UserList_Widget'));
	}

//---------------------------------------------
// Fin de la partie d'édition
//---------------------------------------------

}

$inst_GB_UserList = new GB_UserList();