<?php

namespace Integrai\Core\Block\Adminhtml\Order\View;

class PaymentInfo extends \Magento\Backend\Block\Template
{
    protected $_helper;
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Integrai\Core\Helper\Data $helper,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
    }

    public function getPaymentResponse() {
        $order = $this->_coreRegistry->registry('sales_order');
        $paymentAdditionalInformation = $order->getPayment()->getAdditionalInformation();

        $marketplace_data = array();
        $payments_data = array();
        $marketplace = (array) $paymentAdditionalInformation['marketplace'];
        $payments = (array) $paymentAdditionalInformation['payments'];

        if (isset($marketplace)) {
            $name = isset($marketplace['name']) ? $marketplace['name'] : '';
            $order_id = isset($marketplace['order_id']) ? $marketplace['order_id'] : '';
            $created_at = isset($marketplace['created_at']) ? date_format(date_create($marketplace['created_at']), 'd/m/Y H:i:s') : '';
            $updated_at = isset($marketplace['updated_at']) ? date_format(date_create($marketplace['updated_at']), 'd/m/Y H:i:s') : '';

            $marketplace_data = array(
                'Criado por' => $name,
                'Nº Pedido Marketplace' => $order_id,
                'Data criação do pedido no marketplace' => $created_at,
                'Data atualização do pedido no marketplace' => $updated_at
            );
        }

        if (isset($payments) && count($payments) > 0) {
            foreach ($payments as $payment) {
                $method = isset($payment['method']) ? $payment['method'] : '';
                $module_name = isset($payment['module_name']) ? $payment['module_name'] : '';
                $value = isset($payment['value']) ? 'R$' . number_format($payment['value'],2,",",".") : '';
                $transaction_id = isset($payment['transaction_id']) ? $payment['transaction_id'] : '';
                $date_approved = isset($payment['date_approved']) ? date_format(date_create($payment['date_approved']), 'd/m/Y H:i:s') : '';
                $installments = isset($payment['installments']) ? $payment['installments'] . 'x' : '';
                $boleto = isset($payment['boleto']) ? (array) $payment['boleto']: '';
                $card = isset($payment['card']) ? (array) $payment['card']: '';
                $pix = isset($payment['pix']) ? (array) $payment['pix']: '';

                $card_data = '';
                if (isset($card) && is_array($card)) {
                    $card_number = isset($card['last_four_digits']) ? $card['last_four_digits'] : '';
                    $card_brand = isset($card['brand']) ? $card['brand'] : '';
                    $card_holder = isset($card['holder']) ? $card['holder'] : '';
                    $expiration_month = isset($card['expiration_month']) ? $card['expiration_month'] : '';
                    $expiration_year = isset($card['expiration_year']) ? $card['expiration_year'] : '';
                    $expiration = implode('/', array_filter(array($expiration_month, $expiration_year)));

                    $card_data = array(
                        'Número do cartão' => "**** **** **** $card_number",
                        'Nome do titular' => $card_holder,
                        'Expiração' => $expiration,
                        'Bandeira' => strtoupper( $card_brand )
                    );
                }

                $payments_data[] = array(
                    'Método' => $method,
                    'Processado por' => $module_name,
                    'Identificação da transação' => $transaction_id,
                    'Data de pagamento' => $date_approved,
                    'Nº de Parcelas' => $installments,
                    'Valor cobrado' => $value,
                    'boleto' => $boleto,
                    'card' => $card_data,
                    'pix' => $pix,
                );
            }
        }

        $this->_helper->log("marketplace_data ", $marketplace_data);
        $this->_helper->log("payments_data ", $payments_data);

        return array(
            'marketplace_data' => $marketplace_data,
            'payments' => $payments_data,
        );
    }
}