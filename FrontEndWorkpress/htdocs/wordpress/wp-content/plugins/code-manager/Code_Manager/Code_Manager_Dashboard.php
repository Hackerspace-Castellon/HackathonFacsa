<?php

namespace Code_Manager {

	class Code_Manager_Dashboard {

		public static function add_dashboard() {
			$dashboard = new Code_Manager_Dashboard();
			$dashboard->dashboard();
		}

		protected static function navigation_enabled( $navigation_type ) {
			$code_manager_plugin_navigation = get_option( 'code_manager_plugin_navigation' );
			if ( false === $code_manager_plugin_navigation ) {
				$code_manager_plugin_navigation = 'dashboard';
			}
			return in_array( $code_manager_plugin_navigation, [ 'both', $navigation_type ] );
		}

		public static function dashboard_enabled() {
			return self::navigation_enabled( 'dashboard' );
		}

		public static function menu_enabled() {
			return self::navigation_enabled( 'menu' );
		}

		public function dashboard() {
			if ( self::dashboard_enabled() ) {
				$this->dashboard_default();
				$this->dashboard_mobile();

				if ( isset( $_REQUEST['tabmode'] ) && 'on' === $_REQUEST['tabmode'] ) {
					$this->toolbar_tabmode();
				} elseif (
					! isset( $_REQUEST['action'] ) &&
					isset( $_REQUEST['page'] ) && 'code_manager' === $_REQUEST['page']
				) {
					$this->toolbar_listmode();
				}
			}
		}

		protected function dashboard_default() {
			?>
			<div id="cm-dashboard" style="display:none">
				<div class="cm-dashboard">
					<div class="cm-dashboard-group cm-dashboard-group-code">
						<a class="cm-dashboard-item cm_tooltip_icons" href="<?php echo admin_url('admin.php'); ?>?page=code_manager" title="Standard WordPress list view">
							<span class="material-icons">format_list_bulleted</span>
							<div class="label">List mode</div>
						</a>
						<a class="cm-dashboard-item cm_tooltip_icons" href="<?php echo admin_url('admin.php'); ?>?page=code_manager&tabmode=on" title="Open multiple code editors simultaneously">
							<span class="material-icons">tab</span>
							<div class="label">Tab mode</div>
						</a>
						<a class="cm-dashboard-item cm_tooltip_icons" href="https://code-manager.com/code/" title="Download reusable code from plugin website" target="_blank">
							<span class="material-icons">cloud_download</span>
							<div class="label">Download</div>
						</a>
						<div class="subject">Code</div>
					</div>
					<div class="cm-dashboard-group cm-dashboard-group-settings">
						<a class="cm-dashboard-item cm_tooltip_icons" href="<?php echo admin_url('options-general.php?page=code_manager_settings'); ?>" title="Plugin Settings">
							<span class="material-icons">settings</span>
							<div class="label">Settings</div>
						</a>
						<?php
						if ( code_manager_fs()->is_registered() ) {
							?>
							<a class="cm-dashboard-item cm_tooltip_icons" href="<?php echo admin_url('admin.php'); ?>?page=code_manager-account" title="Manage Account">
								<span class="material-icons">person</span>
								<div class="label">Account</div>
							</a>
							<?php
						}
						?>
						<a class="cm-dashboard-item cm_tooltip_icons" target="_blank" href="https://code-manager.com/pricing/" title="Online Pricing, Licensing and Ordering">
							<span class="material-icons">attach_money</span>
							<div class="label">Pricing</div>
						</a>
						<?php
						$menufound = false;
						if ( self::menu_enabled() ) {
							global $submenu;
							$plugin_navigation_default_page = get_option('plugin_navigation_default_page');
							if ( isset( $submenu[ $plugin_navigation_default_page ] ) ) {
								foreach ( $submenu[ $plugin_navigation_default_page ] as $pluginmenu ) {
									if ( 'code_manager-pricing' === $pluginmenu[2] ) {
										$menufound = true;
										break;
									}
								}
							}
						} else {
							$menufound = true;
						}
						if ( $menufound ) {
							?>
							<a class="cm-dashboard-item cm_tooltip_icons" href="<?php echo admin_url('admin.php'); ?>?page=code_manager-pricing" title="Upgrade plugin from dashboard">
								<span class="material-icons">new_releases</span>
								<div class="label">Upgrade</div>
							</a>
							<?php
						}
						?>
						<div class="subject">Manage</div>
					</div>
					<div class="cm-dashboard-group cm-dashboard-group-support">
						<a class="cm-dashboard-item cm_tooltip_icons" target="_blank" href="https://code-manager.com/blog/docs/index/getting-started/read-this-first/" title="Online Help and Documentation">
							<span class="material-icons">help</span>
							<div class="label">Docs</div>
						</a>
						<a class="cm-dashboard-item cm_tooltip_icons" target="_blank" href="https://wordpress.org/support/plugin/code-manager/" title="Public Support Forum">
							<span class="material-icons">forum</span>
							<div class="label">Forum</div>
						</a>
						<?php
						if ( code_manager_fs()->is_premium() ) {
							?>
							<a class="cm-dashboard-item cm_tooltip_icons" target="_blank" href="https://users.freemius.com/store/2612" title="Premium Support">
								<span class="material-icons">stars</span>
								<div class="label">Premium</div>
							</a>
							<?php
						}
						?>
						<div class="subject">Support</div>
					</div>
				</div>
			</div>
			<?php
		}

