<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Interfaces;

/**
 *
 * @author jarrodswift
 */
interface RequestData {
    public function __construct( array $get = array(), array $post = array(), array $cookies = array() );
}

