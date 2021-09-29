<?php

namespace Weblike\Cms\Shop\Module\Payment\GoPayment;

use GoPay\Definition\Language;
use GoPay\Definition\Payment\BankSwiftCode;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;

class PaymentConfiguration
{
    /** @var string */
    protected $go_id;

    /** @var string */
    protected $client_id;

    /** @var string */
    protected $client_secret;

    /** @var string */
    protected $mod;

    /** @var null|string */
    protected $lang = null;

    /** @var null|string */
    protected $currency = null;

    /** @var array */
    protected $allowed_swifts = [];

    /** @var array */
    protected $allowed_payment_instruments = [];

    /** @var array */
    protected $callbacks;
    
    function __construct( string $go_id, string $client_id, string $client_secret, ?bool $production = false )
    {
        $this->go_id = $go_id;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->mod = $production;
    }

    public function getId() : string
    {
        return $this->go_id;
    }

    public function getClientId() : ?string
    {
        return $this->client_id;
    }

    public function getSecretKey() : ?string
    {
        return $this->client_secret;
    }

    public function isProduction() : bool
    {
        return $this->mod;
    }

    public function setLang( string $lang ) : void
    {
        $this->lang = \strtoupper( $lang );
    }

    public function getLang() : string
    {
        $def_lang = Language::SLOVAK;

        switch ( $this->lang )
        {
            case 'SK' :
                return Language::SLOVAK;
            break;
            case 'CZ' :
                return Language::CZECH;
            break;
            default : return $def_lang;
        }
    }

    public function setCurrency( string $currency ) : void
    {
        $this->currency = $currency;
    }

    public function getCurrency()
    {
        if ( $this->currency === NULL )
        {
            switch( $this->lang )
            {
                case 'SK' : 
                    return Currency::EUROS;
                break;
                case 'CZ' : 
                    return Currency::CZECH_CROWNS;
                break;
                default ; return Currency::EUROS;
            }
        }

        switch( $this->currency )
            {
                case 'EUR' : 
                    return Currency::EUROS;
                break;
                case 'CZK' : 
                    return Currency::CZECH_CROWNS;
                break;
                default ; return Currency::EUROS;
            }
    }

    public function setAllowedSwifts( array $swifts ) : void
    {
        foreach ( $swifts as $swift )
        {
            if ( $swift instanceof BankSwiftCode )
            {
                $this->allowed_swifts[] = $swift;
            }
        }
    }

    public function getAllowedSwifts() : array
    {
        if ( $this->allowed_swifts && !empty( $this->allowed_swifts ) )
            return $this->allowed_swifts;
        else return [ BankSwiftCode::POSTOVA_BANKA, BankSwiftCode::TATRA_BANKA ];
    }

    public function setAllowedPaymentInstruments( array $payment_instruments )
    {
        foreach ( $payment_instruments as $payment_instrument )
        {
            if ( $payment_instrument instanceof PaymentInstrument )
                $this->allowed_payment_instruments[] = $payment_instrument;
        }
    }

    public function getAllowedPaymentInstruments() : array
    {
        if ( $this->allowed_payment_instruments && !empty( $this->allowed_payment_instruments ) )
            return $this->allowed_payment_instruments;
        else return [ PaymentInstrument::BANK_ACCOUNT, PaymentInstrument::PAYMENT_CARD ];
    }

    public function setCallbacks( string $return_url, string $notification_url )
    {
        $this->callbacks = [
            'return_url' => $return_url,
            'notification_url' => $notification_url,
        ];
    }

    public function getCallbacks() : array
    {
        return $this->callbacks;
    }

}