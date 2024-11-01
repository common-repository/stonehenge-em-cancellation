<?php
if( !defined('ABSPATH') ) exit;
if( !class_exists('Stonehenge_EM_Event_Cancellation')) :

Class Stonehenge_EM_Event_Cancellation extends Stonehenge_EM_Event_Cancellation_Process {

	var $label;
	var $status;
	var $taxonomy;
	var $term_id;

	#===============================================
	public function __construct() {
		$plugin 		= self::plugin();
		$this->plugin 	= $plugin;
		$this->text 	= $plugin['text'];
		$this->options 	= $plugin['options'];
		$this->is_ready = is_array($plugin['options'] ) ? true : false;

		$slug = $plugin['slug'];

		// Options.
		add_action('stonehenge_before_form', array($this, 'define_plugin_info'));
		add_filter("{$slug}_options", array($this, 'define_options'), 10);

		if( $this->is_ready ) {
			// Set variables.
			$this->taxonomy = 'event-categories';
			$this->status 	= 'event-cancelled';
			$this->label	= $this->options['post_status'] ?? __em('Cancelled');

			add_action('admin_init', array($this, 'reactivation_hook'));
			add_action('init', array($this, 'add_post_status'));
			add_filter('display_post_states', array($this, 'add_post_status_title_label'));
			add_action('admin_footer-edit.php', array($this, 'add_post_status_to_quick_edit'));
			add_action('admin_footer-post.php', array($this, 'add_post_status_to_publishbox'));

			add_action('em_booking', array($this, 'add_booking_status'), 10, 2);
			add_filter('em_booking_email_messages', array($this, 'add_booking_email'), 10, 2);
			add_filter('em_bookings_table_booking_actions_6', array($this, 'add_booking_actions'), 10, 2);

			add_action('init', array($this, 'add_event_category'));
			add_action('admin_init', array($this, 'alter_events_list_filter'), 20);
			add_action('admin_init', array($this, 'show_category_column'));
			add_filter('parse_query', array($this, 'alter_events_list_query'));

			add_action('em_event_save_pre', array($this, 'prepare_cancellation'), 10, 1);
			add_action('save_post', array($this, 'process_cancellation'), 20, 3);
		}
	}


	#===============================================
	public static function plugin() {
		return stonehenge_em_cancellation();
	}


	#===============================================
	public static function dependency() {
		$dependency = array(
			'events-manager/events-manager.php' => 'Events Manager',
		);
		return $dependency;
	}

	#===============================================
	public static function register_assets() {
		return;
	}


	#===============================================
	public static function load_admin_assets() {
		return;
	}


	#===============================================
	public static function load_public_assets() {
		return;
	}


	#===============================================
	public static function plugin_updated() {
		return;
	}



	#===============================================
	public function define_plugin_info( $plugin ) {
		if( $plugin['url'] != $this->plugin['url'] ) {
			return;
		}

		$section = array(
			'id'		=> 'plugin_info',
			'label'		=> __wp('Info'),
			'fields' 	=> array(
				array(
					'id' 		=> 'info',
					'label' 	=> '',
					'type' 		=> 'info',
					'default' 	=> __('Unfortunately, you sometimes have to cancel a planned event. This add-on makes that extremely easy!', $this->text) .'<br><br>'. __('Just change the Post Status to "Cancelled" and this add-on will automatically:', $this->text) . sprintf('<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
__('Close the Booking Form by adjusting the Event cut-off time to the timestamp of cancellation;', $this->text),
__('Change the Event Category to the new "Event Cancelled";', $this->text),
__('Change the Booking Status to the new "Event Cancelled" for selected booking statuses;', $this->text) .'<br>('. sprintf( __('You might want to consider %s, prior to cancelling the event.', $this->text), $this->move_bookings_url() ) .')',
__('Send the new "Event Cancelled" Email to notify your customers.', $this->text), __('After cancelling an event, it will still be visible in the front end, but the booking form will be closed. This prevents 404-errors for visitors and search engines.', $this->text), __('Refunding online payments remains a manual procedure.', $this->text) ) .'<p><br>'. __('This add-on is fully integrated with the Events Manager Dashboard. These new options will work exactly the same as the built-in ones.', $this->text) .'</p><p><strong>'. sprintf( __('Compatibility with %s:', $this->text), '<a href="https://www.stonehengecreations.nl/creations/stonehenge-em-ongoing-events/" target="_blank" title="EM - Ongoing Events">EM Ongoing Events</a>') .'</strong><br>'. __('If you cancel a main event, all sub events will be cancelled as well, but if you cancel a sub event, the rest of this Ongoing Series will remain unchanged.', $this->text),
				),
			)
		);
		stonehenge()->render_metabox( $section, $section['id'], 0 );
	}


	#===============================================
	private function move_bookings_url() {
		$label 	= __('Moving the Booking', $this->text);
		$url 	= 'https://wordpress.org/plugins/stonehenge-em-move-bookings/';
		$link 	= "<a href='{$url}' target='_blank' title='EM - Move Bookings'>{$label}</a>";
		return $link;
	}


	#===============================================
	public function define_options( $sections = array() ) {
		$sections[] = array(
			'id' 		=> 'options',
			'label' 	=> __wp('Options'),
			'fields' 	=> array(
				array(
					'id' 		=> 'post_status',
					'label'		=> 'Post Status',
					'type' 		=> 'text',
					'required' 	=> true,
					'default'	=> __em('Cancelled'),
					'helper' 	=> __('Enter the label for the new post status. It will be shown in the Status Dropdown and used for the Event Category.', $this->text) .'<br>'. $this->post_status_names(),
				),
				array(
					'id' 		=> 'booking_status',
					'label'		=> __('Booking Status', $this->text),
					'type' 		=> 'text',
					'required' 	=> true,
					'default'	=> 'Event Cancelled',
					'helper' 	=> __('Enter the label for the new booking status. It will be shown in the Bookings Overview.', $this->text) .'<br>'. $this->booking_status_names(),
				),
				array(
					'id'		=> 'show_categories',
					'label'		=> __('Show Categories', $this->text),
					'type' 		=> 'toggle',
					'default'	=> 'no',
					'required' 	=> true,
					'helper'	=> __('Do you want to add a column to the Admin Events List displaying the Event Category?', $this->text),
				),
				array(
					'id' 		=> 'email_info',
					'label' 	=> '',
					'type' 		=> 'info',
					'default'	=> '<br><span class="h4">'. __('Event Cancellation Emails', $this->text) .'</span><br><span class="description">'. __('When you cancel an event, a notification email will be sent.', $this->text) .' '. __('It uses the same process and settings as your other EM Booking Emails.', $this->text) .'</span><p class="description">'. sprintf( __('This accepts %s, %s and %s placeholders.', $this->text), __('Booking Related Placeholders', 'events-manager'), __('Event Related Placeholders', 'events-manager'), __('Location Related Placeholders', 'events-manager')) .'</p>',
				),
				array(
					'id' 		=> 'subject',
					'label'		=> __wp('Subject'),
					'type'		=> 'text',
					'required' 	=> true,
					'default' 	=> get_option('dbem_bookings_email_rejected_subject'),
				),
				array(
					'id' 		=> 'content',
					'label'		=> __wp('Content'),
					'type'		=> 'editor',
					'required' 	=> true,
					'default' 	=> get_option('dbem_bookings_email_rejected_body'),
				),
				array(
					'id' 		=> 'include',
					'label' 	=> __('Include Status', $this->text),
					'type' 		=>'checkboxes',
					'required' 	=> true,
					'default'	=> 1,
					'choices' 	=> $this->get_booking_status(),
					'helper' 	=> __('Select each booking status that you want to include in the booking status change and notification emails.', $this->text),
				),
				array(
					'id' 		=> 'auto_send',
					'label' 	=> __('Auto Send', $this->text),
					'type' 		=>'toggle',
					'required' 	=> true,
					'default'	=> 'yes',
					'helper' 	=> __('Do you want the Event Cancellation Email to be sent automatically?', $this->text) .'<br>'. __('You can always (re)send it manually in the Single Booking Page.', $this->text),
				),
			)
		);
		return $sections;
	}


	#===============================================
	private function post_status_names() {
		$status = get_post_statuses();
		$values = array_values($status);
		$result = wp_sprintf('%s: %l.', __('Please note that these labels are already taken', $this->text), $values);
		return $result;
	}


	#===============================================
	private function get_booking_status() {
		$status = stonehenge()->get_em_booking_status();
		unset( $status[6] ); 	// Avoid confusion.
		return $status;
	}


	#===============================================
	private function booking_status_names() {
		$status = $this->get_booking_status();
		$values = array_values($status);
		$result = wp_sprintf('%s: %l.', __('Please note that these labels are already taken', $this->text), $values);
		return $result;
	}


} // End class.

new Stonehenge_EM_Event_Cancellation();
endif;