<?php
/**
 * Gravity Forms Feed Add-On: Dynamics / Azure forwarder (theme-embedded).
 *
 * @package Muuttohaukat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MH_GF_Dynamics_AddOn extends GFFeedAddOn {

	const META_STATUS   = 'mh_gf_dynamics_status';
	const META_PAYLOAD  = 'mh_gf_dynamics_payload';
	const META_ERROR    = 'mh_gf_dynamics_error';
	const META_SENT_AT  = 'mh_gf_dynamics_sent_at';
	const META_RESPONSE = 'mh_gf_dynamics_http_code';

	/**
	 * @var string
	 */
	protected $_version = MH_GF_DYNAMICS_VERSION;

	/**
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.5';

	/**
	 * @var string
	 */
	protected $_slug = 'muuttohaukat-gf-dynamics';

	/**
	 * Theme-relative path (not a wp-content/plugins path).
	 *
	 * @var string
	 */
	protected $_path = 'Muuttohaukat/inc/gf-dynamics.php';

	/**
	 * @var string
	 */
	protected $_full_path = MH_GF_DYNAMICS_FILE;

	/**
	 * @var string
	 */
	protected $_title = 'Muuttohaukat Dynamics';

	/**
	 * @var string
	 */
	protected $_short_title = 'Dynamics';

	/**
	 * @var string|array
	 */
	protected $_capabilities_form_settings = 'gravityforms_edit_forms';

	/**
	 * @var string|array
	 */
	protected $_capabilities_settings_page = 'gravityforms_edit_settings';

	/**
	 * @var string|array
	 */
	protected $_capabilities_plugin_page = 'gravityforms_edit_settings';

	/**
	 * @var array
	 */
	protected $_capabilities = array(
		'gravityforms_edit_forms',
		'gravityforms_edit_settings',
	);

	/**
	 * Process feeds in the background so the form response stays fast,
	 * while the Azure POST itself can be blocking (so failures are visible).
	 *
	 * @var bool
	 */
	protected $_async_feed_processing = true;

	/**
	 * @var MH_GF_Dynamics_AddOn|null
	 */
	private static $_instance = null;

	/**
	 * @var bool
	 */
	private static $init_done = false;

	/**
	 * @return MH_GF_Dynamics_AddOn
	 */
	public static function get_instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Plugin bootstrap hooks.
	 */
	public function init() {
		if ( self::$init_done ) {
			return;
		}
		self::$init_done = true;

		parent::init();
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'register_entry_meta_box' ), 10, 3 );
		add_action( 'admin_post_mh_gf_dynamics_resend', array( $this, 'handle_resend' ) );
		add_action( 'admin_post_mh_gf_dynamics_test', array( $this, 'handle_test_connection' ) );
	}

	/**
	 * Left sidebar page under Lomakkeet: connection status + test button.
	 */
	public function plugin_page() {
		$endpoint = $this->get_endpoint();
		$result   = get_transient( 'mh_gf_dynamics_test_result' );
		if ( $result !== false ) {
			delete_transient( 'mh_gf_dynamics_test_result' );
		}

		$test_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=mh_gf_dynamics_test' ),
			'mh_gf_dynamics_test'
		);

		$settings_url = admin_url( 'admin.php?page=gf_settings&subview=' . $this->get_slug() );
		?>
		<div class="gform-settings-panel gform-settings-panel--full" style="max-width: 720px;">
			<header class="gform-settings-panel__header">
				<h4 class="gform-settings-panel__title"><?php esc_html_e( 'Dynamics-yhteys', 'muuttohaukat' ); ?></h4>
			</header>
			<div class="gform-settings-panel__content">
				<p><?php esc_html_e( 'Tämä teema lähettää Gravity Forms -merkinnät samaan Azure-osoitteeseen kuin LibreForm. LibreForm-yhteys ei muutu.', 'muuttohaukat' ); ?></p>

				<table class="widefat striped" style="margin: 1em 0;">
					<tbody>
						<tr>
							<th scope="row" style="width: 180px;"><?php esc_html_e( 'Endpoint', 'muuttohaukat' ); ?></th>
							<td><code style="word-break: break-all;"><?php echo esc_html( $endpoint ); ?></code></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Tila', 'muuttohaukat' ); ?></th>
							<td>
								<?php if ( $this->is_valid_endpoint( $endpoint ) ) : ?>
									<span style="color:#007017;"><?php esc_html_e( 'Endpoint on määritetty', 'muuttohaukat' ); ?></span>
								<?php else : ?>
									<span style="color:#b32d2e;"><?php esc_html_e( 'Endpoint puuttuu tai on virheellinen — aseta se Teeman asetuksissa tai Dynamics-asetuksissa.', 'muuttohaukat' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Add-on', 'muuttohaukat' ); ?></th>
							<td><?php esc_html_e( 'Aktiivinen (teemassa)', 'muuttohaukat' ); ?> · v<?php echo esc_html( MH_GF_DYNAMICS_VERSION ); ?></td>
						</tr>
					</tbody>
				</table>

				<?php if ( is_array( $result ) ) : ?>
					<?php if ( ! empty( $result['ok'] ) ) : ?>
						<div class="notice notice-success inline"><p><?php echo esc_html( $result['message'] ); ?></p></div>
					<?php else : ?>
						<div class="notice notice-error inline"><p><?php echo esc_html( $result['message'] ); ?></p></div>
					<?php endif; ?>
				<?php endif; ?>

				<p>
					<a class="button button-primary" href="<?php echo esc_url( $test_url ); ?>">
						<?php esc_html_e( 'Testaa yhteys', 'muuttohaukat' ); ?>
					</a>
					<a class="button" href="<?php echo esc_url( $settings_url ); ?>">
						<?php esc_html_e( 'Endpoint-asetukset', 'muuttohaukat' ); ?>
					</a>
				</p>
				<p class="description">
					<?php esc_html_e( 'Testi lähettää pienen JSON-pyynnön Azureen (ei oikeaa tarjousta). Onnistunut HTTP 2xx vahvistaa, että osoite vastaa.', 'muuttohaukat' ); ?>
				</p>
				<p class="description">
					<?php esc_html_e( 'Lomakekohtaiset feedit: avaa lomake → Asetukset → Dynamics.', 'muuttohaukat' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * admin-post: ping Azure with a harmless probe payload.
	 */
	public function handle_test_connection() {
		if ( ! current_user_can( 'gravityforms_edit_settings' ) && ! current_user_can( 'gform_full_access' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'muuttohaukat' ) );
		}
		check_admin_referer( 'mh_gf_dynamics_test' );

		$endpoint = $this->get_endpoint();
		if ( ! $this->is_valid_endpoint( $endpoint ) ) {
			set_transient(
				'mh_gf_dynamics_test_result',
				array(
					'ok'      => false,
					'message' => __( 'Endpoint puuttuu tai on virheellinen.', 'muuttohaukat' ),
				),
				60
			);
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->get_slug() ) );
			exit;
		}

		$probe = wp_json_encode(
			array(
				'kind' => 'connectionTest',
				'data' => array(
					'source'    => 'muuttohaukat-gf-dynamics',
					'timestamp' => gmdate( 'c' ),
				),
			)
		);

		$response = wp_remote_post(
			$endpoint,
			array(
				'blocking'    => true,
				'timeout'     => 15,
				'redirection' => 2,
				'headers'     => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'Accept'       => 'application/json',
				),
				'body'        => $probe,
			)
		);

		if ( is_wp_error( $response ) ) {
			set_transient(
				'mh_gf_dynamics_test_result',
				array(
					'ok'      => false,
					'message' => sprintf(
						/* translators: %s: error message */
						__( 'Yhteys epäonnistui: %s', 'muuttohaukat' ),
						$response->get_error_message()
					),
				),
				60
			);
		} else {
			$code = (int) wp_remote_retrieve_response_code( $response );
			// Any HTTP response means the host is reachable (Azure may return 400 for our probe kind).
			$ok = $code >= 100 && $code < 600;
			set_transient(
				'mh_gf_dynamics_test_result',
				array(
					'ok'      => $ok,
					'message' => $ok
						? sprintf(
							/* translators: %d: HTTP status */
							__( 'Azure vastasi (HTTP %d). Yhteys toimii.', 'muuttohaukat' ),
							$code
						)
						: sprintf(
							/* translators: %d: HTTP status */
							__( 'Odottamaton vastaus (HTTP %d).', 'muuttohaukat' ),
							$code
						),
				),
				60
			);
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . $this->get_slug() ) );
		exit;
	}

	/**
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'     => esc_html__( 'Name', 'muuttohaukat-gf-dynamics' ),
			'wplf_form_id' => esc_html__( 'LibreForm ID', 'muuttohaukat-gf-dynamics' ),
		);
	}

	/**
	 * @param array  $feed  Feed.
	 * @param string $column Column key.
	 * @return string
	 */
	public function get_column_value_wplf_form_id( $feed, $column = '' ) {
		$id = (string) rgars( $feed, 'meta/wplf_form_id' );
		return $id !== '' ? esc_html( $id ) : '—';
	}

	/**
	 * Feed settings UI.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Dynamics feed', 'muuttohaukat-gf-dynamics' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Feed name', 'muuttohaukat-gf-dynamics' ),
						'type'    => 'text',
						'name'    => 'feedName',
						'class'   => 'medium',
						'required' => true,
					),
					array(
						'label'   => esc_html__( 'Impersonate LibreForm', 'muuttohaukat-gf-dynamics' ),
						'type'    => 'select',
						'name'    => 'wplf_form_id',
						'choices' => $this->get_libreform_choices(),
						'required' => true,
						'tooltip' => esc_html__( 'Azure identifies the offer type by this numeric LibreForm post ID. Pick the LibreForm this Gravity Form replaces.', 'muuttohaukat-gf-dynamics' ),
					),
					array(
						'name'    => 'field_map',
						'label'   => esc_html__( 'Field mapping', 'muuttohaukat-gf-dynamics' ),
						'type'    => 'dynamic_field_map',
						'tooltip' => esc_html__( 'Map LibreForm entry keys (left) to Gravity Forms fields (right). Unchecked checkboxes must stay unmapped or empty so they are omitted from the payload.', 'muuttohaukat-gf-dynamics' ),
						'enable_custom_key' => false,
						'key_field' => array(
							'choices' => mh_gf_dynamics_wplf_field_choices(),
							'title'   => esc_html__( 'LibreForm key', 'muuttohaukat-gf-dynamics' ),
						),
						'value_field' => array(
							'title' => esc_html__( 'Gravity Forms field', 'muuttohaukat-gf-dynamics' ),
						),
					),
					array(
						'label'   => esc_html__( 'Test mode', 'muuttohaukat-gf-dynamics' ),
						'type'    => 'checkbox',
						'name'    => 'test_mode',
						'choices' => array(
							array(
								'label' => esc_html__( 'Build and log the payload without sending to Azure', 'muuttohaukat-gf-dynamics' ),
								'name'  => 'test_mode',
							),
						),
					),
					array(
						'name'           => 'condition',
						'label'          => esc_html__( 'Conditional logic', 'muuttohaukat-gf-dynamics' ),
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable conditional logic', 'muuttohaukat-gf-dynamics' ),
						'instructions'   => esc_html__( 'Process this feed if', 'muuttohaukat-gf-dynamics' ),
					),
				),
			),
		);
	}

	/**
	 * Optional plugin-level endpoint override (defaults to theme option).
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Azure endpoint', 'muuttohaukat-gf-dynamics' ),
				'fields' => array(
					array(
						'name'    => 'endpoint_override',
						'label'   => esc_html__( 'Endpoint URL override', 'muuttohaukat-gf-dynamics' ),
						'type'    => 'text',
						'class'   => 'large',
						'tooltip' => esc_html__( 'Leave empty to use the same endpoint as the theme (Teeman asetukset → D365).', 'muuttohaukat-gf-dynamics' ),
					),
				),
			),
		);
	}

	/**
	 * @return array<int, array{label: string, value: string}>
	 */
	private function get_libreform_choices() {
		$choices = array(
			array(
				'label' => esc_html__( '— Select LibreForm —', 'muuttohaukat-gf-dynamics' ),
				'value' => '',
			),
		);

		$posts = get_posts(
			array(
				'post_type'              => 'libreform',
				'post_status'            => array( 'publish', 'draft', 'private' ),
				'numberposts'            => 100,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $posts as $post ) {
			$slug = $post->post_name ? $post->post_name : '(no slug)';
			$choices[] = array(
				'label' => sprintf( '%s (#%d · %s)', $post->post_title, $post->ID, $slug ),
				'value' => (string) $post->ID,
			);
		}

		if ( count( $choices ) === 1 ) {
			$choices[] = array(
				'label' => esc_html__( 'No libreform posts found — enter ID via custom mapping after creating forms', 'muuttohaukat-gf-dynamics' ),
				'value' => '',
			);
		}

		return $choices;
	}

	/**
	 * Process a single feed for an entry.
	 *
	 * @param array $feed  Feed.
	 * @param array $entry Entry.
	 * @param array $form  Form.
	 * @return void
	 */
	public function process_feed( $feed, $entry, $form ) {
		$wplf_form_id = absint( rgars( $feed, 'meta/wplf_form_id' ) );
		if ( ! $wplf_form_id ) {
			$this->add_feed_error( __( 'Dynamics feed is missing the impersonated LibreForm ID.', 'muuttohaukat-gf-dynamics' ), $feed, $entry, $form );
			return;
		}

		$payload = MH_GF_Dynamics_Payload_Builder::build( $feed, $entry, $form, $this );
		$json    = MH_GF_Dynamics_Payload_Builder::encode( $payload );

		if ( $json === false ) {
			$this->add_feed_error( __( 'Could not JSON-encode the Dynamics payload.', 'muuttohaukat-gf-dynamics' ), $feed, $entry, $form );
			gform_update_meta( $entry['id'], self::META_STATUS, 'error' );
			gform_update_meta( $entry['id'], self::META_ERROR, 'json_encode failed' );
			return;
		}

		gform_update_meta( $entry['id'], self::META_PAYLOAD, $json );

		$test_mode = (string) rgars( $feed, 'meta/test_mode' ) === '1';
		if ( $test_mode ) {
			$this->log_debug( __METHOD__ . '(): Test mode — payload stored, not sent. ' . $json );
			$this->add_note( $entry['id'], __( 'Dynamics test mode: payload built and stored; Azure was not called.', 'muuttohaukat-gf-dynamics' ), 'success' );
			gform_update_meta( $entry['id'], self::META_STATUS, 'test' );
			gform_update_meta( $entry['id'], self::META_ERROR, '' );
			gform_update_meta( $entry['id'], self::META_SENT_AT, current_time( 'mysql' ) );
			return;
		}

		$result = $this->send_to_azure( $json );

		if ( is_wp_error( $result ) ) {
			$message = $result->get_error_message();
			$this->add_feed_error( $message, $feed, $entry, $form );
			gform_update_meta( $entry['id'], self::META_STATUS, 'error' );
			gform_update_meta( $entry['id'], self::META_ERROR, $message );
			gform_update_meta( $entry['id'], self::META_SENT_AT, current_time( 'mysql' ) );
			return;
		}

		$code = (int) $result;
		gform_update_meta( $entry['id'], self::META_RESPONSE, $code );
		gform_update_meta( $entry['id'], self::META_SENT_AT, current_time( 'mysql' ) );

		if ( $code >= 200 && $code < 300 ) {
			$this->add_note(
				$entry['id'],
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Dynamics: forwarded successfully (HTTP %d).', 'muuttohaukat-gf-dynamics' ),
					$code
				),
				'success'
			);
			gform_update_meta( $entry['id'], self::META_STATUS, 'success' );
			gform_update_meta( $entry['id'], self::META_ERROR, '' );
			$this->log_debug( __METHOD__ . "(): Azure accepted payload (HTTP {$code})." );
			return;
		}

		$message = sprintf(
			/* translators: %d: HTTP status code */
			__( 'Dynamics: Azure returned HTTP %d.', 'muuttohaukat-gf-dynamics' ),
			$code
		);
		$this->add_feed_error( $message, $feed, $entry, $form );
		gform_update_meta( $entry['id'], self::META_STATUS, 'error' );
		gform_update_meta( $entry['id'], self::META_ERROR, $message );
	}

	/**
	 * Resolve endpoint: plugin override → theme helpers → option → default.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		$settings = $this->get_plugin_settings();
		$override = is_array( $settings ) ? (string) rgar( $settings, 'endpoint_override' ) : '';
		if ( $override !== '' && $this->is_valid_endpoint( $override ) ) {
			return $override;
		}

		if ( function_exists( '\\Muuttohaukat\\d365_endpoint' ) ) {
			$endpoint = \Muuttohaukat\d365_endpoint();
			if ( is_string( $endpoint ) && $this->is_valid_endpoint( $endpoint ) ) {
				return $endpoint;
			}
		}

		$stored = get_option( 'muuttohaukat_d365_endpoint', '' );
		if ( is_string( $stored ) && $this->is_valid_endpoint( $stored ) ) {
			return $stored;
		}

		$backup = get_option( 'muuttohaukat_d365_endpoint_backup', '' );
		if ( is_string( $backup ) && $this->is_valid_endpoint( $backup ) ) {
			return $backup;
		}

		if ( defined( 'MUUTTOHAUKAT_D365_ENDPOINT' ) && $this->is_valid_endpoint( (string) MUUTTOHAUKAT_D365_ENDPOINT ) ) {
			return (string) MUUTTOHAUKAT_D365_ENDPOINT;
		}

		return 'https://func-muuttohaukat-xrm-prod.azurewebsites.net/api/AddOfferToDynamics';
	}

	/**
	 * @param string $endpoint URL.
	 * @return bool
	 */
	private function is_valid_endpoint( $endpoint ) {
		if ( function_exists( '\\Muuttohaukat\\d365_endpoint_is_valid' ) ) {
			return (bool) \Muuttohaukat\d365_endpoint_is_valid( $endpoint );
		}
		return is_string( $endpoint ) && $endpoint !== '' && (bool) wp_http_validate_url( $endpoint );
	}

	/**
	 * Blocking POST so failures surface on the entry.
	 *
	 * @param string $json JSON body.
	 * @return int|WP_Error HTTP status code or error.
	 */
	public function send_to_azure( $json ) {
		$endpoint = $this->get_endpoint();
		if ( ! $this->is_valid_endpoint( $endpoint ) ) {
			return new WP_Error( 'mh_gf_dynamics_endpoint', __( 'Dynamics endpoint is missing or invalid.', 'muuttohaukat-gf-dynamics' ) );
		}

		$response = wp_remote_post(
			$endpoint,
			array(
				'blocking'    => true,
				'timeout'     => 15,
				'redirection' => 2,
				'headers'     => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'Accept'       => 'application/json',
				),
				'body'        => $json,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return (int) wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Entry detail sidebar meta box.
	 *
	 * @param array $meta_boxes Boxes.
	 * @param array $entry      Entry.
	 * @param array $form       Form.
	 * @return array
	 */
	public function register_entry_meta_box( $meta_boxes, $entry, $form ) {
		$meta_boxes['mh_gf_dynamics'] = array(
			'title'    => esc_html__( 'Dynamics', 'muuttohaukat-gf-dynamics' ),
			'callback' => array( $this, 'render_entry_meta_box' ),
			'context'  => 'side',
		);
		return $meta_boxes;
	}

	/**
	 * @param array $args Args with entry/form.
	 */
	public function render_entry_meta_box( $args ) {
		$entry   = rgar( $args, 'entry' );
		$form    = rgar( $args, 'form' );
		$id      = absint( rgar( $entry, 'id' ) );
		$status  = (string) gform_get_meta( $id, self::META_STATUS );
		$error   = (string) gform_get_meta( $id, self::META_ERROR );
		$sent    = (string) gform_get_meta( $id, self::META_SENT_AT );
		$code    = gform_get_meta( $id, self::META_RESPONSE );
		$payload = (string) gform_get_meta( $id, self::META_PAYLOAD );

		echo '<p><strong>' . esc_html__( 'Status', 'muuttohaukat-gf-dynamics' ) . ':</strong> ';
		echo esc_html( $status !== '' ? $status : '—' );
		echo '</p>';

		if ( $sent !== '' ) {
			echo '<p><strong>' . esc_html__( 'Last attempt', 'muuttohaukat-gf-dynamics' ) . ':</strong> ' . esc_html( $sent ) . '</p>';
		}
		if ( $code !== false && $code !== '' && $code !== null ) {
			echo '<p><strong>' . esc_html__( 'HTTP', 'muuttohaukat-gf-dynamics' ) . ':</strong> ' . esc_html( (string) $code ) . '</p>';
		}
		if ( $error !== '' ) {
			echo '<p style="color:#b32d2e;"><strong>' . esc_html__( 'Error', 'muuttohaukat-gf-dynamics' ) . ':</strong> ' . esc_html( $error ) . '</p>';
		}

		if ( $payload !== '' ) {
			$pretty = $payload;
			$decoded = json_decode( $payload, true );
			if ( is_array( $decoded ) ) {
				$encoded = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				if ( is_string( $encoded ) && $encoded !== '' ) {
					$pretty = $encoded;
				}
			}

			echo '<p><strong>' . esc_html__( 'Payload', 'muuttohaukat-gf-dynamics' ) . ':</strong></p>';
			echo '<textarea readonly rows="12" style="width:100%;font-family:Consolas,Monaco,monospace;font-size:11px;line-height:1.35;">';
			echo esc_textarea( $pretty );
			echo '</textarea>';
		}

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action'   => 'mh_gf_dynamics_resend',
					'entry_id' => $id,
					'form_id'  => absint( rgar( $form, 'id' ) ),
				),
				admin_url( 'admin-post.php' )
			),
			'mh_gf_dynamics_resend_' . $id
		);

		echo '<p><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Lähetä uudelleen', 'muuttohaukat-gf-dynamics' ) . '</a></p>';
		echo '<p class="description">' . esc_html__( 'Rebuilds the payload from the current feed mapping and posts to Azure (ignores test mode).', 'muuttohaukat-gf-dynamics' ) . '</p>';
	}

	/**
	 * admin-post handler for resend.
	 */
	public function handle_resend() {
		$entry_id = isset( $_GET['entry_id'] ) ? absint( $_GET['entry_id'] ) : 0;
		$form_id  = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		if ( ! $entry_id || ! $form_id || ! current_user_can( 'gravityforms_view_entries' ) ) {
			wp_die( esc_html__( 'You do not have permission to resend this entry.', 'muuttohaukat-gf-dynamics' ) );
		}

		check_admin_referer( 'mh_gf_dynamics_resend_' . $entry_id );

		$entry = GFAPI::get_entry( $entry_id );
		$form  = GFAPI::get_form( $form_id );

		if ( is_wp_error( $entry ) || ! $form ) {
			wp_die( esc_html__( 'Entry or form not found.', 'muuttohaukat-gf-dynamics' ) );
		}

		$feeds = $this->get_feeds( $form_id );
		$feed  = null;
		foreach ( $feeds as $candidate ) {
			if ( ! empty( $candidate['is_active'] ) ) {
				$feed = $candidate;
				break;
			}
		}

		if ( ! $feed ) {
			wp_die( esc_html__( 'No active Dynamics feed on this form.', 'muuttohaukat-gf-dynamics' ) );
		}

		// Force a real send even if the feed is in test mode.
		$feed['meta']['test_mode'] = '';
		$this->process_feed( $feed, $entry, $form );

		$redirect = add_query_arg(
			array(
				'page' => 'gf_entries',
				'view' => 'entry',
				'id'   => $form_id,
				'lid'  => $entry_id,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}
}
