<?php

namespace Weblike\Cms\Shop\Interfaces;

interface IOrder
{
    /** @var int PENDING - cakajuca */
    const PENDING = 1;

    /** @var int PAID - zaplatena */
    const PAID = 2;

    /** @var int CANCELED - zrusena */
    const CANCELED = 3;
    
    /** @var int REFUNDED - refundovana */
    const REFUNDED = 4;

    /** @var int STORNO - stornovana */
    const STORNO = 5;

    /** @var int PARTLY_PAID - ciastocne uhradena */
    const PARTLY_PAID = 6;

    /** @var int SHIPPED - expedovana */
    const SHIPPED = 1;

    /** @var int SHIPPED - na expedovani */
    const SHIPPING = 2;

    /** @var string - navratove hlasky */
    const RETURN_MESSAGE_TIMEOUTED = 'Čas vypršal';
    const RETURN_MESSAGE_CANCELED = 'Zrušená';
    const RETURN_MESSAGE_CREATED = 'Čakáme na úhradu';
    const RETURN_MESSAGE_PAID = 'Zaplatená';
    const RETURN_MESSAGE_PENDING = 'Čaká na úhradu';
    const RETURN_MESSAGE_REFUNDED = 'Objednávka bola uhradená. Platba bola vrátená.';
    const PROMOCODES_SALE_IN_CURRENCY_MESSAGE = 'Na ďalší nákup Vás odmeníme %s %s zľavou. Zadajte kód';
    const PROMOCODES_SALE_IN_PERCENTAGE_MESSAGE = 'Za ďalší nákup Vás odmeníme zľavou %s EUR. Zadajte kód';
    const PROMOCODES_SALE_IN_DELIVERY_MESSAGE = 'Za ďalší nákup Vás odmeníme odmeníme %s %s zľavou na dopravu. Zadajte kód';
    const PARSING_CUSTOMER_INPUTS_DATA_MESSAGE = "Je potrebne vyplnit dodacie udaje, ako adresa, mesto.\nPripadne doplnit fakturacne udaje.";
    const PARSING_CUSTOMER_INPUTS_DATA_TITLE = 'Dodacie udaje';

    const PAYMENT_STATE_MESSAGE_PAID = 'Order nr.: %s is paid.';
    const PAYMENT_STATE_MESSAGE_CANCELED = 'Order nr.: %s has been canceled.';
    const PAYMENT_STATE_MESSAGE_TIMEOUTED = 'Order nr.: %s wait for payment. Time for payment has expired.';
    const PAYMENT_STATE_MESSAGE_REFUNDED = 'Order nr.: %s was refunded.';
    const PAYMENT_STATE_MESSAGE_CREATED = 'Order nr.: %s wait for payment.';
    const PAYMENT_STATE_MESSAGE_PARTLY_PAID = 'Order nr.: %s wait for full payment.';

    const PAYMENT_STATE_TITLE_PAID = 'Order paid';
    const PAYMENT_STATE_TITLE_CANCELED = 'Canceled';
    const PAYMENT_STATE_TITLE_TIMEOUTED = 'Transaction expired';
    const PAYMENT_STATE_TITLE_REFUNDED = 'Order has been refunded';
    const PAYMENT_STATE_TITLE_CREATED = 'Transaction created';
    const PAYMENT_STATE_TITLE_PARTLY_PAID = 'Order partly paid';

    const PACKAGE_EXPANDED_SUCCESS_TITLE = 'Zásielka bola úspešne expedovaná';
    const PACKAGE_EXPANDED_SUCCESS_MESSAGE = 'Zásielku sa podarilo úspešne expedovať a zákazník bol o tom upovedomený e-mailom.';
    const PACKAGE_ON_EXPAND_SUCCESS_TITLE = 'Zásielka bola úspešne označená na expedovanie';
    const PACKAGE_ON_EXPAND_SUCCESS_MESSAGE = 'Zásielku sa podarilo úspešne označiť ako "na expedovanie". V Prípade doplnenia čísla bude zákazník informovaný o tomto kroku e-mailom.';

    
}