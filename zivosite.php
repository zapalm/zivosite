<?php
/**
 * JivoChat/JivoSite Live Chat: module for Prestashop 1.5-1.7
 *
 * @author    zapalm <zapalm@ya.ru>
 * @copyright (c) 2014, zapalm
 * @link      http://prestashop.modulez.ru/en/free-products/27-jivosite-live-chat.html The module's homepage
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Zivosite extends Module
{
    const CONF_USER_EMAIL          = 'JIVOSITE_USER_EMAIL';
    const CONF_USER_PASSWORD       = 'JIVOSITE_USER_PASSWD';
    const CONF_USER_NAME           = 'JIVOSITE_USER_NAME';
    const CONF_WIDGET_DOMAIN       = 'JIVOSITE_WIDGET_DOMAIN';
    const CONF_WIDGET_ID           = 'JIVOSITE_WIDGET_ID';
    const CONF_AUTH_TOKEN          = 'JIVOSITE_AUTH_TOKEN';
    const FORM_KEY_LOGIN_URL       = 'JIVOSITE_LOGIN';
    const FROM_KEY_WIDGET_ID_EXIST = 'JIVOSITE_WIDGET_ID_EXIST';

    private $template = 'zivosite.tpl';

    public function __construct()
    {
        $this->name          = 'zivosite';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'zapalm';
        $this->need_instance = 0;
        $this->bootstrap     = true;

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
     * Generates GUID.
     *
     * @link https://php.net/com_create_guid#99425
     *
     * return string
     */
    private static function generateGuid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535),
            mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * Get good password that include's at list one number.
     *
     * @return string
     *
     * @throws PrestaShopException
     */
    private function generatePassword()
    {
        do {
            $password = Tools::passwdGen();

            $containsNumbers = preg_match('/[0-9]/', $password);
            if (false === $containsNumbers) {
                throw new PrestaShopException('The function preg_match is failed.');
            }
        } while (0 === $containsNumbers);

        return $password;
    }

    public function getContent()
    {
        $output      = '';
        $submit_save = !empty($_POST['submit_save']); // Tools::isSubmit() method is unusable for PS1.5 when 'form helper' is using at this time
        $isoCode     = Language::getIsoById((int)$this->context->cookie->id_lang);

        if ($submit_save) {
            if (Tools::getValue(self::FROM_KEY_WIDGET_ID_EXIST) && Tools::getValue(self::CONF_WIDGET_ID)) {
                Configuration::updateValue(self::CONF_WIDGET_ID, Tools::getValue(self::CONF_WIDGET_ID));
            } elseif (!Tools::getValue(self::FROM_KEY_WIDGET_ID_EXIST)) {
                $signin_params = array(
                    'email'           => Tools::getValue(self::CONF_USER_EMAIL),
                    'partnerId'       => 'prestashop',
                    'userDisplayName' => Tools::getValue(self::CONF_USER_NAME),
                    'siteUrl'         => Tools::getValue(self::CONF_WIDGET_DOMAIN),
                    'authToken'       => self::generateGuid(),
                    'agent_id'        => ($isoCode === 'ru' ? '7280' : '4086'),
                    'userPassword'    => Tools::getValue(self::CONF_USER_PASSWORD),
                );

                $validated = true;
                foreach ($signin_params as $param) {
                    if (empty($param)) {
                        $validated = false;
                    }
                }

                if (!$validated) {
                    $output .= $this->displayError($this->l('Please, fill out required fields'));
                } else {
                    $post_data = http_build_query($signin_params);

                    $opts = array(
                        'http' =>
                            array(
                                'method'  => 'POST',
                                'header'  => 'Content-type: application/x-www-form-urlencoded',
                                'content' => $post_data
                            )
                    );

                    $context    = stream_context_create($opts);
                    $postUrl    = 'http://admin.jivosite.com/integration/install/?lang=' . ('ru' === $isoCode ? 'ru' : 'en');
                    $postResult = file_get_contents($postUrl, false, $context);
                    if (strncmp($postResult, 'Error', 5) === 0) {
                        $postResult = str_replace('Error: ', '', $postResult);
                        $output     .= $this->displayError('JivoChat: ' . $postResult);
                    } elseif (strlen($postResult)) {
                        Configuration::updateValue(self::CONF_WIDGET_ID, $postResult);
                        Configuration::updateValue(self::CONF_AUTH_TOKEN, $signin_params['authToken']);

                        $output .= $this->displayConfirmation('The account successfully created');
                    }
                }
            }
        }

        return $output . $this->displayForm();
    }

    protected function displayForm()
    {
        $iso_code         = Language::getIsoById((int)$this->context->cookie->id_lang);
        $widget_id_exists = Configuration::get(self::CONF_WIDGET_ID) ? 1 : 0;
        $fields_form      = array();

        $fields_form[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration'),
                    'icon'  => 'icon-cogs'
                ),
                'input'  => array(
                    array(
                        'type'     => 'radio',
                        'label'    => $this->l('Are you already have JivoSite Widget ID?'),
                        'name'     => self::FROM_KEY_WIDGET_ID_EXIST,
                        'is_bool'  => true,
                        'required' => true,
                        'desc'     => $this->l('Choose to continue the configuration.'),
                        'class'    => 't',
                        'values'   => array(
                            array(
                                'id'    => 'widget_id_existence_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id'    => 'widget_id_existence_off',
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
                    'icon'  => 'icon-cogs'
                ),
                'input'  => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Your JivoSite Widget ID'),
                        'name'     => self::CONF_WIDGET_ID,
                        'required' => false,
                        'desc'     => $this->l('Copy your Widget ID from your JivoSite Code and insert here.'),
                    ),
                    array(
                        'type'     => 'free',
                        'label'    => $this->l('JivoSite admin URL'),
                        'name'     => self::FORM_KEY_LOGIN_URL,
                        'required' => false,
                        'desc'     => $this->l('Log-In to JivoSite admin panel.'),
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
                    'icon'  => 'icon-cogs'
                ),
                'input'  => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('E-mail'),
                        'name'     => self::CONF_USER_EMAIL,
                        'required' => true,
                        'desc'     => $this->l('Your E-mail that will be used to log-in to JivoSite.') . ' ' . $this->l('Change if need and remember it please.'),
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Password'),
                        'name'     => self::CONF_USER_PASSWORD,
                        'required' => true,
                        'desc'     => $this->l('Your password that will be used to log-in to JivoSite.') . ' ' . $this->l('Change if need and remember it please.'),
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Manager name'),
                        'name'     => self::CONF_USER_NAME,
                        'required' => true,
                        'desc'     => $this->l('This name will be dislpayed in the chat.'),
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Shop domain'),
                        'name'     => self::CONF_WIDGET_DOMAIN,
                        'required' => true,
                        'desc'     => $this->l('A domain on witch the widget will work.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Create'),
                    'class' => 'button'
                )
            ),
        );

        $form = new HelperForm();

        $form->token         = Tools::getAdminTokenLite('AdminModules');
        $form->currentIndex  = AdminController::$currentIndex . '&configure=' . $this->name;
        $form->show_toolbar  = false;
        $form->submit_action = 'submit_save';

        $form->fields_value[self::FORM_KEY_LOGIN_URL]       = '<a target="_blank" href="' . ($iso_code === 'ru' ? 'http://www.jivosite.ru?partner_id=7280' : 'https://www.jivochat.com?partner_id=4086') . '">www.jivochat.com</a>';
        $form->fields_value[self::CONF_WIDGET_ID]           = Configuration::get(self::CONF_WIDGET_ID);
        $form->fields_value[self::FROM_KEY_WIDGET_ID_EXIST] = $widget_id_exists;
        $form->fields_value[self::CONF_USER_EMAIL]          = Tools::getValue(self::CONF_USER_EMAIL) ? Tools::getValue(self::CONF_USER_EMAIL) : Configuration::get('PS_SHOP_EMAIL');
        $form->fields_value[self::CONF_WIDGET_DOMAIN]       = Tools::getValue(self::CONF_WIDGET_DOMAIN) ? Tools::getValue(self::CONF_WIDGET_DOMAIN) : Tools::getShopDomain(true);
        $form->fields_value[self::CONF_USER_PASSWORD]       = Tools::getValue(self::CONF_USER_PASSWORD) ? Tools::getValue(self::CONF_USER_PASSWORD) : $this->generatePassword();
        $form->fields_value[self::CONF_USER_NAME]           = Tools::getValue(self::CONF_USER_NAME) ? Tools::getValue(self::CONF_USER_NAME) : $this->context->employee->firstname . ' ' . $this->context->employee->lastname;

        $this->context->controller->addJS($this->_path . 'js/admin_zivosite.js');

        return $form->generateForm($fields_form);
    }

    /**
     * @inheritdoc
     */
    public function hookFooter($params)
    {
        $widgetId = trim(Configuration::get(self::CONF_WIDGET_ID));
        if ('' === $widgetId) {
            return '';
        }

        $cacheId = $this->getCacheId($this->name . '|' . $widgetId);
        if (!$this->isCached($this->template, $cacheId)) {
            $this->context->smarty->assign(array(
                self::CONF_WIDGET_ID => $widgetId,
            ));
        }

        return $this->display(__FILE__, $this->template, $cacheId);
    }
}