<?php

namespace Sunnysideup\SimpleTemplateCaching\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Versioned\Versioned;

class DataObjectExtension extends DataExtension
{
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $owner = $this->getOwner();
        $className = $owner->ClassName;

        // NB.
        // if the dataobject has the versioned extension then the cache should be invalidated onAfterPublish
        // hasStages function is part of the Versioned class so safe to check here
        if (! $owner->hasExtension(Versioned::class)) {
            $this->doUpdateCache($className);
        } elseif (! $owner->hasStages()) {
            $this->doUpdateCache($className);
        }
    }

    public function onAfterDelete()
    {
        parent::onAfterDelete();
        $this->doUpdateCache();
    }

    //* this function needs further consideration as it is called many times on the front end */
    // public function updateManyManyComponents()
    // {
    //     $owner = $this->owner;
    //     $className = $owner->ClassName;

    //     if(!$owner->hasExtension(Versioned::class)){
    //         $this->doUpdateCache($className);
    //     }
    //     //if the dataobject has the versioned extension then the cache should be invalidated onAfterPublish
    //     else if (!$owner->hasStages()){
    //         $this->doUpdateCache($className);
    //     }
    // }

    public function onBeforeRollback()
    {
        $this->doUpdateCache();
    }

    public function onAfterPublish()
    {
        $this->doUpdateCache();
    }

    public function onAfterArchive()
    {
        $this->doUpdateCache();
    }

    public function onAfterUnpublish()
    {
        $this->doUpdateCache();
    }

    public function onAfterVersionedPublish()
    {
        $this->doUpdateCache();
    }

    public function onAfterWriteToStage($toStage)
    {
        $this->doUpdateCache();
    }

    private function doUpdateCache()
    {
        $className = (string) $this->getOwner()->ClassName;
        if ($className && $this->canUpdateCache($className)) {
            SimpleTemplateCachingSiteConfigExtension::update_cache_key($className);
        }
    }

    private function canUpdateCache($className): bool
    {
        $excludedClasses = Config::inst()->get(self::class, 'excluded_classes');

        return ! in_array($className, $excludedClasses, true);
    }
}
