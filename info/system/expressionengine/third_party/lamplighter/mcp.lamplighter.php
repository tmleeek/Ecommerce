<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Lamplighter Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link
 */
class Lamplighter_mcp
{

	public $return_data;

	private $_base_url;

	/**
	 * The boolean representation of whether or not EE3 is installed.
	 * @var boolean
	 */
	private $ee3 = false;

	/**
	 * The URL that saves a Lamplighter key to the DB.
	 * @var string
	 */
	public $save_key_url = null;

	/**
	 * The URL that removes a Lamplighter key from the DB.
	 * @var string
	 */
	public $remove_key_url = null;

	/**
	 * The URL used to send add-on data to Lamplighter.
	 * @var string
	 */
	public $refresh_url = null;

	/**
	 * The URL used to refresh the available update data in the add-ons list.
	 * @var string
	 */
	 public $update_url = null;

	/**
	 * The URL used to hide an add-on from the list view for a user.
	 * @var string
	*/
	public $hide_addon_url = null;

	/**
	 * The URL used to unhide an add-on from the list view for a user.
	 * @var string
	 */
	public $unhide_addon_url = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path( PATH_THIRD.'lamplighter/' );
		$this->EE->load->library('lamplighter_library');
		$this->EE->load->library('devotee_library');
		// Set the Lamplighter themes URL
		$this->theme_url = defined('URL_THIRD_THEMES')
			? URL_THIRD_THEMES . '/lamplighter/'
			: $this->EE->config->item('theme_folder_url') . 'third_party/lamplighter/';
		// Set the EE3 boolean value based on the currently installed version of EE.
		$this->ee3 = $this->EE->lamplighter_library->ee3();
		// Set various module URLs based on the EE version
		$this->_base_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter') : BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=lamplighter';
		$this->_token_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/site_token')  : $this->_base_url.AMP.'method=site_token';
		$this->hide_addon_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/hide_addon')  : $this->_base_url.AMP.'method=hide_addon';
		$this->unhide_addon_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/unhide_addon')  : $this->_base_url.AMP.'method=unhide_addon';
		$this->save_key_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/save_key')  : $this->_base_url.AMP.'method=save_key';
		$this->remove_key_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/remove_key')  : $this->_base_url.AMP.'method=remove_key';
		$this->sync_data_url = $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/refresh&api=addons') : $this->_base_url.'&method=refresh&api=addons';
		$this->update_url =  $this->ee3 ? ee('CP/URL', 'addons/settings/lamplighter/check_for_updates')  : $this->_base_url.AMP.'method=check_for_updates';
		// Set the module navigation based on API key status and EE version
		if ( !empty($this->EE->lamplighter_library->api_key) ) {
			if ( $this->ee3 ) {
				$sidebar = ee('CP/Sidebar')->make();
				$sidebar->addHeader(lang('module_home'), $this->_base_url);
				$sidebar->addHeader(lang('token'), $this->_token_url);
				$sidebar->addHeader(lang('send_data'), $this->sync_data_url);
			} else {
				$this->EE->cp->set_right_nav(array(
					'module_home'	=> $this->_base_url,
					'send_data' => $this->sync_data_url,
				));
			}
		} else {
			if ( $this->ee3 ) {
				$sidebar = ee('CP/Sidebar')->make();
				$sidebar->addHeader(lang('module_home'), $this->_base_url);
				$sidebar->addHeader(lang('token'), $this->_token_url);
			} else {
				$this->EE->cp->set_right_nav(array(
					'module_home'	=> $this->_base_url,
				));
			}
		}
	}

	// ----------------------------------------------------------------

	public function index()
	{
		// For EE2, the accessory covers this functionality. Redirect to the token page.
		if ( ! $this->ee3 ) {
			$this->EE->functions->redirect($this->_token_url);
		}
		// For EE3, show the add-on updates table similar to the one from the EE2 accessory.
		$this->EE->view->cp_page_title = lang('lamplighter_module_name');
		$view_data = array(
			'updates' => $this->EE->devotee_library->get_addons(false, false),
			'last_check' => $this->EE->devotee_library->last_check_date(),
			'hidden_addons' => $this->EE->devotee_library->fetch_hidden_addons(),
			'cp' => $this->EE->cp,
			'base_url' => $this->_base_url,
			'update_url' => $this->update_url,
			'hide_addon_url' => $this->hide_addon_url,
			'unhide_addon_url' => $this->unhide_addon_url,
			'show_hidden' => $this->EE->input->get('show_hidden', true) == 'y' ? true : false
		);
		$this->EE->cp->add_to_foot('<link rel="stylesheet" href="' . $this->theme_url . 'styles/cp.css?v=' . LAMPLIGHTER_VERSION . '" />');
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->theme_url . 'scripts/cp.js?v=' . LAMPLIGHTER_VERSION . '"></script>');
		return $this->view('mcp_index', $view_data, $this->_base_url, lang('lamplighter_module_name'));
	}

	// ----------------------------------------------------------------

	/**
	 * The Module's control panel homepage.
	 * @return string
	 */
	public function site_token()
	{
		$this->EE->view->cp_page_title = lang('token');
		$view_data = array(
			'api_key' => $this->EE->lamplighter_library->getSiteToken(),
			'url_save_key' => $this->save_key_url,
			'url_remove_key' => $this->remove_key_url,
			'curl_enabled' => $this->is_curl_enabled(),
			'cp' => $this->EE->cp
		);

		return $this->view('mcp_token', $view_data, $this->_token_url, lang('token'));
	}

	// ----------------------------------------------------------------

	/**
	 * This method sends a request to a specified LL endpoint.
	 */
	public function refresh()
	{
		$api_endpoint = $this->EE->input->get('api', true);
		$response = $this->EE->lamplighter_library->api_request($api_endpoint);
		if ( $response['status'] == 'success' ) {
			$this->success_alert($response['message']);
		} else {
			$this->EE->lamplighter_library->purge_token_data();
			$this->error_alert($response['message']);
		}
		return $this->EE->functions->redirect($this->_base_url);
	}

	// ----------------------------------------------------------------

	/**
	 * This method stores the site token data in the DB and registers the
	 * action ID with the Lamplighter app.
	 */
	public function save_key()
	{
		$site_token = $this->EE->input->post('api_key');
		$response = $this->EE->lamplighter_library->store_token_data($site_token);
		if ( $response['status'] == 'success' ) {
			$this->success_alert($response['message']);
		} else {
			$this->EE->lamplighter_library->purge_token_data();
			$this->error_alert($response['message']);
		}
		return $this->EE->functions->redirect($this->_base_url);
	}

	// ----------------------------------------------------------------

	/**
	 * This method removes the site token data from the DB and unregisters the
	 * action ID with the Lamplighter app.
	 */
	public function remove_key()
	{
		$response = $this->EE->lamplighter_library->unregister_action_id();
		if ( isset($response->status) && $response->status == 'success' ) {
			$this->success_alert($response->message);
		} else {
			$this->error_alert($response->message);
		}
		return $this->EE->functions->redirect($this->_base_url);
	}

	// ----------------------------------------------------------------

	/**
	 * This method adds an add-on to the list of add-ons that are NOT to be
	 * displayed in the add-ons list and redirects the user back to the main
	 * control panel page.
	 */
	public function hide_addon()
	{
		// Add setting to database
		$this->EE->devotee_library->hide_addon($this->EE->input->get('package'),$this->EE->session->userdata('member_id'));
		// Refresh view
		return $this->EE->functions->redirect($this->_base_url);
	}

	// ----------------------------------------------------------------

	/**
	 * This mmethod removes an add-on from the hidden add-ons list for a
	 * particular user then refreshes the page.
	 */
	public function unhide_addon()
	{
		// Fetch the add-on package name from the URL
		$package_name = $this->EE->input->get('package');
		// Delete hidden add-on setting for user
		$this->EE->devotee_library->unhide_addon($package_name, $this->EE->session->userdata('member_id'));
		return $this->EE->functions->redirect($this->_base_url);
	}

	// ----------------------------------------------------------------

	/**
	 * This method destroys the Lamplighter add-on's cache and checks devot:ee
	 * for add-on update information. The user is then redirected back to the
	 * add-on's CP homepage.
	 */
	public function check_for_updates()
	{
		$this->EE->devotee_library->clear_cache();
		return $this->EE->functions->redirect($this->_base_url);
	}

	// ----------------------------------------------------------------

	/**
	 * This method determines if curl is enabled on the server.
	 * @return boolean
	 */
	protected function is_curl_enabled()
	{
		return function_exists('curl_version');
	}

	// ----------------------------------------------------------------

	/**
	 * This method queues a success alert message to be displayed on the next page
	 * load. The method used to do this depends on the version of EE installed.
	 * @param string $message
	 */
	protected function success_alert($message='')
	{
		if ( $this->ee3 ) {
			ee('CP/Alert')->makeBanner('Success')->addToBody($message)->asSuccess()->defer();
		} else {
			$this->EE->session->set_flashdata('message_success', $message);
		}
	}

	// ----------------------------------------------------------------

	/**
	 * This method queues an error alert message to be displayed on the next page
	 * load. The method used to do this depends on the version of EE installed.
	 * @param string $message
	 */
	protected function error_alert($message='')
	{
		if ( $this->ee3 ) {
			ee('CP/Alert')->makeBanner('Error')->addToBody($message)->asIssue()->defer();
		} else {
			$this->EE->session->set_flashdata('message_error', $message);
		}
	}

	// ----------------------------------------------------------------

	/**
	 * This method loads one of the add-ons CP views. The process for handling
	 * this varies based on the version of EE installed.
	 * @param string $view
	 * @param array $data
	 * @param string $url
	 * @param string $breadcrumb_lang
	 * @return string
	 */
	protected function view($view='index', $data=array(), $url='', $breadcrumb_lang='')
	{
		if ( $this->ee3 ) {
			$content = array(
				'body'       => ee('View')->make('lamplighter:'.$view)->render($data),
				'breadcrumb' => array(
					$this->_base_url->compile() => lang('module_home')
				),
				'heading'  => $breadcrumb_lang,
			);
		} else {
			$content = $this->EE->load->view($view, $data, true);
		}
		return $content;
	}

}
