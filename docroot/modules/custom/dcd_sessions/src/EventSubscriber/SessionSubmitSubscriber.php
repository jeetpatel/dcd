<?php

namespace Drupal\dcd_sessions\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\dcd_sessions\Event\SessionSubmitEvent;
use Drupal\dcd_sessions\Helper\SessionHelper;

/**
 * Class SessionSubmitSubscriber.
 *
 * @package Drupal\dcd_sessions\SessionSubmitSubscriber
 */
class SessionSubmitSubscriber implements EventSubscriberInterface {

  /**
   * Entity Session Submit handler.
   *
   * @param \Drupal\dcd_sessions\Event\SessionSubmitEvent $event
   *   Event object.
   */
  public function sessionSubmitHandler(SessionSubmitEvent $event) {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() == 'node' && $entity->in_preview == NULL) {
      // If status is published.
      if ($entity->status->value == 1) {
        // Add json write handler.
        switch ($entity->getType()) {
          case 'session':
            $helper = \Drupal::service('dcd_sessions.helper');
            $helper->sendSessionSubmitedEmail($entity);
            break;

          default:
            break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SessionSubmitEvent::SESSION_SUBMIT][] = ['sessionSubmitHandler'];
    return $events;
  }

}
