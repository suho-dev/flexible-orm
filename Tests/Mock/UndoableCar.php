<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
use ORM\Undoable\UndoableModel;
/**
 * Description of AlternateCar
 *
 * A simple Model using the same database as Car but it is also "undoable"
 */
class UndoableCar extends UndoableModel {
    const TABLE     = 'cars';
}
?>
