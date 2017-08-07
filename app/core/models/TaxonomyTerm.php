<?php
namespace module\core\models;

class TaxonomyTerm extends \yii\db\ActiveRecord
{
	const YELLOW_PAGE = 2;
	public $sub_items = array();

	public static function tableName()  
    {  
        return 'catalog_taxonomy_term';
    }

    public static function getTreeNav()
	{
		$terms = self::find()
			->orderBy(['parent_id'=>SORT_ASC, 'sort_order'=>SORT_ASC])
			->where('taxonomy_id=:id and status=0', [':id'=>self::YELLOW_PAGE])
			->all();

        $parents = [];
        $data = array();
		foreach($terms as $m) {
            if($m->parent_id == '0') {
                $parents[] = $m->id;
            }
            else {
                if(! in_array($m->parent_id, $parents)) {
                    continue;
                }
            }

			$data[] = $m->attributes;
		}
		
		return \module\core\helpers\DataFormatHelper::toTree($data, 'id', 'name', 'parent_id');
	}
}