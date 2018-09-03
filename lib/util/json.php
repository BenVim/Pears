<?php

namespace lib\core;

class Json implements CodecInterface
{
    public static function encode($type, $sequence, $data)
    {
        return json_encode([
            't' => $type,
            'd' => $data,
            'p' => $sequence
        ]);
    }

    public static function decode($data)
    {
        $data = @json_decode($data);
        if (!isset($data->t) || !isset($data->d)) {
            return;
        }
        return [
            'type' => $data->t,
            'data' => $data->d
        ];
    }
}