<?php

namespace Weblike\Cms\Shop;

use Doctrine\ORM\Query;
use Weblike\Plugins\doHash;
use Dompdf\Dompdf;
use Weblike\Cms\Core\Db;
use Weblike\Cms\Core\Template;
use Weblike\Cms\Shop\Entity\Order;

define('DOMPDF_ENABLE_AUTOLOAD', false);

class InvoiceManager
{
	/** @var integer PAID - zaplatena */
	const PAID = 1;

	/** @var integer PPROFORMA - cakajuca */
	const PPROFORMA = 2;

	/** @var integer PENDING - cakajuca */
	const PENDING = 3;

	/** @var int REFUNDED - refundovana */
	const REFUNDED = 4;

	/** @var integer STORNO - zrusena */
	const STORNO = 5;

	/** @var int PARTLY_PAID - ciastocne uhradena */
	const PARTLY_PAID = 6;

	/** @var bool $restock_after_invoice - preskladnenie (default: false) */
	public static $restock_after_invoice = false;

	protected $orders;

	/** @var OrderManager */
	protected $orderManager;

	protected $invoiceData;

	/** @var ShopConfiguration */
	protected $shopCfg;

	/** @var string */
	protected $invoice_id;

	function __construct()
	{
		if ($this->companyDataIsntComplete() === TRUE) return false;

		$this->orderManager = new OrderManager;
		$this->shopCfg = new ShopConfiguration;
		// self::$restock_after_invoice = (\globalSettings('change_stock') === 'change_stock_invoice') ? true : false;
	}

	/**
	 * ## Udaje o spolocnosti
	 * - bez tichto informacii nie je mozne FA vystavit
	 * @return bool
	 * @method companyDataIsntComplete
	 * @static
	 */
	public static function companyDataComplete()
	{
		// if (empty(\companyData('name'))) return true;
		// if (empty(\companyData('address'))) return true;
		// if (empty(\companyData('city'))) return true;
		// if (empty(\companyData('zip'))) return true;
		// if (empty(\companyData('ico'))) return true;
		// // if ( empty( \companyData( 'dic' ) ) ) return true;
		// if (empty(\companyData('iban'))) return true;
		// if (empty(\companyData('tax_payer'))) return true;
		// if (empty(\companyData('country'))) return true;
		return false;
	}

	/**
	 * ## Udaje o spolocnosti
	 * - bez tichto informacii nie je mozne FA vystavit
	 * @return bool
	 */
	public function companyDataIsntComplete()
	{
		// if (empty(\companyData('name'))) return true;
		// if (empty(\companyData('address'))) return true;
		// if (empty(\companyData('city'))) return true;
		// if (empty(\companyData('zip'))) return true;
		// if (empty(\companyData('ico'))) return true;
		// // if ( empty( \companyData( 'dic' ) ) ) return true;
		// if (empty(\companyData('iban'))) return true;
		// if (empty(\companyData('tax_payer'))) return true;
		// if (empty(\companyData('country'))) return true;

		return false;
	}

	protected function parseInvoiceData($data)
	{
		if (isset($data['created_at'])) unset($data['created_at']);
		if (isset($data['token'])) unset($data['token']);
		if (isset($data['id'])) unset($data['id']);
		if (isset($data['discount_tax'])) unset($data['discount_tax']);
		if (isset($data['discount_wthTax'])) unset($data['discount_wthTax']);
		if (isset($data['payments'])) unset($data['payments']);
		if (isset($data['package'])) unset($data['package']);
		if (isset($data['status'])) unset($data['status']);
		if (isset($data['invoices'])) unset($data['invoices']);
		if (isset($data['class'])) unset($data['class']);

		$data = array_filter($data);

		foreach ($data as $k => $v) {
			switch ($k) {
				case 'products':
				case 'shipping_to':
				case 'promocodes':
				case 'loyality_points':
				case 'payment':
				case 'shipping':
					if (gettype($v) === 'string' && \startsWith($v, '{')) {
						$data[$k] = json_decode($v, true);
					} else {
						$data[$k] = json_encode($v, \JSON_FORCE_OBJECT);
					}
			}
		}
		return $data;
	}

	public function setInvoiceId(string $invoice_id): self
	{
		$this->invoice_id = $invoice_id;
	}

