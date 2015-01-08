<?php

/**
 * JivoSite Live Chat module for Prestashop
 *
 * @link http://prestashop.modulez.ru/en/ Modules for Prestashop CMS
 * @author zapalm <zapalm@ya.ru>
 * @copyright (c) 2014, zapalm
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
		$this->version = '0.2';
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

	public function getContent()
	{
		$output = '';
		$submit = !empty($_POST['submit_save']);	// Tools::isSubmit() method is unusable for PS1.5 when form helper is using

		if ($submit && !Tools::isEmpty($widget_id = Tools::getValue('JIVOSITE_WIDGET_ID')))
		{
			if (Configuration::updateValue('JIVOSITE_WIDGET_ID', $widget_id))
				$output .= $this->displayConfirmation($this->l('Successfull update'));
			else
				$output .= $this->displayError($this->l('Unsuccessfull update'));
		}

		return $output.$this->displayForm();
	}

	protected function displayForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Configuration'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Your JivoSite Widget ID'),
						'name' => 'JIVOSITE_WIDGET_ID',
						'required' => true,
						'desc' => $this->l('Copy your Widget ID from your JivoSite Code and insert here.'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'button'
				)
			),
		);

		$form = new HelperForm();
		$form->token = Tools::getAdminTokenLite('AdminModules');
		$form->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$form->show_toolbar = false;
		$form->submit_action = 'submit_save';
		$form->fields_value['JIVOSITE_WIDGET_ID'] = Configuration::get('JIVOSITE_WIDGET_ID');

		return $form->generateForm(array($fields_form));
	}

	public function hookFooter($params)
	{
		$this->context->smarty->assign(array(
			'JIVOSITE_WIDGET_ID' => Configuration::get('JIVOSITE_WIDGET_ID')
		));

		return $this->display(__FILE__, 'zivosite.tpl');
	}
}