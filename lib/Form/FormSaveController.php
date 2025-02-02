<?php

namespace MailPoet\Form;

use MailPoet\Entities\FormEntity;
use MailPoet\WP\Functions as WPFunctions;
use MailPoetVendor\Carbon\Carbon;
use MailPoetVendor\Doctrine\ORM\EntityManager;

class FormSaveController {
  /** @var EntityManager */
  private $entityManager;

  /** @var WPFunctions */
  private $wp;

  public function __construct(
    EntityManager $entityManager,
    WPFunctions $wp
  ) {
    $this->entityManager = $entityManager;
    $this->wp = $wp;
  }

  public function duplicate(FormEntity $formEntity): FormEntity {
    $duplicate = clone $formEntity;
    $duplicate->setName(sprintf(__('Copy of %s', 'mailpoet'), $formEntity->getName()));

    // reset timestamps
    $now = Carbon::createFromTimestamp($this->wp->currentTime('timestamp'));
    $duplicate->setCreatedAt($now);
    $duplicate->setUpdatedAt($now);
    $duplicate->setDeletedAt(null);

    $this->entityManager->persist($duplicate);
    $this->entityManager->flush();

    return $duplicate;
  }
}