	/**
	 * ## Vytvorenie faktury
	 * 
	 * @param integer $order_number cislo objednavky
	 * @return array invoice data
	 */
	public function create(int $order_number)
	{
		if ($this->companyDataIsntComplete() === TRUE) return false;

		$order_data = $this->order->getByOrderNumber($order_number);
		$order_data = $this->parseInvoiceData($order_data);

		if (empty($this->getInvoiceByOrderNumber($order_number, 'id'))) {
			if (Db::get()->es_invoices()->insert($order_data))                                                   // vlozi zaznam do tabulky
			{
				$last_id = Db::get()->es_invoices()->order('id DESC')->limit(1)->fetch('id');                       // vyberie posledne pridane ID
				$num = Shop::$order_num_start . '' . str_pad($last_id, Shop::$order_num_count, 0, STR_PAD_LEFT);  // vytvori format cisla podla objednavky
				$token = doHash::getHash(10, true);
				Db::get()->es_invoices('id', $last_id)->update([
					'invoice_number' => $num,
					'token'          => $token
				]);                                                                                                 // vlozi cislo faktury a token na stiahnutie vFA
				if (self::$restock_after_invoice === true) {
					$this->order->restock($order_data);
				}
				$this->invoiceData = $this->getInvoiceData($last_id);
				return $this->invoiceData;
			}
		}

		return false;
	}

	/**
	 * ## FA podla obj.c
	 * 
	 * @param integer $order_number cislo obj.
	 * @param string $fetch_column vypis z tabulky v tejto bunke
	 * 
	 * @return object NotORM::Row
	 */
	public function getInvoiceByOrderNumber($order_number, $fetch_column = '')
	{
		$tb = Db::get()->es_invoices('order_number', $order_number);

		return !empty($fetch_column) ? $tb->fetch($fetch_column) : $tb->fetch();
	}

	/**
	 * ## FA podla obj.c
	 * - vytiahne vsetky FA podla cisla obj.
	 * 
	 * @param integer $order_number cislo obj.
	 * 
	 * @return object NotORM::Results
	 */
	public function getInvoicesByOrderNumber(int $order_number)
	{
		$tb = Db::get()->es_invoices('order_number', $order_number);

		return $tb;
	}

	/**
	 * ## FA podla ID
	 * 
	 * @param bool|integer $id (default=false) Identifikator v DB
	 * @param bool $fetch (default=false) ak je true prihliada na $column_fetching
	 * @param string $column_fetching vypis z tejto bunky
	 */
	public function getInvoice(string $id, ?bool $as_array = false)
	{
		// if ($this->companyDataIsntComplete() === TRUE) return false;

		return Db::get()->getRepository(Order::class)
			->createQueryBuilder('o')
			->where('o.id = :oId')
			->setParameter('oId', $id)
			->getQuery()
			->getOneOrNullResult($as_array ? Query::HYDRATE_ARRAY : Query::HYDRATE_OBJECT);
	}

	/**
	 * ### Nastavi data globalnej premennej
	 * @param integer $id
	 * @return self
	 */
	public function setInvoiceData(string $id): self
	{
		$this->invoiceData = $this->getInvoice($id);

		return $this;
	}

	/**
	 * ## Update zaplatenej Obj.
	 * - pripravi subor do /tmp vo formate PDF podla template
	 * - ak nie su zadane FA udaje v globalnych nastaveniach fakturu neuhradi
	 * 
	 * @param string $order_id cislo objednavky, podla ktorej update upravi status
	 * @param string|double|float $amount ciastka, ktora bola uhradena
	 * 
	 * @return string invoice file full path (absolute)
	 */
	public function paid($order_number, $amount)
	{
		if ($this->companyDataIsntComplete() === TRUE) return false;

		$invoice = $this->getInvoiceData($order_number); /* Db::get()->es_invoices()->where( 'order_number', $order_number )->fetch(); */

		if ($invoice !== FALSE) {
			$data = [
				'state' => ($amount >= $invoice['total']) ? self::PAID : self::PARTLY_PAID,
				'paid' => (float)whoolPrice($amount, 2, '.')
			];

			if ($invoice['state'] !== self::PAID) {
				Db::get()->es_invoices()->where('order_number', $order_number)->update($data);
			}

			return $this->exportToPdf($invoice);
		} else {
			$this->create($order_number);
			return $this->paid($order_number, $amount);
		}
	}

	/**
	 * ## Sablona FA
	 * - nacitanie sablony v html
	 * 
	 * @param array $invoice_data data faktury,
	 * @param string $template nazov suboru bez koncovky v html
	 * @return string
	 */
	protected function invoiceTemplate($invoice_data, string $template = 'invoice')
	{
		// if (empty($template)) {
		// 	if ((int)\companyData('tax_payer') === -1) $template = 'invoice';
		// 	else $template = 'invoice_taxpayer';
		// }

		$signatureFile = WEB_DIR . "/src/img/invoice/signature.png";

		$base64img = null;

		if (file_exists($signatureFile)) {
			$type = mime_content_type("$signatureFile");
			$base64img = base64_encode(file_get_contents($signatureFile));
		}

		$layout = new Template(ROOT_DIR . "/templates/shop/$template.latte");
		$layout->signature = file_exists($signatureFile)
			? "<img src=\"data:$type;charset=utf-8;base64,$base64img\" alt=\"Invoice signature\" width=\"352\" height=\"216\" />"
			: null;
		$layout->invoice = $invoice_data;
		$layout->shopCfg = $this->shopCfg;

		return $layout->toString(true);
	}

