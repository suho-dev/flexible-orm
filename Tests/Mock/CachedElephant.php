<?php
/**
 * @file
 * @author jarrod.swift
 */
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace FlexibleORMTests\Mock;
use Suho\FlexibleOrm\CachedORMModel;
/**
 * Description of CachedElephant
 *
 */
class CachedElephant extends CachedORMModel {
    const TABLE         = 'my_elephants';
    const PRIMARY_KEY   = 'name';
}
?>
