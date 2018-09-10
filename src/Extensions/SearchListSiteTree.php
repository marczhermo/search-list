<?php

namespace Marcz\Search\Extenstions;

use SiteTreeExtension;

class SearchListSiteTree extends SiteTreeExtension
{
    /**
     * Hook called after the page's {@link Versioned::publishSingle()} action is completed
     *
     * @param SiteTree &$original The current Live SiteTree record prior to publish
     */
    public function onAfterPublish(&$original)
    {
        parent::onAfterPublish($original);
    }

    /**
     * Hook called after the page's {@link SiteTree::doUnpublish()} action is completed
     */
    public function onAfterUnpublish()
    {
        parent::onAfterUnpublish();
    }
}
