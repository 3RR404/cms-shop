<?php

namespace Weblike\Cms\Shop\Interfaces;

use Weblike\Cms\Shop\Response;

interface IPayment
{
	const PAYMENT_TYPE_TITLE_CARD =  'Platba kartou';
	const PAYMENT_TYPE_MESSAGE_CARD =  'Platba zatiaľ nebola uhradená. Čakáme na prevod peňažných prostriedkov. ';
	const PAYMENT_TYPE_TITLE_CASH =  'Platba v hotovosti';
	const PAYMENT_TYPE_MESSAGE_CASH =  'Platba zatiaľ nebola uhradená. Čakáme na príjem hotovosti.';
	const PAYMENT_TYPE_TITLE_BANK_ACCOUNT =  'Platba prevodom';
	const PAYMENT_TYPE_MESSAGE_BANK_ACCOUNT =  'Platba zatiaľ nebola uhradená. Čakáme na prevod peňažných prostriedkov. ';
	const PAYMENT_TYPE_TITLE_DOBIERKA =  'Platba na dobierku';
	const PAYMENT_TYPE_MESSAGE_DOBIERKA =  'Platba zatiaľ nebola uhradená. Čakáme na príjem hotovosti.';

	/** @var string returning url from getaway */
	const CALLBACK_URL = '/e-shop/response';

	/** @var string notification url */
	const NOTIFICATION_URL = '/e-shop/notify';

	/** @var integer bank acc transfer payment code */
	const BANK_TRANSFER = 3;

	/** @var integer kredit/debet card code */
	const CARD = 1;

	/** @var integer cash code */
	const CASH = 2;

	/** @var integer cash on delivery */
	const COD = 4;

	/** @var integer online transfer payment code */
	const ONLINE = 1;

	/** @var integer offline transfer payment code */
	const OFFLINE = 0;

	public function setData(string $data): \Weblike\Cms\Shop\PaymentManager;

	public function pay(): \Weblike\Cms\Shop\PaymentManager;

	public function getResponse(): ?\Weblike\Cms\Core\Response;

	public function getJsonResponse(): ?string;
}
