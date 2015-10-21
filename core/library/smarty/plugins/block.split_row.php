<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
  Distributed under the terms of the BSD Licence:
  Copyright (c) 2007, Internet Brands, Inc (www.internetbrands.com)

  All rights reserved.

  Redistribution and use in source and binary forms, with or without 
  modification, are permitted provided that the following conditions are met:

      * Redistributions of source code must retain the above copyright notice, this list of 
        conditions and the following disclaimer.
      * Redistributions in binary form must reproduce the above copyright notice, 
        this list of conditions and the following disclaimer in the documentation and/or other 
        materials provided with the distribution.
      * Neither the name of the Internet Brands nor the names of its contributors may be used 
        to endorse or promote products derived from this software without specific prior 
        written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
  PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
  PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
  LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * Smarty {split}{/split} block plugin
 *
 * Type:     block function<br>
 * Name:     split<br>
 * Purpose:  loop structure that splits an array into rows to allow for column formatting
 * @param array
 * <pre>
 * Params:   count: number of columns
 *           from: source array
 *           name: smarty param to assign chunks to
 * </pre>
 * @author Kevin Sours (kevin.sours@internetbrands.com)
 *         Internet Brands (http://ibbydev.blogspot.com/)
 * @param string contents of the block
 * @param Smarty 
 * @return string $content - smarty content is not modified.
 */
function smarty_block_split_row($params, $content, &$smarty, &$repeat) {
  static $sanity = 0;
  if (!array_key_exists('from', $params)) {
    $smarty->trigger_error("split_row: missing 'from' parameter", E_USER_WARNING);
    $repeat = false;
    return;
  }

  //if the source var isn't set, quietly treat it as empty.
  if(is_null($params['from'])) {
    $repeat = false;
    return;
  }

  if (!is_array($params['from'])) {
    $smarty->trigger_error("split_row: 'from' parameter must be an array", E_USER_WARNING);
    $repeat = false;
    return;
  }

  if (!isset($params['item'])) {
    $smarty->trigger_error("split_row: missing 'item' parameter", E_USER_WARNING);
    $repeat = false;
    return;
  }
  
  $count = 2;
  if (isset($params['count'])) {
    $count = $params['count'];
  }

  $name = $params['item'];

  $indexName = "smarty.IB.split_row.$name.index";
  $index = $smarty->getTemplateVars($indexName);
  if(!$index) {
    $index = 0;
  }
  $lastName = "smarty.IB.split_row.$name.last";
  
  $source = $params["from"];
  if(is_null($content)) {
    split_row_helper_doslice(&$smarty, $name, $source, $index, $count);
    $index++;
    $smarty->assign($indexName, $index);
  }
  else {
    if($index >= ceil(count($source)/$count)) {
      $smarty->clearAssign($indexName);   
      $smarty->clearAssign($name);   
    }
    else {
      split_row_helper_doslice(&$smarty, $name, $source, $index, $count);
      $index++;
      $smarty->assign($indexName, $index);
      $repeat = true;
    }

    if($sanity > 10) {
      $repeat = false;
    }
    $sanity++;
    
    return $content;
  }
}

function split_row_helper_doslice(&$smarty, $name, $source, $index, $columns) {
  $colsize = ceil(count($source)/$columns);
  $row = array();
  for($i = 0; $i< $columns; $i++) {
    $item = array_slice($source, ($i * $colsize) + $index, 1, false);
    $row = array_merge($row, $item);
  }
  $smarty->assign($name, $row); 
}