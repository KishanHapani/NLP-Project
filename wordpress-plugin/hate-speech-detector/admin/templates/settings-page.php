<?php
/**
 * Admin settings page template for the Hate Speech Detector plugin.
 *
 * @package HateSpeechDetector
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = hsd_get_options();
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php esc_html_e( 'Configure the connection to the NLP Hate Speech Detection API and choose how flagged comments are handled.', 'hate-speech-detector' ); ?></p>

    <form method="post" action="options.php">
        <?php settings_fields( 'hsd_options_group' ); ?>

        <table class="form-table" role="presentation">

            <!-- API URL -->
            <tr>
                <th scope="row">
                    <label for="hsd_api_url"><?php esc_html_e( 'API Base URL', 'hate-speech-detector' ); ?></label>
                </th>
                <td>
                    <input
                        type="url"
                        id="hsd_api_url"
                        name="hsd_options[api_url]"
                        value="<?php echo esc_attr( $options['api_url'] ); ?>"
                        class="regular-text"
                        placeholder="http://localhost:8080"
                    />
                    <p class="description">
                        <?php esc_html_e( 'The base URL of the running FastAPI hate-speech detection service (e.g. http://localhost:8080 or your deployed cloud endpoint).', 'hate-speech-detector' ); ?>
                    </p>
                </td>
            </tr>

            <!-- Confidence threshold -->
            <tr>
                <th scope="row">
                    <label for="hsd_confidence"><?php esc_html_e( 'Confidence Threshold', 'hate-speech-detector' ); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        id="hsd_confidence"
                        name="hsd_options[confidence]"
                        value="<?php echo esc_attr( $options['confidence'] ); ?>"
                        min="0"
                        max="1"
                        step="0.01"
                        class="small-text"
                    />
                    <p class="description">
                        <?php esc_html_e( 'Minimum confidence score (0.0 – 1.0) required to flag a comment. Lower values increase sensitivity.', 'hate-speech-detector' ); ?>
                    </p>
                </td>
            </tr>

            <!-- Action for flagged comments -->
            <tr>
                <th scope="row">
                    <?php esc_html_e( 'Action on Flagged Comments', 'hate-speech-detector' ); ?>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="hsd_options[action]" value="hold"
                                <?php checked( $options['action'], 'hold' ); ?> />
                            <?php esc_html_e( 'Hold for moderation', 'hate-speech-detector' ); ?>
                        </label><br />
                        <label>
                            <input type="radio" name="hsd_options[action]" value="trash"
                                <?php checked( $options['action'], 'trash' ); ?> />
                            <?php esc_html_e( 'Move to trash', 'hate-speech-detector' ); ?>
                        </label><br />
                        <label>
                            <input type="radio" name="hsd_options[action]" value="spam"
                                <?php checked( $options['action'], 'spam' ); ?> />
                            <?php esc_html_e( 'Mark as spam', 'hate-speech-detector' ); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>

            <!-- Notify admin -->
            <tr>
                <th scope="row">
                    <?php esc_html_e( 'Email Notifications', 'hate-speech-detector' ); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="hsd_options[notify_admin]" value="1"
                            <?php checked( $options['notify_admin'], true ); ?> />
                        <?php esc_html_e( 'Send me an email when a comment is flagged', 'hate-speech-detector' ); ?>
                    </label>
                </td>
            </tr>

        </table>

        <?php submit_button(); ?>
    </form>
</div>
