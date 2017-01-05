<?php
/**
 * 2017 Thirty Bees
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 *  @author    Thirty Bees <modules@thirtybees.com>
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2017 Thirty Bees
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/../../paypal.php';

class PayPalHostedsolutionsubmitModuleFrontController extends ModuleFrontController
{
    public $context;

    /**
     * PayPalIntegralEvolutionSubmit constructor.
     *
     * @author    PrestaShop SA <contact@prestashop.com>
     * @copyright 2007-2016 PrestaShop SA
     * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        parent::__construct();
    }

    public function initContent()
    {
        $id_cart = Tools::getValue('id_cart');
        $id_module = Tools::getValue('id_module');
        $id_order = Tools::getValue('id_order');
        $key = Tools::getValue('key');

        if ($id_module && $id_order && $id_cart && $key) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $integral_evolution_submit = new PayPalIntegralEvolutionSubmit();
                $integral_evolution_submit->run();
            }
        } elseif ($id_cart) {
            // Redirection
            $values = array(
                'id_cart' => (int) $id_cart,
                'id_module' => (int) Module::getInstanceByName('paypal')->id,
                'id_order' => (int) Order::getOrderByCartId((int) $id_cart),
            );

            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $customer = new Customer(Context::getContext()->cookie->id_customer);
                $values['key'] = $customer->secure_key;
                $url = _MODULE_DIR_.'/paypal/integral_evolution/submit.php';
                Tools::redirectLink($url.'?'.http_build_query($values, '', '&'));
            } else {
                $values['key'] = Context::getContext()->customer->secure_key;
                $link = Context::getContext()->link->getModuleLink('paypal', 'submit', $values);
                Tools::redirect($link);
            }
        } else {
            Tools::redirectLink(__PS_BASE_URI__);
        }

        exit(0);
    }

    /**
     * Display PayPal order confirmation page
     *
     * @author    PrestaShop SA <contact@prestashop.com>
     * @copyright 2007-2016 PrestaShop SA
     * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
     */
    public function displayContent()
    {
        $idOrder = (int) Tools::getValue('id_order');
        $order = PayPalOrder::getOrderById($idOrder);
        $price = Tools::displayPrice($order['total_paid'], $this->context->currency);

        $this->context->smarty->assign(array(
            'order' => $order,
            'price' => $price,
        ));

        $this->context->smarty->assign(array(
            'reference_order' => Order::getUniqReferenceOf($idOrder),
        ));

        echo $this->context->smarty->fetch(_PS_MODULE_DIR_.'paypal/views/templates/front/order-confirmation.tpl');
    }
}
