<?php

namespace App\Listeners;

use App\Events\PubSubExampleEvent;
use App\Events\PubSubAppEvent;
use App\Events\PubSubUnknownEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listen pub sub events.
 */
class PubSubEventSubscriber implements ShouldQueue
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            PubSubUnknownEvent::class,
            self::class . '@onPubSubUnknownEvent'
        );

        $events->listen(
            PubSubExampleEvent::class,
            self::class . '@onPubSubExampleEvent'
        );

        $events->listen(
            PubSubAppEvent::class,
            self::class . '@onPubSubAppEvent'
        );
    }

    /**
     * Handle event.
     *
     * @param PubSubUnknownEvent $event
     */
    public function onPubSubUnknownEvent(PubSubUnknownEvent $event)
    {
        logException(new Exception(
            "Unknown pubsub message, attributes : "
            . print_r($event->externalEvent()->attributes(), true)
            . ", data : "
            . substr(print_r($event->externalEvent()->data(), true), 0, 1024)
        ));
    }

    /**
     * Handle event.
     *
     * @param PubSubExampleEvent $event
     */
    public function onPubSubExampleEvent(PubSubExampleEvent $event)
    {
        if (app()->environment(['local', 'staging'])) {
            logger()->info(
                "Received PubSubExampleEvent, attributes : "
                . print_r($event->externalEvent()->attributes(), true)
                . ", data : "
                . substr(print_r($event->externalEvent()->data(), true), 0, 1024)
            );
        }
    }

    /**
     * Handle event.
     *
     * @param PubSubAppEvent $event
     */
    public function onPubSubAppEvent(PubSubAppEvent $event)
    {
        if (app()->environment(['local', 'staging'])) {
            logger()->info(
                "Received PubSubAppEvent, attributes : "
                . print_r($event->externalEvent()->attributes(), true)
                . ", data : "
                . substr(print_r($event->externalEvent()->data(), true), 0, 1024)
            );
        }
    }
}
