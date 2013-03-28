<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\SDB;

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