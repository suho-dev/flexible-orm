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
 * Description of CachedElephant
 *
 */
class CachedElephant extends CachedORMModel {
    const TABLE         = 'my_elephants';
    const PRIMARY_KEY   = 'name';
}
?>
