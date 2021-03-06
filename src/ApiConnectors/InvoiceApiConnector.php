<?php

namespace PhpTwinfield\ApiConnectors;

use PhpTwinfield\Exception;
use PhpTwinfield\Invoice;
use PhpTwinfield\DomDocuments\InvoicesDocument;
use PhpTwinfield\Mappers\InvoiceMapper;
use PhpTwinfield\Office;
use PhpTwinfield\Request as Request;
use Webmozart\Assert\Assert;

/**
 * A facade to make interaction with the Twinfield service easier when trying to retrieve or set information about
 * Invoices.
 *
 * If you require more complex interactions or a heavier amount of control over the requests to/from then look inside
 * the methods or see the advanced guide details the required usages.
 *
 * @author Leon Rowland <leon@rowland.nl>
 * @copyright (c) 2013, Pronamic
 */
class InvoiceApiConnector extends ProcessXmlApiConnector
{
    /**
     * Requires a specific invoice based off the passed in code, invoiceNumber and optionally the office.
     *
     * @param string $code
     * @param string $invoiceNumber
     * @param Office $office
     * @return Invoice|bool The requested invoice or false if it can't be found.
     * @throws Exception
     */
    public function get(string $code, string $invoiceNumber, Office $office)
    {
        // Make a request to read a single invoice. Set the required values
        $request_invoice = new Request\Read\Invoice();
        $request_invoice
            ->setCode($code)
            ->setNumber($invoiceNumber)
            ->setOffice($office->getCode());

        // Send the Request document and set the response to this instance
        $response = $this->sendDocument($request_invoice);

        return InvoiceMapper::map($response);
    }

    /**
     * Sends a \PhpTwinfield\Invoice\Invoice instance to Twinfield to update or add.
     *
     * @param Invoice $invoice
     * @throws Exception
     */
    public function send(Invoice $invoice): void
    {
        $this->sendAll([$invoice]);
    }

    /**
     * @param Invoice[] $invoices
     * @throws Exception
     */
    public function sendAll(array $invoices): void
    {
        Assert::allIsInstanceOf($invoices, Invoice::class);
        Assert::notEmpty($invoices);

        // Gets a new instance of InvoicesDocument and sets the invoice
        $invoicesDocument = new InvoicesDocument();

        foreach ($invoices as $invoice) {
            $invoicesDocument->addInvoice($invoice);
        }

        // Sends the DOM document request and sets the response
        $this->sendDocument($invoicesDocument);
    }
}
