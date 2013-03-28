<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Exceptions;

/**
 * When doing complex fetches with multiple classes, it is possible that
 * one of the related classes cannot be found.
 * 
 * In this case this exception will be thrown
 *
 */
class ORMFetchIntoRelatedClassNotFoundException extends ORMFetchIntoException {
}