<?php

namespace ionutradis\promotions;

class CodeFactory
{
    private $length;
    private $data = [];
    private $failed = false;
    private $rules = [];

    public function setLengths($value) {
        $this->length = (filter_var($value, FILTER_VALIDATE_INT)) ? $value : null;
    }

    public function generate($predefined = false) {
        if($predefined) {
            $string = $predefined;
        } else {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $string = '';
            for ($i = 0; $i < $this->length; $i++) {
                $string .= $characters[rand(0, $charactersLength - 1)];
            }
        }
        $this->appendToData(['code' => $string]);
        return $string;
    }

    public function onlyFor($param) {
        switch (gettype($param)) {
            case 'integer':
                $this->setRule(['onlyFor' => $param]);
                break;
            case 'array':
                $this->setRule(['onlyFor' => ($param)]);
                break;
            default:
                die;
                break;
        }
    }

    public function redeems($integer = 0) {
        $this->setRule(['redeems' => $integer]);
    }

    public function minCartAmount($int = 0) {
        if($int !== 0 && is_numeric($int))
            $this->setRule(['minCartAmount' => $int]);
    }

    public function requiredProducts($param) {
        $this->setRule(['requiredProducts' => $param]);
    }

    private function setRule($param) {
        $this->rules = array_merge($this->rules, $param);
    }

    public function getRules() {
        return $this->rules;
    }

    public function appendToData($value) {
        $this->data = array_merge($this->data, $value);
    }

    public function getData() {
        return $this->data;
    }
}
