<?php

namespace App\Utils;

class ArrayUtils
{
   public static function verifyAttributes(array $attributesPlayer): bool {
        if (!is_array($attributesPlayer)) {
            return false;
        }
        foreach ($attributesPlayer as $key => $value) {
            if (!is_string($key) || !is_int($value)) {
                return false;
            }
        }
        return true;
   }
}