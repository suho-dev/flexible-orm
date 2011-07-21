<?php
/**
 * @file
 * @author jarrod.swift
 */
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ORM\Tests\Mock;
use ORM\CachedORMModel;
/**
 * Description of CachedCar
 *
 */
class CachedCar extends CachedORMModel {
    const TABLE     = 'cars';
    const FOREIGN_KEY_MANUFACTURER = 'brand';
    const FOREIGN_KEY_CACHEDOWNER = 'owner_id';
}
?>
