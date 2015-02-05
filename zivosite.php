<?php

/**
 * JivoSite Live Chat module for Prestashop
 *
 * @link http://prestashop.modulez.ru/en/ Modules for Prestashop CMS
 * @author zapalm <zapalm@ya.ru>
 * @copyright (c) 2014-2015, zapalm
 * @link http://prestashop.modulez.ru/en/free-products/27-jivosite-live-chat.html The module's homepage
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;

class Zivosite extends Module
{
	public function __construct()
	{
		$this->name = 'zivosite';
		$this->tab = 'front_office_features';
		$this->version = '0.9';
		$this->author = 'zapalm';
		$this->need_instance = 0;
		$this->bootstrap = true;
		$this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => '1.6.1.0');

		parent::__construct();

		$this->displayName = $this->l('JivoSite Live Chat');
		$this->description = $this->l('Allow to add JivoSite Live Chat.');
	}

	public function install()
	{
		return parent::install() && $this->registerHook('footer');
	}

	public function uninstall()
	{
		return parent::uninstall();
	}

	/*
	 * generates GUID
	 *
	 * @link https://php.net/com_create_guid#99425
	 *
	 * return string
	 */
	private static function generateGUID()
	{
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

	public function getContent()
	{
		$output = '';
		$submit_save = !empty($_POST['submit_save']); // Tools::isSubmit() method is unusable for PS1.5 when 'form helper' is using at this time

		if ($submit_save)
		{
			if (Tools::getValue('JIVOSITE_WIDGET_ID_EXIST') && Tools::getValue('JIVOSITE_WIDGET_ID'))
				Configuration::updateValue('JIVOSITE_WIDGET_ID', Tools::getValue('JIVOSITE_WIDGET_ID'));
			elseif (!Tools::getValue('JIVOSITE_WIDGET_ID_EXIST'))
			{
				$signin_params = array(
					'email' => Tools::getValue('JIVOSITE_USER_EMAIL'),
					'partnerId' => 'prestashop',
					'userDisplayName' => Tools::getValue('JIVOSITE_USER_NAME'),
					'siteUrl' => Tools::getValue('JIVOSITE_WIDGET_DOMAIN'),
					'authToken' => self::generateGUID(),
					'agent_id' => '4086',
					'userPassword' => Tools::getValue('JIVOSITE_USER_PASSWD'),
				);

				$validated = true;
				foreach ($signin_params as $param)
					if (empty($param))
						$validated = false;

				if (!$validated)
					$output .= $this->displayError($this->l('Please, fill out required fields'));
				else
				{
					$post_data = http_build_query($signin_params);

					$opts = array('http' =>
						array(
							'method' => 'POST',
							'header' => 'Content-type: application/x-www-form-urlencoded',
							'content' => $post_data
						)
					);

					$context = stream_context_create($opts);

					$post_result = file_get_contents('http://admin.jivosite.com/integration/install', false, $context);
					if (strncmp($post_result, 'Error', 5) == 0)
					{
						$post_result = str_replace('Error: ', '', $post_result);
						$output .= $this->displayError($post_result);
					}
					elseif (strlen($post_result))
					{
						Configuration::updateValue('JIVOSITE_WIDGET_ID', $post_result);
						Configuration::updateValue('JIVOSITE_AUTH_TOKEN', $signin_params['authToken']);

						$output .= $this->displayConfirmation('The account successfully created');
					}
				}
			}
		}

		return $output.$this->displayForm();
	}

	protected function displayForm()
	{
		$widget_id_exists = Configuration::get('JIVOSITE_WIDGET_ID') ? 1 : 0;
		$fields_form = array();

		$fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Configuration'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'radio',
						'label' => $this->l('Are you already have JivoSite Widget ID?'),
						'name' => 'JIVOSITE_WIDGET_ID_EXIST',
						'is_bool' => true,
						'required' => true,
						'desc' => $this->l('Choose to continue the configuration.'),
						'class' => 't',
						'values' => array(
							array(
								'id' => 'widget_id_existence_on',
								'value' => $widget_id_exists,
								'label' => $this->l('Yes'),
							),
							array(
								'id' => 'widget_id_existence_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
				),
			),
		);

		$fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Setting JivoSite Widget ID or Log-In to JivoSite'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Your JivoSite Widget ID'),
						'name' => 'JIVOSITE_WIDGET_ID',
						'required' => false,
						'desc' => $this->l('Copy your Widget ID from your JivoSite Code and insert here.'),
					),
					array(
						'type' => 'free',
						'label' => $this->l('JivoSite admin URL'),
						'name' => 'JIVOSITE_LOGIN',
						'required' => false,
						'desc' => $this->l('Log-In to JivoSite admin panel.'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'button'
				)
			),
		);

		$fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Create new JivoSite account to get Widget ID'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('E-mail'),
						'name' => 'JIVOSITE_USER_EMAIL',
						'required' => true,
						'desc' => $this->l('Your E-mail that will be used to log-in to JivoSite.').' '.$this->l('Change if need and remember it please.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Password'),
						'name' => 'JIVOSITE_USER_PASSWD',
						'required' => true,
						'desc' => $this->l('Your password that will be used to log-in to JivoSite.').' '.$this->l('Change if need and remember it please.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Manager name'),
						'name' => 'JIVOSITE_USER_NAME',
						'required' => true,
						'desc' => $this->l('This name will be dislpayed in the chat.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Shop domain'),
						'name' => 'JIVOSITE_WIDGET_DOMAIN',
						'required' => true,
						'desc' => $this->l('A domain on witch the widget will work.'),
					),
				),
				'submit' => array(
					'title' => $this->l('Create'),
					'class' => 'button'
				)
			),
		);

		$form = new HelperForm();
		$form->token = Tools::getAdminTokenLite('AdminModules');
		$form->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$form->show_toolbar = false;
		$form->submit_action = 'submit_save';
		$form->fields_value['JIVOSITE_LOGIN'] = '<a target="_blank" class="" href="https://www.jivochat.com?pid=4086">www.jivochat.com</a>';
		$form->fields_value['JIVOSITE_WIDGET_ID'] = Configuration::get('JIVOSITE_WIDGET_ID');
		$form->fields_value['JIVOSITE_WIDGET_ID_EXIST'] = $widget_id_exists;
		$form->fields_value['JIVOSITE_USER_EMAIL'] = Tools::getValue('JIVOSITE_USER_EMAIL') ? Tools::getValue('JIVOSITE_USER_EMAIL') : Configuration::get('PS_SHOP_EMAIL');
		$form->fields_value['JIVOSITE_WIDGET_DOMAIN'] = Tools::getValue('JIVOSITE_WIDGET_DOMAIN') ? Tools::getValue('JIVOSITE_WIDGET_DOMAIN') : Tools::getShopDomain(true);
		$form->fields_value['JIVOSITE_USER_PASSWD'] = Tools::getValue('JIVOSITE_USER_PASSWD') ? Tools::getValue('JIVOSITE_USER_PASSWD') : Tools::passwdGen();
		$form->fields_value['JIVOSITE_USER_NAME'] = Tools::getValue('JIVOSITE_USER_NAME') ? Tools::getValue('JIVOSITE_USER_NAME') : $this->context->employee->firstname.' '.$this->context->employee->lastname;

		$this->context->controller->addJS($this->_path.'js/admin_zivosite.js');

		return $form->generateForm($fields_form);
	}

	public function hookFooter($params)
	{
		$widget_id = Configuration::get('JIVOSITE_WIDGET_ID');

		if (!$widget_id)
			return null;

		$this->context->smarty->assign(array(
			'JIVOSITE_WIDGET_ID' => $widget_id
		));

		return $this->display(__FILE__, 'zivosite.tpl');
	}
}