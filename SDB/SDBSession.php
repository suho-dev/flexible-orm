<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;

/**
 * A single session
 *
 */
class SDBSession extends ORMModelSDB {
    public $name;
    public $data;
    public $lastModifiedTime;

    public function beforeSave() {
        $this->lastModifiedTime = time();
    }
}