<?php
/**
 * Database configuration and connection functions
 */

// Database connection details are loaded from .env by config.php
// The getConnection() function is already defined in config.php

/**
 * Execute a query and return the results
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
function executeQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a query and return a single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|false Query result or false if no rows
 */
function executeQuerySingle($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Execute a query and return the number of affected rows
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return int Number of affected rows
 */
function executeNonQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Get the last inserted ID
 * 
 * @return string Last inserted ID
 */
function getLastInsertId() {
    $conn = getConnection();
    return $conn->lastInsertId();
}
