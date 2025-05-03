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
        public $text_in_popup;

        public $payment_method;
        public $stripe;

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
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->timer_enabled = 'yes' === $this->get_option('timer_enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->private_key = $this->testmode ? $this->get_option('test_private_key') : $this->get_option('private_key');
            $this->publishable_key = $this->testmode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');
            $this->text_in_popup = $this->get_option('text_in_popup');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('wp_enqueue_scripts', array($this, 'scripts'));

            add_action('woocommerce_api_get_client_secret_blik', array($this, 'get_client_secret_blik_webhook'));

            $this->includes();

            $this->stripe = new \Stripe\StripeClient($this->private_key);
            $this->create_payment_method();
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
                'text_in_popup' => array(
                    'title'       => 'Text in popup',
                    'type'        => 'textarea',
                    'description' => 'This text user can view on popup after success blik code',
                    'default'     => 'Confirm payment in the app within 60 seconds',
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable test mode',
                    'type'        => 'checkbox',
                    'description' => '',
                    'desc_tip'    => true,
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

            do_action('woocommerce_blik_for_woocomerce_start', $this->id);
?>

            <fieldset id="wc-<?php echo $this->id ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
                <input type="hidden" name="blik_pass" value="no">
                <input type="hidden" name="blik_error" value="">
                <input type="number" min="000000" max="999999" name="blik_token" placeholder="000000" pattern="/[0-9]{6}/" maxlength="6" minlength="6">
                <?php do_action('woocommerce_blik_for_woocomerce_end', $this->id); ?>
                <div class="clear"></div>
            </fieldset>
<?php echo '';
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

            wp_enqueue_style('blik-for-woocomerce', plugins_url('assets/style.css', __FILE__));

            wp_enqueue_script('stripe-for-blik', 'https://js.stripe.com/v3/', true);
            wp_enqueue_script(
                'blik-for-woocomerce',           // Имя скрипта
                plugins_url('assets/bundle.js', __FILE__), // URL скрипта
                [],                               // Массив зависимостей (пусто)
                null,                             // Версия (null означает текущую версию WP)
            );

            wp_localize_script(
                'blik-for-woocomerce',           // Имя скрипта
                'blikObject',             // Название объекта в JS
                array(
                    'publicKey' => $this->publishable_key,
                    'textInPopup' => $this->text_in_popup,
                )
            );
        }


        public function process_payment($order_id)
        {
            $token = $_POST['blik_token'];
            $pass = $_POST['blik_pass'];
            $error = $_POST['blik_error'];

            if (!isset($token) || strlen($token) !== 6 || !isset($pass) || !isset($error)) {
                wc_add_notice('Incorrectly filled in the Blik code field', 'error');
                return;
            }

            $order = wc_get_order($order_id);

            $confirmed = $this->create_intent($order, $token);


            if ($confirmed->status === 'succeed') {
                $this->confirm_payment($order);
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            }

            wc_add_notice($confirmed->cancellation_reason, 'error');

            return;

            // if ($error !== '') {
            //     wc_add_notice($error, 'success');
            //     return;
            // }

            // if ($pass === 'no') {
            //     $this->create_intent($order);
            //     wc_add_notice('Confirm payment in stripe', 'success');
            //     return;
            // }

            // if ($pass === 'yes') {
            //     $this->confirm_payment($order);
            //     return array(
            //         'result'   => 'success',
            //         'redirect' => $this->get_return_url($order)
            //     );
            // }

            // wc_add_notice('Something went wrong.', 'error');
        }

        public function confirm_payment($order)
        {
            $order->payment_complete();
            $order->add_order_note('Заказ оплачен, спасибочки!', true);
            WC()->cart->empty_cart();
        }

        public function create_intent($order, $token)
        {


            $intent = $this->stripe->paymentIntents->create([
                'amount' => $order->get_total() * 100,
                'currency' => 'pln',
                'payment_method_types' => ['blik'],
                'payment_method' => $this->payment_method->id,
                'capture_method' => 'automatic',
                'description' => 'description',
                'statement_descriptor' => 'ORDER_' . $order->get_id(),
            ]);

            $confirmed = $intent->confirm(
                $intent->id,
                [
                    'payment_method' => $this->payment_method->id,
                    'payment_method_options' => [
                        'blik' => [
                            'code' => $token,
                        ],
                    ]
                ]
            );


            return $confirmed;

            // if (session_status() === PHP_SESSION_NONE) {
            //     session_start();
            // }

            // $_SESSION["blik_client_secret"] = $intent->client_secret;
        }

        public function create_payment_method()
        {
            $this->payment_method = $this->stripe->paymentMethods->create([
                'type' => 'blik',
                'blik' => [],
                'billing_details' => ['name' => 'John Doe'],
            ]);

            // echo json_encode($this->payment_method);
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

        public function get_client_secret_blik_webhook()
        {
            session_start();
            $client_secret = $_SESSION["blik_client_secret"];

            if (!isset($client_secret)) {
                echo json_encode([
                    'status' => 'error',
                    'error' => 'client secret not found'
                ]);
                die();
            }

            echo json_encode([
                'status' => 'ok',
                'client_secret' => $client_secret,
            ]);
            die();
        }
    }
}
