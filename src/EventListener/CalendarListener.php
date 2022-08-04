<?php

declare(strict_types=1);

/*
 * This file is part of SocialImagesBundle.
 *
 * (c) Codefog
 *
 * @license MIT
 */

namespace Codefog\SocialImagesBundle\EventListener;

use Codefog\SocialImagesBundle\ImageCollector;
use Contao\CalendarEventsModel;
use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Contao\Module;
use Contao\ModuleEventMenu;
use Contao\ModuleEventReader;
use Contao\Template;

class CalendarListener
{
    private ImageCollector $imageCollector;

    public function __construct(ImageCollector $imageCollector)
    {
        $this->imageCollector = $imageCollector;
    }

    /**
     * @Hook("getAllEvents")
     */
    public function onGetAllEvents(array $events, array $calendars, int $start, int $end, Module $module): array
    {
        // Do not add images of events from the event menu
        if ($module instanceof ModuleEventMenu) {
            return $events;
        }

        $prepend = $module instanceof ModuleEventReader;

        foreach ($events as $v) {
            foreach ($v as $vv) {
                foreach ($vv as $vvv) {
                    $result = $this->imageCollector->addFromUuid($vvv['socialImage'], $prepend);

                    // Fallback to the main event image
                    if (!$result && $vvv['addImage']) {
                        $this->imageCollector->addFromUuid($vvv['singleSRC'], $prepend);
                    }
                }
            }
        }

        return $events;
    }

    /**
     * @Hook("parseTemplate")
     */
    public function onParseTemplate(Template $template): void
    {
        if (!str_starts_with('mod_eventreader', $template->getName())) {
            return;
        }

        $alias = Input::get(Config::get('useAutoItem') ? 'auto_item' : 'events', false, true);

        if (!$alias) {
            return;
        }

        $event = CalendarEventsModel::findByIdOrAlias($alias);

        if (null === $event) {
            return;
        }

        $result = $this->imageCollector->addFromUuid($event->socialImage, true);

        // Fallback to the main event image
        if (!$result && $event->addImage) {
            $this->imageCollector->addFromUuid($event->singleSRC, true);
        }
    }
}
