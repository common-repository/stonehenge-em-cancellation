<?php
if( !defined('ABSPATH') ) exit;
if( !class_exists('Stonehenge_EM_Event_Cancellation_Process')) :

Class Stonehenge_EM_Event_Cancellation_Process extends Stonehenge_EM_Event_Cancellation_Admin {


	#===============================================
	public function process_cancellation( $post_id, $post, $updated ) {
		if( $post->post_type != 'event' || $post->post_status != $this->status ) {
			return;
		}

		$EM_Event 	= em_get_event($post_id, 'post_id');
		$rsvp_end 	= strtotime( $EM_Event->event_rsvp_date .' '. $EM_Event->event_rsvp_time );
		$now 		= current_time('timestamp');

		if( $rsvp_end > $now ) {
			$now_date  	= date('Y-m-d', $now);
			$now_time 	= date('H:i:s', $now);
			update_post_meta( $post_id, '_event_rsvp_date', $now_date );
			update_post_meta( $post_id, '_event_rsvp_time', $now_time );

			// Close all tickets.
			foreach( $EM_Event->get_tickets() as $EM_Ticket ) {
				$EM_Ticket->ticket_end = date('Y-m-d H:i:s', $now);
				$EM_Ticket->save();
			}

			// Set to "Cancelled".
			if( EM_MS_GLOBAL ) {
				global $wpdb;
				$wpdb->insert(EM_META_TABLE, array( 'meta_value' => $this->term_id, 'object_id' => $EM_Event->event_id, 'meta_key' => 'event-category'));
			}
			else {
				wp_set_object_terms( $EM_Event->post_id, $this->status, $this->taxonomy );
			}

			// Cancel Bookings.
			$EM_Bookings 	= $EM_Event->get_bookings();
			$send_email 	= isset($this->options['auto_send']) && $this->options['auto_send'] != 'no' ? true : false;
			if( $EM_Bookings ) {
				foreach( $EM_Bookings as $EM_Booking ) {
					if( in_array( (int) $EM_Booking->booking_status, $this->options['include'] ) ) {
						$note = sprintf( '%s: %s', $this->options['booking_status'], date(get_option('date_format') .' '. get_option('time_format'), $now) );
						$EM_Booking->add_note( $note );
						$EM_Booking->set_status( 6, $send_email );
					}
				}
			}
		}
	}


	#===============================================
	public function prepare_cancellation( $EM_Event ) {
		if( $EM_Event->post_status != $this->status ) {
			return;
		}

		// Remove current categories.
		$EM_Categories 	= $EM_Event->get_categories()->terms;
		if( !empty($EM_Categories) ) {
			$term_ids 	= array_keys($EM_Event->categories->terms);
			foreach( $term_ids as $id ) {
				unset( $EM_Event->categories->terms[$id] );
			}
		}

		// Maybe process Sub events as well.
		$this->check_for_ongoing($EM_Event);
	}


	#===============================================
	private function check_for_ongoing( $EM_Event ) {
		global $EM_Ongoing;
		// EM Ongoing Events not activated.
		if( !is_object( $EM_Ongoing ) ) {
			return;
		}

		// Is this part of an Ongoing Series?
		$parent_id = $EM_Ongoing->is_ongoing_series($EM_Event);
		if( !$parent_id ) {
			return;
		}
		// If Main event is cancelled, cancel all sub events as well.
		// A single sub event can be cancelled, while others do take place.
		if( $parent_id === $EM_Event->post_id ) {
			$sub_events = $EM_Ongoing->get_sub_events($parent_id);
			foreach( $sub_events as $sub ) {
				wp_delete_object_term_relationships( $sub->ID, $this->taxonomy );

				// Set to "Cancelled".
				if( EM_MS_GLOBAL ) {
					global $wpdb;
					$event_id = get_post_meta($sub->ID, '_event_id', true);
					$wpdb->insert(EM_META_TABLE, array( 'meta_value' => $this->term_id, 'object_id' => $event_id, 'meta_key' => 'event-category'));
				}
				else {
					wp_set_object_terms( $sub->ID, $this->status, $this->taxonomy );
				}

				// Cancel sub events as well.
				wp_update_post( array(
					'ID' 			=> $sub->ID,
					'post_status' 	=> $this->status,
				) );
			}
		}
		return;
	}


} // End class.
endif;
