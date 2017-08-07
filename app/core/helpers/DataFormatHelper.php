<?php
namespace module\core\helpers;

class DataFormatHelper
{
	public static function toTree($data, $idField, $nameField, $parentIdField)
	{
		$treeAllItems = array();
        foreach($data as $item) {
            $id = intval($item[$idField]);
            $parentId = intval($item[$parentIdField]);
            if($parentId === 0) {
                $treeAllItems[$id] = $item;
                $treeAllItems[$id]['items'] = array();
            }
            else {
                if(! isset($treeAllItems[$parentId])) 
                    $treeAllItems[$parentId] = array();
                $treeAllItems[$parentId]['items'][] =  $item;
            }
        }
        
        $treeItems = array();
        foreach($treeAllItems as $mainCategory) {
            $subCategoryItems = array();
            foreach($mainCategory['items'] as $subCategory) {
                $subCategoryItems[] = array(
                    'id'=>intval($subCategory[$idField]), 
                    'name'=>$subCategory[$nameField],
                    'name_zh'=>$subCategory[$nameField.'_zh'],
                    'items'=>array(),
                    'parentId'=>$subCategory[$parentIdField],
                    'typeId'=>$subCategory['taxonomy_id']
                );
            }

            $treeItems[] = array(
                'id'=>intval($mainCategory[$idField]),
                'name'=>$mainCategory[$nameField],
                'name_zh'=>$mainCategory[$nameField.'_zh'],
                'items'=>$subCategoryItems,
                'parentId'=>$mainCategory[$parentIdField],
                'typeId'=>$mainCategory['taxonomy_id']
            );
        }
        return $treeItems;
	}
}