	/**
	 * ## Export FA do PDF
	 * @param $invoice_data (default=false) - ak je nastavena globalna premenna,
	 * data sa nachadzaju v nej viz. setInvoiceData
	 * @return string invoice file full path (absolute)
	 * @method setInvoiceData
	 */
	public function exportToPdf($stream = true)
	{
		$html = $this->invoiceTemplate($this->invoiceData);

		$dompdf = new Dompdf();

		$options = $dompdf->getOptions();
		$options->setIsHtml5ParserEnabled(true);
		$dompdf->setOptions($options);

		$dompdf->loadHtml($html);
		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4');
		// $dompdf->parseDefaultView('Fit');
		// Render the HTML as PDF
		$dompdf->render();

		if ($stream === FALSE) {
			// Ulozi do suboru
			$file = ROOT_DIR . '/tmp/' . \uniqid() . '_FV_' . $this->invoiceData->getOrderNumber() . '.pdf';
			file_put_contents($file, $dompdf->output());
			// a skonci
			return $file;
		} else if ($stream === TRUE) {
			// vypluje PDF do browsera
			$dompdf->stream('FV_' . $this->invoiceData->getOrderNumber() . '.pdf', ['compress' => 1, "Attachment" => 1]);
		}
	}

	/**
	 * ## Storno FA
	 * - ulozenie dat o storne
	 * @param int|string $order_number cislo podla formatu obj
	 * @return string FA cislo
	 */
	public function stornoInvoiceByOrderNumber($order_number)
	{
		$invoice_data = $this->getInvoiceData($order_number);
		$order = new Order;
		if (empty($invoice_data)) $invoice_data = $order->getOne($order_number);

		foreach ($invoice_data['products'] as $key => $product) {
			foreach ($product as $k => $v) {
				switch ($k) {
					case 'price':
						$product[$k] = (float)$v * -1;
						break;
					case 'with_tax':
						$product[$k] = (float)$v * -1;
						break;
					case 'tax':
						$product[$k] = (float)$v * -1;
						break;
					default:
						$product[$k] = $v;
				}
			}
			$products[$key] = $product;
		}

		$shipping = $invoice_data['shipping'];
		$shipping['price'] = ($invoice_data['shipping']['price'] > 0) ? (float)$invoice_data['shipping']['price'] * -1 : \whoolPrice(0, 2);
		$shipping['wth_tax'] = ($invoice_data['shipping']['wth_tax'] > 0) ? (float)$invoice_data['shipping']['wth_tax'] * -1 : \whoolPrice(0, 3);
		$payment = $invoice_data['payment'];
		$payment['price'] = ($invoice_data['payment']['price'] > 0) ? (float)$invoice_data['payment']['price'] * -1 : \whoolPrice(0, 2);
		$payment['wth_tax'] = ($invoice_data['payment']['wth_tax'] > 0) ? (float)$invoice_data['payment']['wth_tax'] * -1 : \whoolPrice(0, 3);

		$promocodes = [
			$invoice_data['promocodes'],
			'price' => $invoice_data['promocodes']['price'] * -1,
			'wth_tax' => $invoice_data['promocodes']['price_wthTax'] * -1,
		];

		$data = [
			'original_number' => $invoice_data['invoice_number'],
			'order_number' => $invoice_data['order_number'],
			'lang' => $invoice_data['lang'],
			'currency' => $invoice_data['currency'],
			'products' => json_encode($products, JSON_FORCE_OBJECT),
			'promocodes' => json_encode($promocodes, JSON_FORCE_OBJECT),
			'shipping' => json_encode($shipping, JSON_FORCE_OBJECT),
			'payment' => json_encode($payment, JSON_FORCE_OBJECT),
			'subtotal' => (float)$invoice_data['subtotal'] * -1,
			'discount' => \parseFloat($invoice_data['discount']) * -1,
			'total_wthTax' => (float)$invoice_data['total_wthTax'] * -1,
			'total_tax' => (float)$invoice_data['total_tax'] * -1,
			'total' => (float)$invoice_data['total'] * -1,
			'paid' => (float)$invoice_data['total'] * -1,
			'state' => self::REFUNDED,
		];

		$data['user_id'] = $invoice_data['user_id'];
		$data['shipping_to'] = json_encode($invoice_data['shipping_to'], JSON_FORCE_OBJECT);

		// Aktualizacia stavu FA
		// Db::get()->es_invoices( 'invoice_number', $invoice_data['invoice_number'] )->update([
		//     'state' => self::REFUNDED,
		//     'status' => self::REFUNDED
		// ]);

		if (empty(Db::get()->es_invoices()->where('original_number', $invoice_data['invoice_number'])->fetch('id'))) {
			if (Db::get()->es_invoices()->insert($data)) {
				$last_id = Db::get()->es_invoices()->order('id DESC')->limit(1)->fetch('id');                       // vyberie posledne pridane ID
				$num = Shop::$order_num_start . '' . str_pad($last_id, Shop::$order_num_count, 0, STR_PAD_LEFT);  // vytvori format cisla podla objednavky
				$token = doHash::getHash(10, true);
				Db::get()->es_invoices('id', $last_id)->update([
					'invoice_number'    => $num,
					'token'             => $token
				]);
				$this->refundedToMail($invoice_data);
			}
		} else $num = $invoice_data['invoice_number'];


		// return $data;
		return $num;
	}

