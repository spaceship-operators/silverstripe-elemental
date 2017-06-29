<?php

namespace DNADesign\Elemental\Extensions;

use SilverStripe\ORM\DataExtension;


/**
 * @package elemental
 */
class ElementDuplicationExtension extends DataExtension
{

    /**
     * Duplicate items
     *
     */
    public function onAfterDuplicate($original, $doWrite=true)
    {
        $thisClass = $this->owner->ClassName;

        // Duplicate has_one's and has_many's
        $duplicateRelations = Config::inst()->get($thisClass, 'duplicate_relations');
        if ($duplicateRelations && !empty($duplicateRelations)) {
            foreach ($duplicateRelations as $relation) {
                $items = $original->$relation();
                foreach ($items as $item) {
                    $duplicateItem = $item->duplicate(false);
                    $duplicateItem->{$thisClass.'ID'} = $this->owner->ID;
                    $duplicateItem->write();
                }
            }
        }

        // Duplicate many_many's
        $duplicateManyManyRelations = Config::inst()->get($thisClass, 'duplicate_many_many_relations');
        if($duplicateManyManyRelations && !empty($duplicateManyManyRelations)) {
            foreach($duplicateManyManyRelations as $relation) {
                $items = $original->$relation();
                foreach($items as $item) {
                    $this->owner->$relation()->add($item);
                }
            }
        }
    }

    public function onBeforeDuplicate($original, $doWrite=true)
    {
        $thisClass = $this->owner->ClassName;
        $clearRelations = Config::inst()->get($thisClass, 'duplicate_clear_relations');

        if ($clearRelations && !empty($clearRelations)) {
            foreach ($clearRelations as $clearRelation) {
                $clearRelation = $clearRelation . 'ID';
                $this->owner->$clearRelation = 0;
            }
        }
    }
}
