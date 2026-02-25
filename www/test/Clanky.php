<?php

class Clanky {
    public static function generujClanky(int $pocet): array {
        $data = json_decode(file_get_contents('posts.json'));
        return array_slice($data, 0, $pocet);
    }

    public static function getClanky($pocet) {
        $instance = new self();
        return array_slice($instance->clanky, 0, $pocet);
    }
}

class Clanek {
    public static int $lastId = 0;
    private string $titulek, $obsah;
    private int $id;

    public function __construct(int $id = 0) {
        $this->titulek = "Nenastaveno";
        $this->obsah = "Nebylo nic napsáno";
        if ($id > 0) {
            $this->id = $id;
        } else {
            $this->id = ++self::$lastId;
        }
    }

    public function get($name) {}
}










/*
public function __construct() {
    $this->clanky = json_decode(file_get_contents('posts.json'), true);
}*/