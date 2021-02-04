<?php

namespace OpenLivePlugin\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;
use DrpPlugin\Biz\AgencyBindRelation\Service\RelationService;

class MyTeachingNavSlot extends SlotInjection
{
    public function inject()
    {
        $filter = $this->filter;

        return $this->container->get('twig')->render('OpenLivePlugin:slot:my-teaching-nav-menu.html.twig', [
            'filter' => $filter
        ]);
    }

    /**
     * @return RelationService
     */
    protected function getAgencyBindRelationService()
    {
        return $this->getBiz()->service('DrpPlugin:AgencyBindRelation:RelationService');
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}