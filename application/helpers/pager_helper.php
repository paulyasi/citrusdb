<?php
/*
 * ----------------------------------------------------------------------------
 *  count items on pages
 * ----------------------------------------------------------------------------
 */
function getPagerData($numHits, $limit, $page) 
{ 
	$numHits  = (int) $numHits; 
	$limit    = max((int) $limit, 1); 
	$page     = (int) $page; 
	$numPages = ceil($numHits / $limit); 
	
	$page = max($page, 1); 
	$page = min($page, $numPages); 
	
	$offset = ($page - 1) * $limit; 
	
	$ret = new stdClass; 
	
	$ret->offset   = $offset; 
	$ret->limit    = $limit; 
	$ret->numPages = $numPages; 
	$ret->page     = $page; 
	
	return $ret; 
}
?>