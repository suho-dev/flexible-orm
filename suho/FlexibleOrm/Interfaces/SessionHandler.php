<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Interfaces;

/**
 * Simple interface to define a session handler
 * 
 * This exists mainly to allow mocking for testing
 * 
 * @author jarrod.swift
 */
interface SessionHandler {
    public function open( $savePath, $sessionName );

    public function close();

    public function read( $id );

    public function write( $id, $data );

    public function destroy( $id );

    public function gc( $max_expire_time );
}
