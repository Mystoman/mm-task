<?php

namespace Drupal\rest_articles\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\node\Entity\Node;

/**
 * Wrapper class for nodes of "article" type.
 */
class Article extends Node {

  /**
   * Value of a body field if there is one.
   *
   * @return string|null
   */
  public function description(): ?string {
    return $this->get('body')->value;
  }

  /**
   * Value of the image field if there is one.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function image(): ?EntityInterface {
    return $this->get('field_image')->entity;
  }

  /**
   * Value of the tags field if there is one.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function tag(): ?EntityInterface {
    return $this->get('field_tags')->entity;
  }

  /**
   * Canonical URL address of the node.
   *
   * @return string|null
   */
  public function path(): ?string {
    try {
      return $this->toUrl()->toString(TRUE)->getGeneratedUrl();
    }
    catch (EntityMalformedException) {
      return NULL;
    }
  }

}
