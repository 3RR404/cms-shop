<?php

namespace Weblike\Cms\Shop\Interfaces;

interface IResponse
{

	public function getCode(): ?int;

	public function getMessage(): ?string;

	public function getType(): ?string;

	public function getData(): ?string;
}
