<?php

// src/Entity/Task.php
namespace Customize\Entity;

class OptionItem
{
    protected $optionItem = '1';
    protected $product_1 = '1';
    protected $product_2 = '0';
    protected $from = '';
    protected $to = '';
    protected $des_price = '';
    protected $country_name_jp = '';

    public function getOptionItem(): string
    {
        return $this->optionItem;
    }

    public function setOptionItem(string $optionItem): void
    {
        $this->optionItem = $optionItem;
    }

    public function setProduct1(string $product_1): void
    {
        $this->product_1 = $product_1;
    }

    public function getProduct1(): string
    {
        return $this->product_1;
    }

    public function setProduct2(string $product_2): void
    {
        $this->product_2 = $product_2;
    }

    public function getProduct2(): string
    {
        return $this->product_2;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getCountryNameJP(): string
    {
        return $this->country_name_jp;
    }

    public function setCountryNameJP(string $country_name_jp): void
    {
        $this->country_name_jp = $country_name_jp;
    }

    public function getDesPrice(): string
    {
        return $this->des_price;
    }

    public function setDesPrice(string $des_price): void
    {
        $this->des_price = $des_price;
    }
}