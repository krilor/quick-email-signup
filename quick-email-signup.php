<?php
/**
 * WP Chatbot
 *
 * @package     QES
 * @author      Kristoffer Lorentsen
 * @copyright   2017 Kristoffer Lorentsen
 * @license     GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Quick Email Signup
 * Plugin URI: https://github.com/krilor/quick-email-signup
 * Description: Simple implementation of email-only signup
 * Version: 0.1.0
 * Author: Kristoffer Lorentsen
 * Author URI: http://lorut.no
 * Text Domain: qe-signup
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Quick_Email_Signup {
	/* Singleton */
  protected static $instance;
  public $version;

	/**
	 * Main plugin class instance
	 *
	 * Modelled after EDD, to insure that there is only one instance of the plugin at the time. Prevents globals. Follows singleton pattern.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ){

			$class = self::class;

			self::$instance = new $class;

		}

		return self::$instance;

	}

  /**
   * Construct and define hooks
   */
	public function __construct() {

    $this->version = '0.0.1';
		$this->define_hooks();

	}

  /**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_hooks() {

    // Scripts and styles
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'wp_head', array( $this, 'inline_styles'), 100 );

    // Shortcode
    add_shortcode('quick-email-signup',array( $this, 'registration_form' ) );

    // AJAX callbacks
    add_action( 'wp_ajax_nopriv_qes_add_user', array( $this, 'add_user' ) );
    add_action( 'wp_ajax_qes_add_user', array( $this, 'add_user' ) );

	}


  /**
   * Enque the styles needed
   */
  public function inline_styles(){
    echo '<style type="text/css">.qes-status { display: none; }</style>';
  }

  /**
   * Enque the scripts needed
   */
  public function enqueue_scripts(){

    wp_enqueue_script( 'qe-signup', plugin_dir_url( __DIR__ ) . 'quick-email-signup/quick-email-signup.js', array( 'jquery' ), $this->version, true );

    /* Localized JS variables */
		wp_localize_script( 'qe-signup', 'qe_signup', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
      'success_message' => __( 'User successfully created. Follow instructions in email.', 'qe-status'),
      'error_message' => __( 'Ooops. Something went wrong. Try again later.', 'qe-status'),
      'processing_message' => __( 'Processing your request.', 'qe-status')
		));

  }

  /**
   * Return the form content, includinc nonce
   */
  public function registration_form(){
    ob_start();
    ?>
    <div class="quick-email-signup">
      <form role="form">
        <label for="qes_email">Email</label>
        <input type="email" name="qes_email" id="qes_email" value="" placeholder="Email" />
        <?php wp_nonce_field('qes_new_user','qes_new_user_nonce', true, true ); ?>
        <input type="submit" id="btn-qes-submit" value="Register" />
      </form>
      <div class="qes-status">.</div>
    </div>
    <?php
    $form = ob_get_contents();
    ob_end_clean();
    return $form;
  }

  /**
   * Function responsible for handling the ajax request to add user.
   *
   * Only accepts an email and nonce as input. Echos USER_CREATED upon success.
   */
  public function add_user() {

    // Verify nonce
    if( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'qes_new_user' ) )
      wp_die( 'Ooops, something went wrong, please try again later.' );

    // Verify email exists
    if( !isset( $_POST['email'] ) )
      wp_die( 'Please provide an email address.' );

    $email = sanitize_text_field( $_POST[ 'email' ] );

    // Verify email address
    if ( !is_email( $email ) )
      wp_die( 'The email adress is not valid.' );

    // Check if exists allready
    if ( username_exists( $email ) || email_exists( $email ) )
      wp_die( 'User allready exists. Please provide a separate email or try to log in.');

    // At this point we can safely just create the user https://tommcfarlin.com/create-a-user-in-wordpress/
    $password = wp_generate_password( 12, true );
    $user_id = wp_create_user( $email, $password, $email );

    // User created
    $user = new WP_User( $user_id );
    $role = apply_filters( 'qe_signup_default_role', 'subscriber', $email, $user_id );
    $nickname = apply_filters( 'qe_signup_nickname', strstr( $email, '@', true), $email, $user_id );

    // Set nick and role
    wp_update_user(
      array(
        'ID' => $user_id,
        'nickname' =>  $nickname// first part of email only
      )
    );
    $user->set_role( $role );

    // Do whatever you want to the newly created user by accessing this action.
    do_action('qe_signup_user_created', $email, $password, $user_id );

    echo "USER_CREATED";

    wp_die(); // allways die
  }

}



/**
 * Execution of the plugin.
 *
 * Can also be used to get the plugin singleton
 *
 * @since    0.1.0
 */
function quick_email_signup() {
	return Quick_Email_Signup::instance();
}
quick_email_signup();

?>
