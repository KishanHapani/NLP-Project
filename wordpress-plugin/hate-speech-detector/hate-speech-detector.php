<?php
/**
 * Plugin Name:       Hate Speech Detector
 * Plugin URI:        https://github.com/KishanHapani/NLP-Project
 * Description:       Automatically screens blog comments for hate speech using the NLP Hate Speech Detection API before they are published.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            KishanHapani
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       hate-speech-detector
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'HSD_VERSION',     '1.0.0' );
define( 'HSD_PLUGIN_FILE', __FILE__ );
define( 'HSD_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'HSD_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

// ---------------------------------------------------------------------------
// Settings helpers
// ---------------------------------------------------------------------------

/**
 * Return the stored plugin options with sensible defaults.
 *
 * @return array<string, mixed>
 */
function hsd_get_options(): array {
    $defaults = array(
        'api_url'       => 'http://localhost:8080',
        'action'        => 'hold',   // 'hold' | 'trash' | 'spam'
        'notify_admin'  => true,
        'confidence'    => 0.5,
    );

    $saved = get_option( 'hsd_options', array() );
    return wp_parse_args( $saved, $defaults );
}

// ---------------------------------------------------------------------------
// Admin menu & settings page
// ---------------------------------------------------------------------------

add_action( 'admin_menu', 'hsd_add_admin_menu' );

function hsd_add_admin_menu(): void {
    add_options_page(
        __( 'Hate Speech Detector', 'hate-speech-detector' ),
        __( 'Hate Speech Detector', 'hate-speech-detector' ),
        'manage_options',
        'hate-speech-detector',
        'hsd_render_settings_page'
    );
}

function hsd_render_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    include HSD_PLUGIN_DIR . 'admin/templates/settings-page.php';
}

add_action( 'admin_init', 'hsd_register_settings' );

function hsd_register_settings(): void {
    register_setting(
        'hsd_options_group',
        'hsd_options',
        array(
            'sanitize_callback' => 'hsd_sanitize_options',
        )
    );
}

/**
 * Sanitise and validate submitted option values.
 *
 * @param  array<string, mixed> $input Raw POST data.
 * @return array<string, mixed>
 */
function hsd_sanitize_options( array $input ): array {
    $clean = array();

    $clean['api_url'] = isset( $input['api_url'] )
        ? esc_url_raw( trim( $input['api_url'] ) )
        : 'http://localhost:8080';

    $allowed_actions    = array( 'hold', 'trash', 'spam' );
    $clean['action']    = ( isset( $input['action'] ) && in_array( $input['action'], $allowed_actions, true ) )
        ? $input['action']
        : 'hold';

    $clean['notify_admin'] = ! empty( $input['notify_admin'] );

    $confidence           = isset( $input['confidence'] ) ? (float) $input['confidence'] : 0.5;
    $clean['confidence']  = max( 0.0, min( 1.0, $confidence ) );

    return $clean;
}

// ---------------------------------------------------------------------------
// Comment moderation
// ---------------------------------------------------------------------------

add_filter( 'pre_comment_approved', 'hsd_check_comment', 99, 2 );

/**
 * Check a new comment against the hate-speech detection API.
 *
 * @param  int|string           $approved   Current approval status.
 * @param  array<string, mixed> $comment_data Raw comment data.
 * @return int|string
 */
function hsd_check_comment( $approved, array $comment_data ) {
    // Skip trackbacks / pingbacks.
    if ( isset( $comment_data['comment_type'] ) && in_array( $comment_data['comment_type'], array( 'trackback', 'pingback' ), true ) ) {
        return $approved;
    }

    $options    = hsd_get_options();
    $api_url    = trailingslashit( $options['api_url'] ) . 'predict';
    $comment    = isset( $comment_data['comment_content'] ) ? (string) $comment_data['comment_content'] : '';

    if ( '' === $comment ) {
        return $approved;
    }

    $result = hsd_call_api( $api_url, $comment );

    if ( is_wp_error( $result ) ) {
        // API unavailable – let the comment through (fail-open).
        return $approved;
    }

    $is_hate   = hsd_parse_prediction( $result, $options['confidence'] );

    if ( ! $is_hate ) {
        return $approved;
    }

    // Flag the comment according to the configured action.
    if ( $options['notify_admin'] ) {
        hsd_notify_admin( $comment_data, $result );
    }

    switch ( $options['action'] ) {
        case 'spam':
            return 'spam';
        case 'trash':
            return 'trash';
        case 'hold':
        default:
            return 0; // Hold for moderation.
    }
}

/**
 * Call the prediction API and return the decoded JSON body or a WP_Error.
 *
 * @param  string $api_url Full prediction endpoint URL.
 * @param  string $text    Comment text to classify.
 * @return array<string, mixed>|\WP_Error
 */
function hsd_call_api( string $api_url, string $text ) {
    $response = wp_remote_post(
        $api_url,
        array(
            'timeout'    => 10,
            'body'       => array( 'text' => $text ),
            'user-agent' => 'WordPress/HateSpeechDetector ' . HSD_VERSION,
        )
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== (int) $code ) {
        return new WP_Error( 'hsd_api_error', "API returned HTTP {$code}" );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( ! is_array( $data ) ) {
        return new WP_Error( 'hsd_parse_error', 'Could not parse API response.' );
    }

    return $data;
}

/**
 * Decide whether the API result represents hate speech.
 *
 * The FastAPI /predict endpoint returns either:
 *   - A plain string label, e.g. "hate"
 *   - A JSON object with a "label" key and optionally a "confidence" key.
 *
 * @param  array<string, mixed>|string $result     Decoded API response.
 * @param  float                       $min_confidence Minimum confidence threshold.
 * @return bool
 */
function hsd_parse_prediction( $result, float $min_confidence ): bool {
    if ( is_string( $result ) ) {
        return strtolower( $result ) === 'hate';
    }

    $label      = isset( $result['label'] ) ? strtolower( (string) $result['label'] ) : '';
    $confidence = isset( $result['confidence'] ) ? (float) $result['confidence'] : 1.0;

    return $label === 'hate' && $confidence >= $min_confidence;
}

/**
 * Send an admin email when a comment is flagged.
 *
 * @param  array<string, mixed>        $comment_data Raw comment data.
 * @param  array<string, mixed>|string $result       API response.
 */
function hsd_notify_admin( array $comment_data, $result ): void {
    $admin_email = get_option( 'admin_email' );
    $blog_name   = get_option( 'blogname' );
    $author      = isset( $comment_data['comment_author'] ) ? $comment_data['comment_author'] : __( 'Unknown', 'hate-speech-detector' );
    $content     = isset( $comment_data['comment_content'] ) ? $comment_data['comment_content'] : '';

    $subject = sprintf(
        /* translators: %s: blog name */
        __( '[%s] Comment flagged for hate speech', 'hate-speech-detector' ),
        $blog_name
    );

    $message = sprintf(
        /* translators: 1: author name, 2: comment text */
        __( "A comment by \"%1\$s\" has been flagged as potential hate speech:\n\n%2\$s\n\nPlease review it in the WordPress admin.", 'hate-speech-detector' ),
        $author,
        $content
    );

    wp_mail( $admin_email, $subject, $message );
}

// ---------------------------------------------------------------------------
// Activation / deactivation hooks
// ---------------------------------------------------------------------------

register_activation_hook( HSD_PLUGIN_FILE, 'hsd_activate' );

function hsd_activate(): void {
    if ( ! get_option( 'hsd_options' ) ) {
        add_option( 'hsd_options', hsd_get_options() );
    }
}
