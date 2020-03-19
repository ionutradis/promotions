<?php

namespace ionutradis\promotions;


class CodeCalculator
{
    var $data;
    var $voucherRules = [];
    var $allowedProducts = [];
    var $allowedProductCategories = [];
    var $qualifiedProducts = [];
    var $allowAllProducts = false;

    public function __construct($model)
    {
        $this->data = ($model);
        if($this->data !== null) {
            $this->getProducts();
            $this->getCategories();
            $this->voucherRules = ['type' => $this->data['type'], 'value' => $this->data['value']];
        }
    }

    public function calculateProduct($productParams) {
        $initialProductPrice = $productParams['price'];
        $productId = $productParams['product_id'];
        $productQuantity = $productParams['quantity'];
        if(count($this->allowedProducts) > 0) {
            $allowedProducts = $this->allowedProducts;
            $allowedProductQty = $allowedProducts[$productId]['qty'];
        } else {
            $allowedProductQty = 1;
        }

        if(($productQuantity <= $allowedProductQty)) {
            $quantityInject = $productQuantity;
        } else {
            $quantityInject = $allowedProductQty;
            $item['non_promo'] = ['quantity' => ($productQuantity-$allowedProductQty), 'price' => $initialProductPrice];
        }

        switch($this->voucherRules['type']) {
            case 'fixed':
                $reduceBy = $this->voucherRules['value'];
                $item['promo'] = ['quantity' => $quantityInject, 'price' => $initialProductPrice-$reduceBy];
                break;
            case 'percentage':
                $reduceBy = $initialProductPrice/100*$this->voucherRules['value'];
                $item['promo'] = ['quantity' => $quantityInject, 'price' => $initialProductPrice - $reduceBy];
                break;
            case 'override':
                $reduceBy = $initialProductPrice - $this->voucherRules['value'];
                $item['promo'] = ['quantity' => $quantityInject, 'price' => $this->voucherRules['value']];
                break;
        }

        $item['totalReduced'] = ($item['promo']['quantity']*$initialProductPrice) - ($item['promo']['quantity']*$item['promo']['price']);
        return $item;
    }

    public function checkProducts($products) {
        if(count($products)>0) {
            if(in_array_any($products, array_keys($this->allowedProducts))) {
                $this->qualifiedProducts = array_intersect($products, array_keys($this->allowedProducts));
            }
        } else {
            return false;
        }
    }

    private function getProducts() {
        if(isset($this->data['products']) && null !== $this->data['products']) {
            $this->allowedProducts = json_decode($this->data['products'], 1);
        } else {
            return false;
        }
    }

    public function addPreparedProducts($products) {
        $this->allowedProducts = array_replace_recursive($products, $this->allowedProducts);
    }

    private function getCategories() {
        if(isset($this->data['product_categories']) && null !== $this->data['product_categories']) {
            $this->allowedProductCategories = json_decode($this->data['product_categories'], 1);
        } else {
            return false;
        }
    }
}
