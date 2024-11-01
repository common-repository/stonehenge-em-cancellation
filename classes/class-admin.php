<?php
if( !defined('ABSPATH') ) exit;
if( !class_exists('Stonehenge_EM_Event_Cancellation_Admin')) :

Class Stonehenge_EM_Event_Cancellation_Admin {


	#===============================================
	public function add_post_status() {
		$args = array(
			'label'                     => $this->label,
			'label_count'               => _n_noop( $this->label .' <span class="count">(%s)</span>', $this->label .' <span class="count">(%s)</span>' ),
			'public'                    => true,
			'internal' 					=> false,
			'protected' 				=> false,
			'private' 					=> false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'publicly_queryable'		=> true,
			'post_type' 				=> array( 'event', 'event-recurring' ),
		);

		$args = apply_filters( 'em_cancellation_post_status_args', $args );
		register_post_status( $this->status, $args );
	}


	#===============================================
	public function add_post_status_title_label( $statuses ) {
		global $post;
		if( isset($post->post_status) && $post->post_status == $this->status ) {
			return array( $this->label );
		}
		return $statuses;
	}


	#===============================================
	public function add_post_status_to_quick_edit() {
		global $post;
		if( isset($post->post_type) && in_array($post->post_type, array('event', 'event-recurring') ) ) {
			?><script>
				jQuery('select[name="_status"]').append('<option value="<?php echo $this->status; ?>"><?php echo $this->label; ?></option>');
			</script><?php
		}
	}


	#===============================================
	public function add_post_status_to_publishbox() {
		global $post;
		if( isset($post->post_type) && in_array($post->post_type, array('event', 'event-recurring') ) ) {
			$selected = $post->post_status == $this->status ? 'selected="selected"' : null;
			?><script>
				jQuery(document).ready( function($) {
					var SELECTED = '<?php echo $selected; ?>';
					$('select#post_status').append('<option value="event-cancelled" '+ SELECTED +'><?php echo $this->label; ?></option>');
					$('.misc-pub-section label').append('<?php echo $this->label; ?>');

					if( SELECTED.length > 0) {
						$('#post-status-display').html('<?php echo $this->label; ?>');
					}
				});
			</script><?php
		}
	}


	#===============================================
	public function add_event_category() {
		if( get_option('dbem_categories_enabled') ) {
			if( EM_MS_GLOBAL ) { switch_to_blog( get_main_network_id() ); }

			$term = get_term_by('slug', $this->status, $this->taxonomy, OBJECT);
			if( !$term ) {
				$term = wp_insert_term($this->label, $this->taxonomy, array(
					'slug' => $this->status,
					'description' => 'Used by the EM Event Cancellation Plugin.'
				));
			}
			$this->term_id = is_object($term) ? $term->term_id : $term['term_id'];

			if( EM_MS_GLOBAL ) { restore_current_blog(); }
		}
	}


	#===============================================
	public function add_booking_status( $EM_Booking, $booking_data ) {
		$EM_Booking->status_array[6] = $this->options['booking_status'];
	}


	#===============================================
	public function add_booking_email( $msg, $EM_Booking ){
		if( $EM_Booking->booking_status == 6 ) {
			$msg['user']['subject'] = $this->options['subject'];
			$msg['user']['body'] 	= $this->options['content'];
		}
		return $msg;
	}


	#===============================================
	public function add_booking_actions( $booking_actions, $EM_Booking ) {
		$url 		= $EM_Booking->get_event()->get_bookings_url();
		$delete_url	= add_query_arg( array( 'action' => 'bookings_delete', 'booking_id' => $EM_Booking->booking_id ), $url );
		$edit_url  	= add_query_arg( array( 'booking_id' => $EM_Booking->booking_id, 'em_ajax' => null, 'em_obj' => null ), $url );

		$booking_actions = array(
			'delete' 	=> sprintf( '<a href="%s">%s</a>', $delete_url, __em('Delete') ),
			'edit' 		=> sprintf( '<a href="%s">%s</a>', $edit_url, __em('Edit/View') ),
		);
		return $booking_actions;
	}


	#===============================================
	public function alter_events_list_filter() {
		remove_action('restrict_manage_posts', array('EM_Event_Posts_Admin','restrict_manage_posts'));
		add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
	}


	#===============================================
	public function restrict_manage_posts() {
		global $wp_query;
		if( in_array($wp_query->query_vars['post_type'], array('event', 'event-recurring') ) ) {
			?>
			<select name="scope">
				<?php
				$scope = (!empty($wp_query->query_vars['scope'])) ? $wp_query->query_vars['scope'] : 'future';
				foreach( em_get_scopes() as $key => $value ) {
					$selected = ($key == $scope) ? "selected='selected'" : "";
					echo "<option value='{$key}' {$selected}>{$value}</option>";
				}
				?>
			</select>
			<?php
			if( get_option('dbem_categories_enabled') ) {
	            $selected = !empty($_GET['event-categories']) ? $_GET['event-categories'] : 0;
				if( EM_MS_GLOBAL ) { switch_to_blog( get_main_network_id() ); }
					$dropdown = wp_dropdown_categories( array(
						'hide_empty' => 0,
						'name' => EM_TAXONOMY_CATEGORY,
						'hierarchical' => true,
						'orderby' => 'name',
						'id' => EM_TAXONOMY_CATEGORY,
						'taxonomy' => EM_TAXONOMY_CATEGORY,
						'selected' => $selected,
						'show_option_all' => __wp('View all'),
		            ));
				if( EM_MS_GLOBAL ) { restore_current_blog(); }
			}

	        if( !empty($_REQUEST['author']) ){
	        	?>
	        	<input type="hidden" name="author" value="<?php echo esc_attr($_REQUEST['author']); ?>" />
	        	<?php
	        }
		}
	}


	#===============================================
	public function alter_events_list_query( $query ) {
		if( EM_MS_GLOBAL && isset($_REQUEST['filter_action']) && $_REQUEST['filter_action'] == 'Filter' && isset($_REQUEST['event-categories']) ) {
			if( $_REQUEST['event-categories'] != 0 ) {
				$post_ids 	= array();
				$EM_Events 	= EM_Events::get( array('scope' => $_REQUEST['scope']) );
				foreach( $EM_Events as $EM_Event ){
					foreach( $EM_Event->get_categories()->categories as $EM_Category ) {
						if( $EM_Category->term_id == $_REQUEST['event-categories'] ) {
							$post_ids[] = $EM_Event->post_id;
						}
					}
				}
				$query->query_vars['post__in'] = $post_ids;
			}
		}
	}


	#===============================================
	public function show_category_column() {
		if( isset($this->options['show_categories']) && $this->options['show_categories'] != 'no' ) {
			// Single Events.
			add_filter('manage_edit-event_columns', array( $this, 'add_category_column'), 5);
			add_filter('manage_event_posts_custom_column', array( $this, 'add_category_column_output'), 12, 2);

			// Recurring Events.
			add_filter('manage_edit-event-recurring_columns', array( $this, 'add_category_column'), 5);
			add_filter('manage_event-recurring_posts_custom_column', array( $this, 'add_category_column_output'), 12, 2);
		}
	}


	#===============================================
	public function add_category_column( $columns ) {
		$columns['category'] = __wp('Category');
		return $columns;
	}


	#===============================================
	public function add_category_column_output( $column, $post_id ) {
		switch( $column ) {
			case 'category':
				$names			= array();
				$EM_Event 		= new EM_Event( $post_id, 'post_id');
				$EM_Categories 	= $EM_Event->get_categories()->categories;
				foreach( $EM_Categories as $EM_Category ) {
					$names[] = $EM_Event->output("#_CATEGORYNAME");
					}
				echo implode(', ', $names);
			break;
		}
		return $column;
	}


	#===============================================
	public function reactivation_hook() {
		if( !get_option('em_cancelled_reactivated') ) {
			$EM_Events = EM_Events::get( array('scope' => 'all', 'category' => $this->term_id, 'status' => null) );
			if( !empty($EM_Events) ) {
			foreach( $EM_Events as $EM_Event ) {
					wp_update_post( array( 'ID' => $EM_Event->post_id, 'post_status' => $this->status) );
				}
			}
			update_option('em_cancelled_reactivated', true);
		}
		return;
	}


} // End class.
endif;

