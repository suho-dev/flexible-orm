<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace FlexibleORMTests\Mock;
use Suho\FlexibleOrm\Undoable\UndoableModel;
/**
 * Description of AlternateCar
 *
 * A simple Model using the same database as Car but it is also "undoable"
 */
class UndoableCar extends UndoableModel {
    const TABLE     = 'cars';
}
?>
