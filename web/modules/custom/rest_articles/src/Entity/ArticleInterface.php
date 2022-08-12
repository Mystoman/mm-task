<?php

namespace Drupal\rest_articles\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;

/**
 * Provides an interface defining a node entity of a "article" bundle..
 */
interface ArticleInterface extends NodeInterface {

  /**
   * Gets value of a body field if there is one.
   *
   * @return string|null
   */
  public function getDescription(): ?string;

  /**
   * Sets new value to the body field.
   *
   * @param $value
   *   New value for body field.
   *
   * @return $this
   *   The called article node entity.
   */
  public function setDescription($value): static;

  /**
   * Gets value of the image field if there is one.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getImage(): ?EntityInterface;

  /**
   * Sets new value to the image field.
   *
   * @param $fid
   *   New file ID for the image field.
   *
   * @return $this
   *   The called article node entity.
   */
  public function setImage($fid): static;

  /**
   * Gets value of the tags field if there is one.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getTag(): ?EntityInterface;

  /**
   * Sets new value to the tags field.
   *
   * @param $tid
   *   New term ID for the tags field.
   *
   * @return $this
   *   The called article node entity.
   */
  public function setTag($tid): static;

  /**
   * Canonical URL address of the node.
   *
   * @return string|null
   */
  public function getPath(): ?string;

}
