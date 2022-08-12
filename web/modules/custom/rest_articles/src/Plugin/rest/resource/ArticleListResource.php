<?php

namespace Drupal\rest_articles\Plugin\rest\resource;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\file\FileInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a Article List Resource.
 *
 * @RestResource(
 *   id = "article_list",
 *   label = @Translation("Article list"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/article-list"
 *   }
 * )
 */
class ArticleListResource extends ResourceBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * File URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Cacheable metadata that will be added to the response.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected CacheableMetadata $cacheableMetadata;

  /**
   * ArticleListResource constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   File URL generator service.
   * @param mixed ...$parent_parameters
   *   The parent class parameters.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    FileUrlGeneratorInterface $file_url_generator,
    ...$parent_parameters
  ) {
    parent::__construct(...$parent_parameters);
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
    $this->cacheableMetadata = new CacheableMetadata();
    $this->cacheableMetadata->setCacheTags(['node_list:article']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest')
    );
  }

  /**
   * Returns article list as a JSON.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse|\Drupal\rest\ResourceResponse
   *   Json response with articles.
   */
  public function get(): CacheableJsonResponse|ResourceResponse {
    $response = new CacheableJsonResponse($this->fetchArticles(), Response::HTTP_OK);
    $response->addCacheableDependency($this->cacheableMetadata);

    return $response;
  }

  /**
   * Fetches articles from CMS.
   *
   * @return array
   *   Numerical array of articles data.
   */
  protected function fetchArticles(): array {
    $articles = [];
    $image_style = $this->loadImageStyle();

    foreach ($this->loadArticleNodes() as $article) {
      $tag = $article->getTag();
      $image = $article->getImage();

      $this->addCacheableMetadataFromEntity($article);
      $this->addCacheableMetadataFromEntity($tag);
      $this->addCacheableMetadataFromEntity($image);

      $articles[] = [
        'id' => $article->id(),
        'path' => $article->getPath(),
        'title' => $article->getTitle(),
        'body' => $article->getDescription(),
        'image' => $this->applyImageStyle($image, $image_style),
        'tag' => $tag?->label(),
      ];
    }

    return $articles;
  }

  /**
   * Loads article node entities.
   *
   * @return \Drupal\rest_articles\Entity\Article[]
   */
  protected function loadArticleNodes(): array {
    try {
      return $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties([
          'type' => 'article',
        ]);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      $this->logger->error($e->getMessage());
      return [];
    }
  }

  /**
   * Loads image style entity.
   *
   * @param string $image_style_id
   *   Image style name.
   *
   * @return \Drupal\image\ImageStyleInterface|null
   *   Loaded image style entity or NULL in case of an error.
   */
  protected function loadImageStyle(string $image_style_id = 'article'): ?ImageStyleInterface {
    try {
      /** @var \Drupal\image\ImageStyleInterface $image_style */
      $image_style = $this->entityTypeManager
        ->getStorage('image_style')
        ->load($image_style_id);
      $this->addCacheableMetadataFromEntity($image_style);

      return $image_style;
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      $this->logger->error($e->getMessage());
      return NULL;
    }
  }

  /**
   * Applies provided image style to the image file.
   *
   * @param \Drupal\file\FileInterface|null $image_file
   *   File entity.
   * @param \Drupal\image\ImageStyleInterface|null $image_style
   *   Image style entity.
   *
   * @return string|null
   *   Relative URL address of a styled image or NULL if file or image style is missing.
   */
  protected function applyImageStyle(?FileInterface $image_file, ?ImageStyleInterface $image_style): ?string {
    if (!$image_file || !$image_style) {
      return NULL;
    }

    $uri = $image_file->getFileUri();
    $styled_image_url = $image_style->buildUrl($uri);
    return $this->fileUrlGenerator->transformRelative($styled_image_url);
  }

  /**
   * Adds cacheable metadata from the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   */
  protected function addCacheableMetadataFromEntity(?EntityInterface $entity): void {
    if (!$entity) {
      return;
    }

    $metadata = CacheableMetadata::createFromObject($entity);
    $this->cacheableMetadata = $this->cacheableMetadata->merge($metadata);
  }

}
