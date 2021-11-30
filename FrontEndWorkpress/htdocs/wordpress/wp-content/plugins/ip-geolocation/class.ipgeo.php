<?php
if(!class_exists('IP_Geo_Location'))
{
	class IP_Geo_Location
	{
		/**
		 * Construct the plugin object
		 */
    	public function __construct()
    	{
			// Initialize Settings
			add_action('wp_enqueue_scripts', array(&$this, 'ipgeo_enqueue_scripts'));
			
			// ipgeo shortcode
			add_shortcode('ipgeo',array(&$this, 'ipgeo_shortcode'));
			
		} // END public function __construct()

		public function ipgeo_enqueue_scripts()
		{
			wp_enqueue_style('ipgeo', plugins_url( '/assets/css/ipgeo.css', __FILE__ ) );
		}
        
        public function ipgeo_shortcode()
        {
			ob_start();
			$ipgeo_input_class = get_option('ipgeo_input_class');
			$ipgeo_button_class = get_option('ipgeo_button_class');
            ?>
            <form method="post" action="">
				<?php wp_nonce_field('ipgeo_location_nonce_action', 'ipgeo_location_nonce'); ?>
                <input type="text" <?php if(!empty($ipgeo_input_class)) echo 'class="'.esc_attr( $ipgeo_input_class ).'"'; ?> name="ip" value="<?php if(isset($_POST['ip'])) echo esc_attr( $_POST['ip'] ); ?>" placeholder="1.1.1.1" />
                <input type="submit" <?php if(!empty($ipgeo_button_class)) echo 'class="'.esc_attr( $ipgeo_button_class ).'"'; ?> name="check" value="search" />
            </form>
			<?php
			if(isset($_POST['check']) && wp_verify_nonce($_REQUEST['ipgeo_location_nonce'], 'ipgeo_location_nonce_action'))
            {
                $ip = sanitize_text_field( $_POST['ip'] );
				$this->get_ip_info($ip);
			}
			else
			{
                $this->get_ip_info();
			}

            $output = ob_get_contents();
            ob_end_clean();
            return $output;
		}
		
		function get_ip_info( $inp_ip = null )
		{
			$ip = sanitize_text_field( $inp_ip );
			$error = "";
			
			// get api service
			$api_service = get_option('ipgeo_api_service');
			if(!empty($api_service))
			{
				if($api_service=="ip-api")
				{
					$result = $this->get_result_data_api( $ip, 'ip-api' );	
				}
				else
				{
					$api_token = get_option('ipgeo_api_token');
								
					// check api token is empty or no
					if(!empty($api_token))
					{
						$result = $this->get_result_data_api( $ip, $api_service, $api_token );
					}
				}

				// return error
				if(isset($result['error']))
				{
					if(is_array($result['error']))
						$error = $result['error']['title'];
					else
						$error = $result['error'];
				}

				// show data
				if(empty($error))
				{
					echo '<div id="ipw_main_area" class="home-ip-details">';
					foreach($result as $index => $res_value)
					{
						if($index=="status") continue; // skip 'status' item

						if(!empty($res_value) && !is_array($res_value))
						{
							echo '<div class="json-widget-entry">';
								echo '<div class="indent-0 String">';
									echo '<i></i> ';
									echo '<span class="key">'.esc_attr( $index ).':</span> ';
									echo '<span class="value">"'.esc_attr( $res_value ).'"</span>';
								echo '</div>';
							echo '</div>';
						}
					}
					echo '</div>';
					// embed maps
					$location = $this->get_api_location($api_service, $result);
					if(!empty($location))
						$this->load_maps($location[0], $location[1]);
				}
				else
				{
					echo '<p class="alert alert-danger">'.esc_attr( $error ).'</p>';
				}
			}
		}

		protected function get_result_data_api( $ip, $api_service, $api_key = '' )
		{
			$result = [];
			$error  = '';
			$api_key = esc_attr( $api_key );
			// check ans sanitize ip address
			if(!empty($ip) && !WP_Http::is_ip_address($ip))
				$result['error'] = __('IP Address is invalid.', 'ipgeo');
			
			// api service condition
			if(empty($error))
			{
				switch($api_service)
				{
					case "ip-api":
						$response = wp_remote_get('http://ip-api.com/json/'.$ip );
						break;
					case "abstractapi":
						if(!empty($ip))
							$response = wp_remote_get('https://ipgeolocation.abstractapi.com/v1/?api_key='.esc_attr($api_key).'&ip_address='.$ip );
						else
							$response = wp_remote_get('https://ipgeolocation.abstractapi.com/v1/?api_key='.esc_attr($api_key) );
						break;
					case "ipinfo":
						if(!empty($ip))
							$response = wp_remote_get('https://ipinfo.io/'.$ip.'?token='.esc_attr($api_key) );
						else
							$response = wp_remote_get('https://ipinfo.io/?token='.esc_attr($api_key) );
						break;
				}
				$result = json_decode($response['body'], true);
			}
			
			if(isset($result['status']) && $result['status']=="fail")
				$result['error'] = __('API doesn\'t get a valid data.', 'ipgeo');

			return $result;
		}
		
		protected function get_api_location( $api_service, $result )
		{
			$loc = [];
			if(!empty($result) && is_array($result))
			{
				switch($api_service)
				{
					case "ip-api":
						$loc = [$result['lat'], $result['lon']];
						break;
					case "abstractapi":
						$loc = [$result['latitude'], $result['longitude']];
						break;
					case "ipinfo":
						$loc = explode(",", $result['loc']);
						break;
				}
			}

			return $loc;
		}

		protected function load_maps($lat, $lng)
		{
			$latitude = esc_attr( $lat );
			$longitude = esc_attr( $lng );
			$enable_map_token  = get_option('ipgeo_enable_map');
			if($enable_map_token) :
				$map_api_token = get_option('ipgeo_map_api_token');
				if($map_api_token) : 
				?>

				<div id="ipgeo_map" style="width:100%;height:400px;"></div>
				<script>
				// Initialize and add the map
				function initMap() {
					// The location of Uluru
					var uluru = {lat: <?php echo $latitude; ?>, lng: <?php echo $longitude; ?>};
					// The map, centered at Uluru
					var map = new google.maps.Map(
						document.getElementById('ipgeo_map'), {zoom: 18, center: uluru}
					);
					// The marker, positioned at Uluru
					var marker = new google.maps.Marker({position: uluru, map: map});
				}
				</script>
				<script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $map_api_token; ?>&callback=initMap"></script>

				<?php
				endif;
			endif;
		}
		
		/**
		* Activate the plugin
		*/
		public static function ipgeo_activate()
		{
			// Do nothing
		} // END public static function activate

		/**
		* Deactivate the plugin
		*/
		public static function ipgeo_deactivate()
		{
			// Do nothing
		} // END public static function deactivate	
	
	} // END class IPInfo
} // END if(!class_exists('IPInfo'))

if(class_exists('IP_Geo_Location'))
{
	// instantiate the plugin class
	$ipGeoObj = new IP_Geo_Location();
}
?>