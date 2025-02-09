<?php

namespace Sunnysideup\SimpleTemplateCaching\Extensions;

use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;

class SimpleTemplateCachingSiteConfigExtension extends DataExtension
{
    private static $db = [
        'CacheKeyLastEdited' => 'DBDatetime',
        'ClassNameLastEdited' => 'Varchar(200)',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Caching',
            [
                DatetimeField::create('CacheKeyLastEdited', 'Content Last Edited')
                    ->setRightTitle('The frontend template cache will be invalidated every time this value changes.'),
                ReadonlyField::create('ClassNameLastEdited', 'Last class updated')
                    ->setRightTitle('If a class is being updated too often then you can exclude it.'),
            ]
        );
    }

    public static function site_cache_key(): string
    {
        $obj = SiteConfig::current_site_config();

        return (string) 'ts_'.strtotime($obj->CacheKeyLastEdited);
    }

    public static function update_cache_key(?string $className = '')
    {
        DB::query('
            UPDATE "SiteConfig"
            SET
                "CacheKeyLastEdited" = \'' . DBDatetime::now()->Rfc2822() . '\',
                "ClassNameLastEdited" = \'' . $className. '\'
        ;');
    }
}
