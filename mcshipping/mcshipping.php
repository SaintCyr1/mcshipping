<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mcshipping extends CarrierModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'mcshipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'smahiley';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Monconfort Shipping');
        $this->description = $this->l('Gestion des frais de livraisons de Monconfort');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        require 'sql/install.php';
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (extension_loaded('curl') == false)
        {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $carriers = [
            [
                'name' => 'Livraison Standard',
                'description' => 'Livraison à domicile en 2 ou 3 jours'
            ],
            [
                'name' => 'Livraison Monconfort',
                'description' => 'Retrait de colis en point relais'
            ],
            [
                'name' => 'Livraison Express',
                'description' => 'Livraison de votre colis en 4h'
            ]
        ];
        for ($i=0; $i < 3; $i++) { 
            $carrier = $this->addCarrier($carriers[$i]['name'],$carriers[$i]['description']);
            $this->addZones($carrier);
            $this->addGroups($carrier);
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('updateCarrier');
    }

    public function uninstall()
    {
        require 'sql/uninstall.php';

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitMcshippingModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMcshippingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
   protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Config'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'type' => 'select',
                        'name' => 'MCSHIPPING_TYPE_ARTICLE',
                        'label' => $this->l('Type d\'article'),
                        'options' => array(
                            'query' => $options = array(
                                array(
                                    'id_option' => 'normal',
                                    'name' => 'Normal'
                                ),
                                array(
                                    'id_option' => 'meuble',
                                    'name' => 'Meuble'
                                )
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 6,
                        'type' => 'select',
                        'name' => 'MCSHIPPING_REGION',
                        'label' => $this->l('Region'),
                        'options' => array(
                            'query' => $options = array(
                                array(
                                    'id_option' => 'abidjan',
                                    'name' => 'Abidjan'
                                ),
                                array(
                                    'id_option' => 'grand_abidjan',
                                    'name' => 'Grand Abidjan'
                                ),
                                array(
                                    'id_option' => 'interieur',
                                    'name' => 'Interieur'
                                )
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 6,
                        'type' => 'select',
                        'name' => 'MCSHIPPING_MODE',
                        'label' => $this->l('Mode de livraison'),
                        'options' => array(
                            'query' => $options = array(
                                array(
                                    'id_option' => 'office',
                                    'name' => 'Point relais/Bureau'
                                ),
                                array(
                                    'id_option' => 'standard',
                                    'name' => 'Standard'
                                ),
                                array(
                                    'id_option' => 'express',
                                    'name' => 'Express'
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'MCSHIPPING_FORMAT_PETIT',
                        'label' => $this->l('Petit')
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'MCSHIPPING_FORMAT_MOYEN',
                        'label' => $this->l('Moyen')
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'MCSHIPPING_FORMAT_GRAND',
                        'label' => $this->l('Grand')
                    )
                ),
                'description' => "Saisir les prix selon le format de l'article dans les champs ci-dessous",
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'MCSHIPPING_TYPE_ARTICLE' => 'normal',
            'MCSHIPPING_REGION' => 'abidjan',
            'MCSHIPPING_MODE' => 'standard',
            'MCSHIPPING_FORMAT_PETIT' => 0,
            'MCSHIPPING_FORMAT_MOYEN' => 0,
            'MCSHIPPING_FORMAT_GRAND' => 0
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        //Récupérer les valeurs pour le traitement ici.
        /* MCSHIPPING_TYPE_ARTICLE
        MCSHIPPING_REGION
        MCSHIPPING_MODE
        MCSHIPPING_FORMAT_PETIT
        MCSHIPPING_FORMAT_MOYEN
        MCSHIPPING_FORMAT_GRAND */
        die('Youpi....! Nous effectuerons une mise à jour très bientôt. Veuillez revenir après');
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (Context::getContext()->customer->logged == true)
        {
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address = new Address($id_address_delivery);

            /**
             * Send the details through the API
             * Return the price sent by the API
             */
            return 10;
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    protected function addCarrier($carrier_name,$description)
    {
        $carrier = new Carrier();

        $carrier->name = $this->l($carrier_name,$description);
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->need_range = 0;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang)
            $carrier->delay[$lang['id_lang']] = $this->l($description);

        if ($carrier->add() == true)
        {
            @copy(dirname(__FILE__).'/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg');
            Configuration::updateValue('MYSHIPPINGMODULE_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group)
            if($group['name'] == "Customer"){
                $groups_ids[] = $group['id_group'];
            }
        $carrier->setGroups($groups_ids);
    }

    protected function addZones($carrier)
    {
        $zone = Zone::getIdByName("Africa");
        if($zone){
            $carrier->addZone($zone);
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
        */
    }
}