<?php
/**
 * Plugin Name: Ticket Tailor Event List
 * Plugin URI: 
 * Description: Display content using a shortcode to insert in a page or post
 * Version: 0.1
 * Text Domain: ticket-tailor-event-list
 * Author: Joshua Sampson
 * Author URI: 
 */
class TicketTailorEventsList {
	private $ticket_tailor_events_list_options;

	public function __construct() {
        add_action( 'init', array( $this, 'ticket_tailor_event_list_init') );

		add_action( 'admin_menu', array( $this, 'ticket_tailor_events_list_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'ticket_tailor_events_list_page_init' ) );

    }

    public function ticket_tailor_event_list_init() {
        add_shortcode('tt_event_list', array( $this, 'ticket_tailor_event_list_shortcode') );

        wp_register_style( 'ticket_tailor_events_list_style', plugins_url('style.css',__FILE__ ) );
        wp_enqueue_style( 'ticket_tailor_events_list_style' );
    }

	public function ticket_tailor_events_list_add_plugin_page() {
		add_options_page(
			'Ticket Tailor Events List', // page_title
			'Ticket Tailor Events List', // menu_title
			'manage_options', // capability
			'ticket-tailor-events-list', // menu_slug
			array( $this, 'ticket_tailor_events_list_create_admin_page' ) // function
		);
	}

	public function ticket_tailor_events_list_create_admin_page() {
		$this->ticket_tailor_events_list_options = get_option( 'ticket_tailor_events_list_option_name' ); ?>

		<div class="wrap">
			<h2>Ticket Tailor Events List</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'ticket_tailor_events_list_option_group' );
					do_settings_sections( 'ticket-tailor-events-list-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function ticket_tailor_events_list_page_init() {
		register_setting(
			'ticket_tailor_events_list_option_group', // option_group
			'ticket_tailor_events_list_option_name', // option_name
			array( $this, 'ticket_tailor_events_list_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'ticket_tailor_events_list_setting_section', // id
			'Settings', // title
			array( $this, 'ticket_tailor_events_list_section_info' ), // callback
			'ticket-tailor-events-list-admin' // page
		);

		add_settings_field(
			'api_key_0', // id
			'API key', // title
			array( $this, 'api_key_0_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_setting_section' // section
		);
	}

	public function ticket_tailor_events_list_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['api_key_0'] ) ) {
			$sanitary_values['api_key_0'] = sanitize_text_field( $input['api_key_0'] );
		}

		return $sanitary_values;
	}

	public function ticket_tailor_events_list_section_info() {
		
	}

	public function api_key_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="ticket_tailor_events_list_option_name[api_key_0]" id="api_key_0" value="%s">',
			isset( $this->ticket_tailor_events_list_options['api_key_0'] ) ? esc_attr( $this->ticket_tailor_events_list_options['api_key_0']) : ''
		);
	}
	

    public function ticket_tailor_event_list_shortcode($args) {
        $ticket_tailor_events_list_options = get_option( 'ticket_tailor_events_list_option_name' );
        $tt_url = 'https://api.tickettailor.com/v1/event_series';
        $response = wp_remote_get($tt_url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($ticket_tailor_events_list_options['api_key_0'] . ':')
            )
        ));
    
        if ( is_wp_error( $response ) ) {
            return "ERROR";
        }

        $events = json_decode(wp_remote_retrieve_body($response),true);

        $content = "<div class='ticket_tailor_event_list' >";

		usort($events['data'], fn($a, $b) => $a['next_occurrence_date']['unix'] <=> $b['next_occurrence_date']['unix']);
		
        foreach($events['data'] as $event){
			if ($event['status'] === 'published') {
				$content .= "<hr data-ticket-tailor-date='" . date_format(date_create($event['next_occurrence_date']['date']),'D d M') . "' data-ticket-date-group-" . date_format(date_create($event['next_occurrence_date']['date']),'dno') . " />";
				$content .= "<div class='ticket_tailor_event'>";
				$content .= "<div class='ticket_tailor_event_title'>" . $event['name'] . "</div>";
				$content .= "<div class='ticket_tailor_event_venue'>" . $event['venue']['name'];
				if (!empty($event['venue']['postal_code'] )) {
					$content .= ", <span class='postcode'>" . $event['venue']['postal_code'] . "</span>";
				}
				$content .= "</div>";
				$content .= "<div class='ticket_tailor_event_body'>";
				$content .= "<div class='ticket_tailor_event_details'>";
				$content .= "<div class='ticket_tailor_event_description'>";
				// if (strlen($event['description']) > 500) {
				//	 $content .= substr($event['description'], 0, 497) . '...';
				// } else  {
					$content .= $event['description'];
				// }
				$content .= "</div>";
				$content .= "</div>";
				$content .= "<div class='ticket_tailor_event_image'><img src='" . str_replace('h_108,q_85,w_108','h_300,q_85,w_300',$event['images']['thumbnail']) . "' /></div>";
				$content .= "</div>";
				$content .= "<div class='ticket_tailor_event_actions'>";
				$content .= "<a class='ticket_tailor_event_link' target='_blank' href='" . $event['url'] . "'>" . $event['call_to_action'] . "</a>";
				$content .= "</div>";
				$content .= "</div>";
			}
        }

		$content .= "</div>";
        return $content;
    }



}

$ticket_tailor_events_list = new TicketTailorEventsList();

/* 
 * Retrieve this value with:
 * $ticket_tailor_events_list_options = get_option( 'ticket_tailor_events_list_option_name' ); // Array of All Options
 * $api_key_0 = $ticket_tailor_events_list_options['api_key_0']; // API key
 */

 ?>