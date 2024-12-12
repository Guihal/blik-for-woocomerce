<?php
/*
 * Plugin Name: Blik for Woocomerce
 * Plugin URI: -
 * Description: Blik payment for woocomerce
 * Author: Guihal
 * Author URI: https://guihal.ru
 * Version: 1.0.0
 */

add_filter('woocommerce_payment_gateways', 'register_blik_woocomerce_gateway_class');

function register_blik_woocomerce_gateway_class($gateways)
{
    $gateways[] = 'WC_Gateway_blik_woocomerce';
    return $gateways;
}

add_action('plugins_loaded', 'blik_woocomerce_gateway_class');

function blik_woocomerce_gateway_class()
{
    class WC_Gateway_blik_woocomerce extends WC_Payment_Gateway


    {
        public $timer_enabled;
        public $testmode;
        public $private_key;
        public $publishable_key;

        public function __construct()
        {
            $this->id = 'blik-for-woocomerce_guiha';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'Blik for woocomerce';
            $this->method_description = 'Payment gateaway Blik for woocomerce';


            $this->init_form_fields();
            // инициализируем настройки
            $this->init_settings();

            $this->supports = array(
                'products'
            );

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description', 'Blik payment');
            $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
            $this->is_validate = isset($this->settings['is_validate']) === 'yes';
            $this->timer_enabled =  'yes' === $this->get_option('timer_enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->timer_enabled = 'yes' === $this->get_option('timer_enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
            $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');

            // $this->title = 'Blik payment';
            // $this->description = 'Blik payment';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('wp_enqueue_scripts', array($this, 'scripts'));

            $this->includes();
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'On/off',
                    'label'       => 'Enable method',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => 'Title method',
                    'type'        => 'text',
                    'description' => 'This text user can view on cart page',
                    'default'     => 'Blik',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'This text user can view on cart page',
                    'default'     => 'Blik payment',
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable test mode',
                    'type'        => 'checkbox',
                    'description' => '',
                    'desc_tip'    => true,
                ),
                'timer_enabled' => array(
                    'title'       => 'On/off timer on cart page',
                    'label'       => 'Enable timer',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'is_validate' => array(
                    'title'       => 'On/off',
                    'label'       => 'Allows you to send a code only if the required fields are filled in and the payment method is selected',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'test_publishable_key' => array(
                    'title'       => 'Test published key',
                    'type'        => 'text'
                ),
                'test_private_key' => array(
                    'title'       => 'Test private key',
                    'type'        => 'password',
                ),
                'publishable_key' => array(
                    'title'       => 'Published key',
                    'type'        => 'text'
                ),
                'private_key' => array(
                    'title'       => 'Private key',
                    'type'        => 'password'
                )
            );
        }

        private function includes()
        {
            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                return;
            }

            if ('no' === $this->enabled) {
                return;
            }

            require_once __DIR__ . '/stripe/init.php';
        }

        public function payment_fields()
        {
            if ($this->description) {
                if ($this->testmode) {
                    $this->description .= ' ТЕСТОВЫЙ РЕЖИМ АКТИВИРОВАН. В тестовом режиме вы можете использовать тестовые данные карт, указанные в <a href="#" target="_blank">документации</a>.';
                    $this->description  = trim($this->description);
                }
                echo wpautop(wp_kses_post($this->description));
            }

            echo '<fieldset id="wc-' . $this->id . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

            do_action('woocommerce_blik_for_woocomerce_start', $this->id);
?>
            <input type="number" min="000000" max="999999" name="blik_client_secret" placeholder="000000" pattern="/[0-9]{6}/" maxlength="6" minlength="6">
<?php

            do_action('woocommerce_blik_for_woocomerce_end', $this->id);

            echo '<div class="clear"></div></fieldset>';
        }

        // @ подключаемые скрипты
        public function scripts()
        {
            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                return;
            }

            if ('no' === $this->enabled) {
                return;
            }

            if (empty($this->private_key) || empty($this->publishable_key)) {
                return;
            }

            if (!$this->testmode && !is_ssl()) {
                return;
            }
            wp_enqueue_style('blik-for-woocomerce', plugins_url('assets/style.css', __FILE__));

            wp_enqueue_script('stripe-js-for-blik', 'https://js.stripe.com/v3/', true);
            wp_enqueue_script(
                'blik-for-woocomerce',           // Имя скрипта
                plugins_url('assets/bundle.js', __FILE__), // URL скрипта
                [],                               // Массив зависимостей (пусто)
                null,                             // Версия (null означает текущую версию WP)
                true                              // Загружать в footer
            );

            wp_localize_script(
                'blik-for-woocomerce',           // Имя скрипта
                'blikData',             // Название объекта в JS
                array(
                    'timerEnabled' => $this->timer_enabled,
                    'orderId'      => $this->get_order_id(),
                )
            );
        }

        public function get_order_id()
        {
            global $woocommerce;
            $order = $woocommerce->order;
            if (! empty($order)) {
                return $order->get_id();
            }

            return false;
        }

        public function validate_fields() {}

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            $token = $_POST['blik_client_secret'];

            if (!isset($token) || strlen($token) !== 6) {
                wc_add_notice('Incorrectly filled in the Blik code field', 'error');
                return;
            }

            $stripe = new \Stripe\StripeClient($this->private_key);

            $intent = $stripe->paymentIntents->create([
                'amount' => $order->get_total() * 100,
                'currency' => 'pln',
                'payment_method_types' => ['blik'],
                'capture_method' => 'automatic',
                'description' => 'description',
                'statement_descriptor' => 'ORDER_123',
            ]);

            $confirmResult = $stripe->paymentIntents->confirm(
                $intent->id,
                [
                    'payment_method' => 'blik',
                    'payment_method_options' => [
                        'blik' => [
                            'code' => $token
                        ]
                    ]
                ]
            );

            if ($confirmResult->status === 'succeeded') {
                $order->payment_complete();
                $order->add_order_note('Заказ оплачен, спасибочки!', true);
                WC()->cart->empty_cart();
            } else {
                wc_add_notice($confirmResult->last_payment_error->message, 'error');
            }
        }



        public function process_admin_options()
        {
            $saved = parent::process_admin_options();
            $this->init_form_fields();
            return $saved;
        }


        public function admin_options()
        {
            parent::admin_options();
        }

        private function create_intent() {}

        public function webhook_create_intent() {}
    }
}