	/**
	 * ## Odoslanie dat o spracovani
	 * 
	 * - odoslanie dat o spracovani refundacie na email
	 * @param array|object $data data z objednavky
	 * @param string $template nazov suboru bez koncovky
	 * @return bool
	 */
	function refundedToMail($data, string $template = '')
	{
		if (empty($template)) $template = 'order-refunded';

		$email = $data['shipping_to']['email'];
		$attachment = false;

		$predmet = 'Vraciame vám platbu';
		$mail = new \Mail($template, array(
			'title' => $predmet,
			'email' => $email,
			'data' => $data
		));

		$mail->subject = $predmet;
		$mail->to = $email;
		$mail->addImage(WEB_DIR . '/img/home/logo.png', 'logo');
		if (!$this->order->mailSysMailinator($email)) {
			$mail->addAttachment(WEB_DIR . '/upload/filemanager/docs/vop.doc');
			if ($attachment !== FALSE && \file_exists($attachment)) {
				$mail->addAttachment($attachment);
			}
		}

		try {
			$mail->send($email);
			return true;
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	/**
	 * ### Odoslanie dat do prehliadaca
	 * - stiahne pdf ak uzivatel povoli stahovanie na stranke
	 * @return void
	 */
	public function downloadInvoiceAsPdf()
	{
		return $this->exportToPdf(false, true);
	}

	// STATS ||\/\/\/\/\/\||

}

// class InvoicesStats extends Invoices
// {
// 	/**
// 	 * ## Zaznam o ocakavanom prijme / rok
// 	 * @param integer|string $year (default:false)
// 	 * @return string
// 	 */
// 	public function getExpectedIncome($year = false)
// 	{
// 		if ($year === FALSE) $year = date('Y');
// 		$date_from = $year . '-01-01 00:00:01';
// 		$date_to = "$year-12-31 23:59:59";
// 		if ($year == date('Y')) $date_to = date('Y-m-d H:i:s', time());
// 		return Db::get()->es_orders('state', self::PENDING)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	/**
// 	 * ## Zaznam o prijme zo zaplatenych FA / rok
// 	 * @param integer|string $year (default:false)
// 	 * @return string
// 	 */
// 	public function getTotalIncome($year = false)
// 	{
// 		if ($year === FALSE) $year = date('Y');
// 		$date_from = $year . '-01-01 00:00:00';
// 		$date_to = "$year-12-31 23:59:59";
// 		if ($year == date('Y')) $date_to = date('Y-m-d H:i:s', time());
// 		return Db::get()->es_invoices('state = ' . self::PAID . ' OR state = ' . self::PARTLY_PAID)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	/**
// 	 * ## Zaznam o prijme zo zaplatenych FA / aktualny rok
// 	 * @return string
// 	 */
// 	public function getTotalIncomeCurrentYear()
// 	{
// 		$year = date('Y');
// 		$date_from = "$year-01-01 00:00:00";
// 		$date_to = date('Y-m-d H:i:s');

// 		return Db::get()->es_invoices('state = ' . self::PAID . ' OR state = ' . self::PARTLY_PAID)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	/**
// 	 * ## Zaznam o prijme zo zaplatenych FA / minuly rok
// 	 * @return string
// 	 */
// 	public function getTotalIncomeLastYear()
// 	{
// 		$year = date('Y', strtotime("-1 year"));
// 		$date_from = "$year-01-01 00:00:00";
// 		$date_to = "$year-31-12 23:59:59";

// 		return Db::get()->es_invoices('state = ' . self::PAID . ' OR state = ' . self::PARTLY_PAID)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	/**
// 	 * ## Zaznam o refundaciach / rok
// 	 * @param integer|string $year (default:false)
// 	 * @return string
// 	 */
// 	public function getTotalRefunded($year = false)
// 	{
// 		if ($year === FALSE) $year = date('Y');
// 		$date_from = $year . '-01-01 00:00:00';
// 		$date_to = "$year-12-31 23:59:59";
// 		if ($year == date('Y')) $date_to = date('Y-m-d H:i:s', time());
// 		return Db::get()->es_invoices('state', self::REFUNDED)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	/**
// 	 * ## Zaznam o zisku za rok
// 	 * @param integer|string $year (default:false)
// 	 * @return string
// 	 */
// 	public function getIncome($year = false, $month_from = false, $month_to = false)
// 	{
// 		if ($year === FALSE) $year = date('Y');

// 		if ($month_from === false) $month_from = date('m');
// 		if ($month_to === false) $month_to = '12';
// 		$day = '31';
// 		if ($month_to == date('m')) $day = date('d');

// 		$date_from = "$year-$month_from-01 00:00:00";
// 		$date_to = "$year-$month_to-$day 23:59:59";

// 		if ($year == date('Y') && $month_to >= date('m')) $date_to = date('Y-m-d H:i:s', time());

// 		return Db::get()->es_invoices()->where('state', self::PAID)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	/**
// 	 * ## Prijem celkom
// 	 * - porovnanie s minulym rokom v percentach
// 	 * - zaplatene FA
// 	 */
// 	public function getTotalIncomePercentageGrowth($last_year = false, $current_year = false)
// 	{
// 		if (!$last_year) $lastYearIncome = \parseFloat($this->getTotalIncomeLastYear());
// 		if (!$current_year) $currentIncome = \parseFloat($this->getTotalIncomeCurrentYear());

// 		$lastYearIncome = \parseFloat($this->getTotalIncome($last_year));
// 		$currentIncome  = \parseFloat($this->getTotalIncome($current_year));

// 		if ($lastYearIncome < $currentIncome)
// 			$result['rises'] = whoolPrice(100 * (($currentIncome - $lastYearIncome) / $currentIncome), 2);

// 		if ($lastYearIncome > $currentIncome)
// 			$result['lower'] = whoolPrice(100 * (($lastYearIncome - $currentIncome) / $lastYearIncome), 2);

// 		if ($lastYearIncome === $currentIncome)
// 			$result['equal'] = 0;

// 		return $result;
// 	}

// 	public function getExpectedIncomeLastYearGrowth()
// 	{
// 		$year = date('Y', strtotime("-1 year"));

// 		$date_from = "$year-01-01 00:00:00";
// 		$date_to = "$year-12-31 23:59:59";

// 		return Db::get()->es_invoices('state', self::PENDING)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}

// 	public function getExpectedIncomeCurrentYearGrowth()
// 	{
// 		$year = date('Y');

// 		$date_from = "$year-01-01 00:00:00";
// 		$date_to = "$year-12-31 23:59:59";

// 		return Db::get()->es_invoices('state', self::PENDING)->where("created_at >= '$date_from' AND created_at <= '$date_to'")->sum('total');
// 	}


// 	/**
// 	 * ## Ocakavany prijem
// 	 * - porovnanie s minulym rokom v percentach
// 	 * - vsetky obj.
// 	 */
// 	public function getExpectedIncomePercentageGrowth($last_year = false, $current_year = false)
// 	{

// 		if (!$last_year) $lastYearIncome = \parseFloat($this->getExpectedIncomeLastYearGrowth());
// 		if (!$current_year) $currentIncome = \parseFloat($this->getExpectedIncomeCurrentYearGrowth());

// 		$lastYearIncome = \parseFloat($this->getExpectedIncome($last_year));
// 		$currentIncome  = \parseFloat($this->getExpectedIncome($current_year));

// 		if ($lastYearIncome < $currentIncome)
// 			$result['rises'] = whoolPrice(100 * (($currentIncome - $lastYearIncome) / $currentIncome), 2);

// 		if ($lastYearIncome > $currentIncome)
// 			$result['lower'] = whoolPrice(100 * (($lastYearIncome - $currentIncome) / $lastYearIncome), 2);

// 		if ($lastYearIncome === $currentIncome)
// 			$result['equal'] = whoolPrice(0, 2);

// 		return $result;
// 	}
// }
