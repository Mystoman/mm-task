<?php

namespace Drupal\rest_articles\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\node\Entity\Node;

/**
 * Wrapper class for nodes of "article" bundle.
 */
class Article extends Node implements ArticleInterface {

  /**
   * {@inheritdoc}
   */
  public function getDescription(): ?string {
    return $this->get('body')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($value): static {
    $this->set('body', $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getImage(): ?EntityInterface {
    return $this->get('field_image')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setImage($fid): static {
    $this->set('field_image', $fid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTag(): ?EntityInterface {
    return $this->get('field_tags')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setTag($tid): static {
    $this->set('field_tags', $tid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath(): ?string {
    try {
      return $this->toUrl()->toString(TRUE)->getGeneratedUrl();
    }
    catch (EntityMalformedException) {
      return NULL;
    }
  }

}
