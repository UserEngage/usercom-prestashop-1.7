<?php

/**
 * File from http://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @authors     PrestaShow.pl <kontakt@prestashow.pl>
 * @copyright   2018 PrestaShow.pl
 * @license     https://prestashow.pl/license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . "/config.php";

class UserEngage extends PShowModule
{

    /**
     * These hooks are registered during module installation
     *
     * @var array
     */
    public $hooks = [
        'displayHeader',
        'displayOrderConfirmation',
        'displayOrderDetail',
    ];

    /**
     * Module controller with tab in admin menu
     *
     * @var string
     */
    public $admin_menu_tab = 'UserEngageMain';

    /**
     * Module controllers without tab in admin menu
     *
     * @var array
     */
    public $controllers = array(
        'UserEngageBackup',
        'UserEngageUpdate',
    );

    /**
     * Details about confirmed order.
     * Filled on order confirmation page.
     *
     * @var array
     */
    private $purchaseDetails = [];

    /**
     * List of products from order.
     *
     * @var array
     */
    private $orderProducts = [];

    /**
     *  Primary configuration
     */
    public $name = 'userengage';
    public $tab = 'other';
    public $version = '1.2.0';
    public $js_version = '2';
    public $ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->displayName = $this->l('UserEngage');
        $this->description = $this->l(
            'User.com is a single platform providing you with easy-to-use, '
            . 'yet very robust tools for marketing, sales and support departments.'
        );
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        try {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'customer` ADD `sent_to_user_com` BOOLEAN NOT NULL DEFAULT FALSE';
            Db::getInstance()->execute($sql);
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'customer` SET `sent_to_user_com` = 1 WHERE `id_customer` > 0';
            Db::getInstance()->execute($sql);
        } catch (PrestaShopDatabaseException $e) {

        }
    }

    /**
     * Execute before html output
     *
     * @param string $html
     * @throws SmartyException
     */
    public function hookSmartyOutputContent(&$html)
    {
        $address_delivery = new Address($this->context->cart->id_address_delivery);

        $this->context->smarty->assign([
            'userengage_version' => $this->js_version,
            'userengage_apikey' => Configuration::get('userengage_apikey'),
            'userengage_debug' => Configuration::get('userengage_debug'),
            'userengage_server' => Configuration::get('userengage_server'),
            'userengage_is_logged' => $this->context->customer->isLogged(),
            'userengage_customer' => $this->context->customer,
            'userengage_address_delivery' => $address_delivery,
        ]);

        if (isset($this->purchaseDetails['customer'])) {
            $this->context->smarty->assign([
                'userengage_customer' => $this->purchaseDetails['customer'],
            ]);
            unset($this->purchaseDetails['customer']);
        }

        if (isset($this->purchaseDetails['address_delivery'])) {
            $this->context->smarty->assign([
                'userengage_address_delivery' => $this->purchaseDetails['address_delivery'],
            ]);
            unset($this->purchaseDetails['address_delivery']);
        }

        if ($this->context->customer->isLogged()) {
            $sql = 'SELECT `sent_to_user_com` FROM `' . _DB_PREFIX_ . 'customer` WHERE `id_customer` = %d';
            $sent_to_user_com = (int)Db::getInstance()->getValue(sprintf($sql, (int)$this->context->customer->id));
            if (!$sent_to_user_com) {
                $this->context->smarty->assign([
                    'userengage_account_creation' => [
                        'email' => $this->context->customer->email,
                        'name' => $this->context->customer->firstname,
                        'surname' => $this->context->customer->lastname,
                    ],
                ]);
                if ($this->context->customer->newsletter) {
                    $this->context->smarty->assign([
                        'userengage_newsletter_signup' => $this->context->customer->email,
                    ]);
                }

                $sql = 'UPDATE `' . _DB_PREFIX_ . 'customer` SET `sent_to_user_com` = 1 WHERE `id_customer` = %d';
                Db::getInstance()->execute(sprintf($sql, (int)$this->context->customer->id));
            }
        }

        $this->context->smarty->assign([
            'userengage_purchase' => $this->purchaseDetails,
            'userengage_order_products' => $this->orderProducts,
        ]);

        $html = str_replace('</body>', $this->context->smarty->fetch(
                'modules/userengage/views/templates/hook/smartyOutputContent.tpl'
            ) . '</body>', $html);
    }

    /**
     * Execute on hook: displayOrderConfirmation
     *
     * @param array $params
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'];
        $currency = new Currency(
            (int)$order->id_currency, (int)Context::getContext()->language->id
        );
        $customer = $order->getCustomer();

        $this->purchaseDetails = [
            'order_number' => (int)$order->id,
            'revenue' => (float)$order->getTotalProductsWithoutTaxes(),
            'tax' => (float)(
                $order->getTotalProductsWithTaxes() -
                $order->getTotalProductsWithoutTaxes()
            ),
            'shipping' => (float)$order->total_shipping,
            'currency' => $currency->getSign(),
            'registered_user' => (int)$customer->is_guest,
            'payment_method' => $order->payment,
            'address_delivery' => new Address($order->id_address_delivery),
            'products' => [],
        ];

        // append coupons
        $coupons = [];
        array_map(function ($cartRule) use ($coupons) {
            $coupons[] = $cartRule['name'];
        }, $order->getCartRules());
        $this->purchaseDetails['coupon'] = implode(',', $coupons);

        // append products
        $products = $order->getCartProducts();
        foreach ($products as $product) {

            $category = new Category($product['id_category_default']);
            $categoryName = str_replace(' ', '-', strtolower($category->getName()));

            // remove attributes from name
            if ($product['product_attribute_id']) {
                $product['product_name'] = trim(preg_replace(
                    '/(.*)\-.*/', '$1', $product['product_name']
                ));
            }

            $this->purchaseDetails['products'][] = [
                'product_id' => $product['product_id'] . '-' . $product['product_attribute_id'],
                'quantity' => (int)$product['cart_quantity'],
                'name' => $product['product_name'],
                'price' => $product['product_price'],
                'brand' => (Manufacturer::getNameById($product['id_manufacturer'])) ?: '',
                'category_name' => $categoryName,
            ];
        }

        if ($customer->is_guest) {
            $this->purchaseDetails['customer'] = $customer;
        }
    }

    /**
     * Execute on hook: displayOrderDetail
     *
     * @param array $params
     */
    public function hookDisplayOrderDetail($params)
    {
        $order = $params['order'];

        // find products
        $products = $order->getCartProducts();
        foreach ($products as $product) {

            $category = new Category($product['id_category_default']);
            $categoryName = str_replace(' ', '-', strtolower($category->getName()));

            // remove attributes from name
            if ($product['product_attribute_id']) {
                $product['product_name'] = trim(preg_replace(
                    '/(.*)\-.*/', '$1', $product['product_name']
                ));
            }

            $this->orderProducts[$product['id_order_detail']] = [
                'product_id' => $product['product_id'] . '-' . $product['product_attribute_id'],
                'quantity' => (int)$product['cart_quantity'],
                'name' => $product['product_name'],
                'price' => $product['product_price'],
                'brand' => Manufacturer::getNameById($product['id_manufacturer']),
                'category_name' => $categoryName,
                'order_number' => $order->id,
            ];
        }
    }
}
