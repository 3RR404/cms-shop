<?php

namespace Weblike\Cms\Shop;

use Weblike\Cms\Shop\Interfaces\IResponse;

class Response implements IResponse
{
	/** @var string */
	protected $message;

	/** @var null|int */
	protected $code;

	/** @var string */
	protected $type;

	/** @var null|string */
	protected $data;

	function __construct(?string $message = '', ?string $type = null, ?int $code = 200, ?string $data = null)
	{
		$this->message = $message;
		$this->code = $code;
		$this->type = $type;
		$this->data = $data;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	public function getCode(): ?int
	{
		return $this->code;
	}

	public function getType(): ?string
	{
		return $this->type;
	}

	public function getData(): ?string
	{
		return $this->data ?? null;
	}
}
