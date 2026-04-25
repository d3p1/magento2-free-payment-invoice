<?php
/**
 * @description Create invoice
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace D3p1\FreePaymentInvoice\Observer;

use D3p1\FreePaymentInvoice\Api\SystemConfigInterface;
use D3p1\InstantInvoice\Api\InvoiceManagementInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Free;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class CreateInvoice implements ObserverInterface
{
    /**
     * @var InvoiceManagementInterface
     */
    protected InvoiceManagementInterface $_invoiceManagement;

    /**
     * Constructor
     *
     * @param InvoiceManagementInterface $invoiceManagement
     */
    public function __construct(InvoiceManagementInterface $invoiceManagement)
    {
        $this->_invoiceManagement = $invoiceManagement;
    }

    /**
     * Execute
     *
     * @param  Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        /** @var OrderInterface|Order $order */
        $order = $observer->getEvent()->getData('order');
        if ($order) {
            /** @var OrderPaymentInterface|Payment $payment */
            $payment = $order->getPayment();

            if ($this->_isFree($payment)) {
                /**
                 * @note Check configuration to determine
                 *       whether to create automatic invoice
                 */
                if ($payment->getMethodInstance()->getConfigData(
                    SystemConfigInterface::GENERATE_INVOICE_AUTOMATICALLY_KEY,
                    $order->getStoreId()
                )) {
                    /**
                     * @note Create invoice
                     */
                    $this->_invoiceManagement->create($order);
                }
            }
        }
    }

    /**
     * Validate if order/payment is free
     *
     * @param  OrderPaymentInterface $payment
     * @return bool
     */
    private function _isFree(OrderPaymentInterface $payment): bool
    {
        /**
         * @note Check if payment method is free payment method
         */
        return $payment->getMethod() === Free::PAYMENT_METHOD_FREE_CODE;
    }
}