		protected function dashboard_mobile() {
			?>
			<div id="cm-dashboard-mobile" style="display:none">
				<div id="cm-dashboard-drop-down">
					<div class="cm_nav_toggle" onclick="toggleMenu()"><i class="fas fa-bars"></i></div>
					<div class="cm_nav_title">Code Manager</div>
				</div>
				<ul>
					<li class="menu-item"><a href="<?php echo admin_url('admin.php'); ?>?page=code_manager"><span class="material-icons">format_list_bulleted</span> List mode</a></li>
					<li class="menu-item"><a href="<?php echo admin_url('admin.php'); ?>?page=code_manager&tabmode=on"><span class="material-icons">tab</span> Tab mode</a></li>
					<li class="menu-item cm-separator"><a href="https://code-manager.com/code/" target="_blank"><span class="material-icons">cloud_download</span> Download reusable code</a></li>
					<li class="menu-item"><a href="<?php echo admin_url('options-general.php?page=code_manager_settings'); ?>"><span class="material-icons">settings</span> Settings</a></li>
					<li class="menu-item"><a href="<?php echo admin_url('admin.php'); ?>?page=code_manager-account"><span class="material-icons">person</span> Account</a></li>
					<?php
					$menufound = false;
					if ( self::menu_enabled() ) {
						global $submenu;
						$plugin_navigation_default_page = get_option('plugin_navigation_default_page');
						if ( isset( $submenu[ $plugin_navigation_default_page ] ) ) {
							foreach ( $submenu[ $plugin_navigation_default_page ] as $pluginmenu ) {
								if ( 'code_manager-pricing' === $pluginmenu[2] ) {
									$menufound = true;
									break;
								}
							}
						}
					} else {
						$menufound = true;
					}
					?>
					<li class="menu-item <?php echo $menufound ? '' : 'cm-separator'; ?>"><a href="https://code-manager.com/pricing/" target="_blank"><span class="material-icons">attach_money</span> Pricing</a></li>
					<?php
					if ( $menufound ) {
						?>
						<li class="menu-item cm-separator"><a href="<?php echo admin_url('admin.php'); ?>?page=code_manager-pricing"><span class="material-icons">new_releases</span> Upgrade</a></li>
						<?php
					}
					?>
					<li class="menu-item"><a target="_blank" href="https://code-manager.com/blog/docs/index/"><span class="material-icons">help</span> Online Documentation</a></li>
					<li class="menu-item"><a target="_blank" href="https://wordpress.org/plugins/code-manager/"><span class="material-icons">forum</span> Support Forum</a></li>
					<?php
					if ( code_manager_fs()->is_premium() ) {
						?>
						<li class="menu-item"><a target="_blank" href="<?php echo admin_url('admin.php'); ?>?page=code_manager-wp-support-forum"><span class="material-icons">stars</span> Premium Support</a></li>
						<?php
					}
					?>
				</ul>
			</div>
			<?php
		}

		protected function toolbar_tabmode() {
			?>
			<div class="cm-dashboard-toolbar">
				<i id="code_manager_new" class="fas fa-plus-circle cm_tooltip" title="Add new code"></i>
				<i id="code_manager_open" class="fas fa-folder-open cm_tooltip" title="Open existing code"></i>
			</div>
			<?php
		}

		protected function toolbar_listmode() {
			?>
			<div class="cm-dashboard-toolbar">
				<i id="code_manager_new" class="fas fa-plus-circle cm_tooltip" title="Add new code" onclick="window.location.href='?page=<?php echo CODE_MANAGER_MENU_SLUG; ?>&action=new'"></i>
				<i id="code_manager_import" class="fas fa-upload cm_tooltip" title="Import code" onclick="jQuery('#upload_file_container').toggle()"></i>
			</div>
			<?php
		}

	}

}