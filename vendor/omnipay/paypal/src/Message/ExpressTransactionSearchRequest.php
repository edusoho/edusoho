<?php

namespace Omnipay\PayPal\Message;

use DateTime;

/**
 * Paypal Express Checkout - Transaction Search
 *
 * @see https://developer.paypal.com/docs/classic/api/merchant/TransactionSearch_API_Operation_NVP/
 * @see https://developer.paypal.com/docs/classic/express-checkout/ht_searchRetrieveTransactionData-curl-etc/
 *
 * pt_BR:
 * @see https://www.paypal-brasil.com.br/desenvolvedores/tutorial/criando-relatorios-customizados-via-api/
 */
class ExpressTransactionSearchRequest extends AbstractRequest
{
    public function getData()
    {
        $data = $this->getBaseData();
        $data['METHOD'] = 'TransactionSearch';

        $this->validate('startDate');

        $data['STARTDATE'] = $this->getStartDate()->format(DateTime::ISO8601);

        if ($this->getEndDate()) {
            $data['ENDDATE'] = $this->getEndDate()->format(DateTime::ISO8601);
        }

        if ($this->getSalutation()) {
            $data['SALUTATION'] = $this->getSalutation();
        }

        if ($this->getFirstName()) {
            $data['FIRSTNAME'] = $this->getFirstName();
        }

        if ($this->getMiddleName()) {
            $data['MIDDLENAME'] = $this->getMiddleName();
        }

        if ($this->getLastName()) {
            $data['LASTNAME'] = $this->getLastName();
        }

        if ($this->getSuffix()) {
            $data['SUFFIX'] = $this->getSuffix();
        }

        if ($this->getEmail()) {
            $data['EMAIL'] = $this->getEmail();
        }

        if ($this->getReceiver()) {
            $data['RECEIVER'] = $this->getReceiver();
        }

        if ($this->getReceiptId()) {
            $data['RECEIPTID'] = $this->getReceiptId();
        }

        if ($this->getTransactionId()) {
            $data['TRANSACTIONID'] = $this->getTransactionId();
        }

        if ($this->getInvoiceNumber()) {
            $data['INVNUM'] = $this->getInvoiceNumber();
        }

        if ($this->getCard()) {
            $data['ACCT'] = $this->getCard()->getNumber();
        }

        if ($this->getAuctionItemNumber()) {
            $data['AUCTIONITEMNUMBER'] = $this->getAuctionItemNumber();
        }

        if ($this->getTransactionClass()) {
            $data['TRANSACTIONCLASS'] = $this->getTransactionClass();
        }

        if ($this->getAmount()) {
            $this->validate('currency');

            $data['AMT'] = $this->getAmount();
            $data['CURRENCYCODE'] = $this->getCurrency();
        }

        if ($this->getStatus()) {
            $data['STATUS'] = $this->getStatus();
        }

        if ($this->getProfileId()) {
            $data['PROFILEID'] = $this->getProfileId();
        }

        return $data;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    /**
     * @param DateTime|string $date
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setStartDate($date)
    {
        if (! $date instanceof DateTime) {
            $date = new DateTime($date);
        }

        return $this->setParameter('startDate', $date);
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate()
    {
        return $this->getParameter('endDate');
    }

    /**
     * @param DateTime|string $date
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setEndDate($date)
    {
        if (! $date instanceof DateTime) {
            $date = new DateTime($date);
        }

        return $this->setParameter('endDate', $date);
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->getParameter('salutation');
    }

    /**
     * @param string $salutation
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSalutation($salutation)
    {
        return $this->setParameter('salutation', $salutation);
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getParameter('firstName');
    }

    /**
     * @param string $firstName
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setFirstName($firstName)
    {
        return $this->setParameter('firstName', $firstName);
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->getParameter('middleName');
    }

    /**
     * @param string $middleName
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setMiddleName($middleName)
    {
        return $this->setParameter('middleName', $middleName);
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->getParameter('lastName');
    }

    /**
     * @param string $lastName
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setLastName($lastName)
    {
        return $this->setParameter('lastName', $lastName);
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->getParameter('suffix');
    }

    /**
     * @param string $suffix
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSuffix($suffix)
    {
        return $this->setParameter('suffix', $suffix);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * @param string $email
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setEmail($email)
    {
        return $this->setParameter('email', $email);
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->getParameter('receiver');
    }

    /**
     * @param string $receiver
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReceiver($receiver)
    {
        return $this->setParameter('receiver', $receiver);
    }

    /**
     * @return string
     */
    public function getReceiptId()
    {
        return $this->getParameter('receiptId');
    }

    /**
     * @param string $receiptId
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setReceiptId($receiptId)
    {
        return $this->setParameter('receiptId', $receiptId);
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->getParameter('invoiceNumber');
    }

    /**
     * @param string $invoiceNumber
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        return $this->setParameter('invoiceNumber', $invoiceNumber);
    }

    /**
     * @return string
     */
    public function getAuctionItemNumber()
    {
        return $this->getParameter('auctionItemNumber');
    }

    /**
     * @param string $auctionItemNumber
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setAuctionItemNumber($auctionItemNumber)
    {
        return $this->setParameter('auctionItemNumber', $auctionItemNumber);
    }

    /**
     * @return string
     */
    public function getTransactionClass()
    {
        return $this->getParameter('transactionClass');
    }

    /**
     * @param string $transactionClass
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setTransactionClass($transactionClass)
    {
        return $this->setParameter('transactionClass', $transactionClass);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getParameter('status');
    }

    /**
     * @param string $status
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setStatus($status)
    {
        return $this->setParameter('status', $status);
    }

    /**
     * @return string
     */
    public function getProfileId()
    {
        return $this->getParameter('profileId');
    }

    /**
     * @param string $profileId
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setProfileId($profileId)
    {
        return $this->setParameter('profileId', $profileId);
    }

    /**
     * @return ExpressTransactionSearchResponse
     */
    public function createResponse($data)
    {
        return $this->response = new ExpressTransactionSearchResponse($this, $data);
    }
}
