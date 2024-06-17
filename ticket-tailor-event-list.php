<?php
/**
 * Plugin Name: Ticket Tailor - Event List
 * Plugin URI: 
 * Description: Display content using a shortcode to insert in a page or post
 * Version: 0.1
 * Text Domain: ticket-tailor-event-list
 * Author: Joshua Sampson
 * Author URI: https://buymeacoffee.com/pyro1son
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
			'ticket_tailor_events_list_api_setting_section', // id
			'API Settings', // title
			array( $this, 'ticket_tailor_events_list_section_info' ), // callback
			'ticket-tailor-events-list-admin' // page
		);
		
		add_settings_section(
			'ticket_tailor_events_list_display_setting_section', // id
			'Display Settings', // title
			array( $this, 'ticket_tailor_events_list_section_info' ), // callback
			'ticket-tailor-events-list-admin' // page
		);

		add_settings_field(
			'api_key_0', // id
			'API key', // title
			array( $this, 'api_key_0_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_api_setting_section' // section
		);
		
		add_settings_field(
			'no_events_text', // id
			'Message when no events returned', // title
			array( $this, 'no_events_text_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_display_setting_section' // section
		);

		add_settings_field(
			'date_format', // id
			'Date format (e.g. D d M)', // title
			array( $this, 'date_format_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_display_setting_section' // section
		);
		
		add_settings_field(
			'hide_time_range', // id
			'Hide time range?', // title
			array( $this, 'hide_time_range_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_display_setting_section' // section
		);
		
		add_settings_field(
			'hide_venue', // id
			'Hide venue details?', // title
			array( $this, 'hide_venue_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_display_setting_section' // section
		);
		
		add_settings_field(
			'hide_thumbnail', // id
			'Hide thumbnail?', // title
			array( $this, 'hide_thumbnail_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_display_setting_section' // section
		);
		
		add_settings_field(
			'show_compact_view', // id
			'Show compact view', // title
			array( $this, 'show_compact_view_callback' ), // callback
			'ticket-tailor-events-list-admin', // page
			'ticket_tailor_events_list_display_setting_section' // section
		);
	}

	public function ticket_tailor_events_list_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['api_key_0'] ) ) {
			$sanitary_values['api_key_0'] = sanitize_text_field( $input['api_key_0'] );
		}

		if ( isset( $input['no_events_text'] ) ) {
			$sanitary_values['no_events_text'] = sanitize_text_field( $input['no_events_text'] );
		}
		
		if ( isset( $input['date_format'] ) ) {
			$sanitary_values['date_format'] = sanitize_text_field( $input['date_format'] );
		}
	
		if ( isset( $input['hide_time_range'] ) ) {
			$sanitary_values['hide_time_range'] = $input['hide_time_range'] == 'on' ? 1 : 0;
		}

		if ( isset( $input['hide_venue'] ) ) {
			$sanitary_values['hide_venue'] = $input['hide_venue'] == 'on' ? 1 : 0;
		}
		
		if ( isset( $input['hide_thumbnail'] ) ) {
			$sanitary_values['hide_thumbnail'] = $input['hide_thumbnail'] == 'on' ? 1 : 0;
		}
		
		if ( isset( $input['show_compact_view'] ) ) {
			$sanitary_values['show_compact_view'] = $input['show_compact_view'] == 'on' ? 1 : 0;
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

	public function no_events_text_callback() {
		printf(
			'<input class="regular-text" type="text" name="ticket_tailor_events_list_option_name[no_events_text]" id="no_events_text" value="%s">',
			isset( $this->ticket_tailor_events_list_options['no_events_text'] ) ? esc_attr( $this->ticket_tailor_events_list_options['no_events_text']) : ''
		);
	}
	
	public function date_format_callback() {
		printf(
			'<input class="regular-text" type="text" name="ticket_tailor_events_list_option_name[date_format]" id="date_format" value="%s">',
			isset( $this->ticket_tailor_events_list_options['date_format'] ) ? esc_attr( $this->ticket_tailor_events_list_options['date_format']) : ''
		);
	}
	
	public function show_compact_view_callback() {
		printf(
			'<input class="regular-text" type="checkbox" name="ticket_tailor_events_list_option_name[show_compact_view]" id="show_compact_view" %s>',
			isset( $this->ticket_tailor_events_list_options['show_compact_view'] ) ? ( $this->ticket_tailor_events_list_options['show_compact_view'] == 1 ? 'checked' : '' ) : ''
		);
	}
	
	public function hide_time_range_callback() {
		printf(
			'<input class="regular-text" type="checkbox" name="ticket_tailor_events_list_option_name[hide_time_range]" id="hide_time_range" %s>',
			isset( $this->ticket_tailor_events_list_options['hide_time_range'] ) ? ( $this->ticket_tailor_events_list_options['hide_time_range'] == 1 ? 'checked' : '' ) : ''
		);
	}
	
	public function hide_venue_callback() {
		printf(
			'<input class="regular-text" type="checkbox" name="ticket_tailor_events_list_option_name[hide_venue]" id="hide_venue" %s>',
			isset( $this->ticket_tailor_events_list_options['hide_venue'] ) ? ( $this->ticket_tailor_events_list_options['hide_venue'] == 1 ? 'checked' : '' ) : ''
		);
	}
	
	public function hide_thumbnail_callback() {
		printf(
			'<input class="regular-text" type="checkbox" name="ticket_tailor_events_list_option_name[hide_thumbnail]" id="hide_thumbnail" %s>',
			isset( $this->ticket_tailor_events_list_options['hide_thumbnail'] ) ? ( $this->ticket_tailor_events_list_options['hide_thumbnail'] == 1 ? 'checked' : '' ) : ''
		);
	}

    public function ticket_tailor_event_list_shortcode($args) {
        $ticket_tailor_events_list_options = get_option( 'ticket_tailor_events_list_option_name' );
        $tt_url = 'https://api.tickettailor.com/v1/events?status=published&start_at.gt=' . time();
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

		usort($events['data'], fn($a, $b) => $a['start']['unix'] <=> $b['start']['unix']);
		
		if (count($events['data']) === 0) {
			$content .= "<div class='ticket_tailor_event_none'>";
			if (!empty($ticket_tailor_events_list_options['no_events_text'])) {
				$content .= $ticket_tailor_events_list_options['no_events_text'];
			} else {
				$content .= "No events currently available";
			}
			$content .= "</div>";
		} else {		
			foreach($events['data'] as $event){
				$content .= "<hr class='dashicon' data-ticket-tailor-date='" . date_format(date_create($event['start']['date']),!empty($ticket_tailor_events_list_options['date_format']) ? $ticket_tailor_events_list_options['date_format'] : 'D d M') . "' data-ticket-date-group-" . date_format(date_create($event['start']['date']),'dno') . " />";
				$content .= "<div class='ticket_tailor_event";
				if (array_key_exists('show_compact_view', $ticket_tailor_events_list_options) && $ticket_tailor_events_list_options['show_compact_view'] == 1) {
					$content .= " reduced";
				}
				$content .= "'>";
				$content .= "<div class='ticket_tailor_header'>";
				$content .= "<div class='ticket_tailor_event_title'>" . $event['name'] . "</div>";
				if (($ticket_tailor_events_list_options['hide_time_range'] ?? 0 ) != 1) {
					if ($event['start']['date'] == $event['end']['date']) {
						$content .= "<div class='ticket_tailor_event_time_range'>";
						$content .= $event['start']['time'];
						$content .= " <span class='to'>to</span> ";
						$content .= $event['end']['time'];
						$content .= "</div>";
					} else {
						$content .= "<div class='ticket_tailor_event_time_range'>";
						$content .= date_format(date_create($event['start']['iso']), 'd M @ H:i');
						$content .= " <span class='to'>to</span> ";
						$content .= date_format(date_create($event['end']['iso']), 'd M @ H:i');
						$content .= "</div>";
					}
				}
				if (($ticket_tailor_events_list_options['hide_venue'] ?? 0) != 1 || (empty($event['venue']['postal_code']) && empty($event['venue']['name']))) {
					$content .= "<div class='ticket_tailor_event_venue'><span class='dashicons dashicons-location'></span>";
					if (!empty($event['venue']['name'])) {
						$content .= $event['venue']['name'];
					}
					if (!empty($event['venue']['postal_code']) && !empty($event['venue']['name'])) {
						$content .= ", ";
					}
					if (!empty($event['venue']['postal_code'] )) {
						$content .= "<span class='postcode'>" . $event['venue']['postal_code'] . "</span>";
					}
					$content .= "</div>";
				}
				$content .= "</div>";
				// $content .= "<div class='ticket_tailor_event_body'>";
				// $content .= "<div class='ticket_tailor_event_details'>";
				$content .= "<div class='ticket_tailor_event_description'>";
				// if (strlen($event['description']) > 500) {
				//	 $content .= substr($event['description'], 0, 497) . '...';
				// } else  {
				$content .= $event['description'];
				// }
				$content .= "</div>";
				// $content .= "</div>";
				if (($ticket_tailor_events_list_options['hide_thumbnail'] ?? 0) != 1) {
					$content .= "<div class='ticket_tailor_event_image'><img src='";
					if (array_key_exists('show_compact_view', $ticket_tailor_events_list_options) && $ticket_tailor_events_list_options['show_compact_view'] == 1) {
						$content .=  $event['images']['thumbnail'];
					} else { 
						$content .= str_replace('h_108,q_85,w_108','h_300,q_85,w_300',$event['images']['thumbnail']);
					}
					$content .= "' /></div>";
				}
				// $content .= "</div>";
				$content .= "<div class='ticket_tailor_event_actions'>";
				if	($event['tickets_available'] === false) {
					$content .= "<span class='ticket_tailor_event_sold_out'>SOLD OUT</span>";
				} else {
					$content .= "<a class='ticket_tailor_event_link' target='_blank' href='" . $event['url'] . "'>" . $event['call_to_action'] . "</a>";
				}
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