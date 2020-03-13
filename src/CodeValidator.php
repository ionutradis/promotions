<?php

namespace ionutradis\promotions;

class CodeValidator
{
    private $data;

    var $failed = false;
    var $error = '';
    var $allowedTo = [];

    public function __construct($model) {
        $this->data = ($model);
        if(null == $this->data) {
            $this->failed = true;
            $this->error = 'code not found';
        } else {
            $this->checkAvailability();
            $this->getUsers();
        }
    }

    public function is_valid() {
        return (!$this->failed);
    }

    public function checkUser($userId) {
        if(count($this->allowedTo)>0) {
            if(in_array($userId, $this->allowedTo)) {
//                return true;
            } else {
                $this->failed = true;
                $this->error = 'User not allowed to claim this voucher code';
                return false;
            }
        } else {
            return null;
        }
    }

    private function checkAvailability() {
        if(strtotime($this->data['date_from']) > time()) {
            $this->error = 'code promotion not valid yet';
            $this->failed = true;
            return false;
        }

        if(strtotime($this->data['date_to']) < time()) {
            $this->error = 'code promotion validity expired';
            $this->failed = true;
            return false;
        }

        if($this->data['maximum_uses'] <= $this->data['current_uses']) {
            $this->error = 'exceeded max uses';
            $this->failed = true;
            return false;
        }
    }

    private function getUsers() {
        if(null !== $this->data['forUsers']) {
            $this->allowedTo = json_decode($this->data['forUsers']);
        } else {
            return false;
        }
    }
}
