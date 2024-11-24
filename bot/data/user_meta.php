<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
use losthost\DB\DB;

class user_meta extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'name' => 'VARCHAR(128) NOT NULL',
        'value' => 'TEXT',
        'PRIMARY KEY' => 'id',
    ];
    
    static public function get(int $user_id, string $name, ?string $default=null) : string {
        $meta = new user_meta(['user_id' => $user_id, 'name' => $name], true);
        if ($meta->isNew()) {
            $meta->value = $default;
            $meta->write();
        }
        return $meta->value;
    }
    
    static public function set(int $user_id, string $name, string $value) {
        $meta = new user_meta(['user_id' => $user_id, 'name' => $name], true);
        $meta->value = $value;
        $meta->write();
        return $value;
    }
    
    static public function isPaid(int $user_id) {
        $paid = static::get($user_id, 'paid_till');
        if ($paid == 'forever') {
            return true;
        } elseif ($paid == null) {
            return false;
        } elseif ($paid >= \date_create()->format(DB::DATE_FORMAT)) {
            return true;
        }
        return false;
    }
    
    static public function setPaidTill(int $user_id, \DateTime|\DateTimeImmutable|null|true $paid_till) {
        if (is_null($paid_till)) {
            static::set($user_id, 'paid_till', null);
        } elseif ($paid_till === true) {
            static::set($user_id, 'paid_till', 'forever');
        } else {
            static::set($user_id, 'paid_till', $paid_till->format(DB::DATE_FORMAT));
        }
    }
    
}
