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
		$this->version = '0.1';
		$this->author = 'zapalm';
		$this->need_instance = 0;

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

	public function hookFooter($params)
	{
		global $smarty;

		$smarty->assign(array(
			'widget_id' => '0',	// insert your widget id
		));

		return $this->display(__FILE__, 'zivosite.tpl');
	}
}