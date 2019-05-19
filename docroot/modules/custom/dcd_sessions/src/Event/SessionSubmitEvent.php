<?php

namespace Drupal\dcd_sessions\Event;

use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class SessionSubmitEvent.
 *
 * @package Drupal\dcd_sessions\Event\SessionSubmitEvent
 */
class SessionSubmitEvent extends Event {

  const SESSION_SUBMIT = 'dcd_sessions.entity.json';

  /**
   * Entity object.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Constructs a event on Entity/Session Insert.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object.
   */
  public function __construct(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Get the inserted entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Return Entity object.
   */
  public function getEntity() {
    return $this->entity;
  }

}
