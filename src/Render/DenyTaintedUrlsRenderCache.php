<?php

namespace Drupal\deny_tainted_urls\Render;

use Drupal\Core\Render\RenderCacheInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Decorates the render cache service to deny caching on any undesired URLs.
 */
class DenyTaintedUrlsRenderCache implements RenderCacheInterface {

  /**
   * The decorated render cache service.
   *
   * @var \Drupal\Core\Render\RenderCacheInterface
   */
  protected $inner;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a DenyTaintedUrlsRenderCache decorator.
   *
   * @param \Drupal\Core\Render\RenderCacheInterface $inner
   *   The render cache service being decorated.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RenderCacheInterface $inner, RequestStack $request_stack) {
    $this->inner = $inner;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableRenderArray(array $elements) {
    return $this->inner->getCacheableRenderArray($elements);
  }

  /**
   * {@inheritdoc}
   */
  public function get(array $elements) {
    return $this->inner->get($elements);
  }

  /**
   * {@inheritdoc}
   */
  public function set(array &$elements, array $pre_bubbling_elements) {
    return $this->allowCacheSet()
      ? $this->inner->set($elements, $pre_bubbling_elements)
      : FALSE;
  }

  /**
   * Gets whether the cache should be populated for the current request.
   *
   * @return bool
   *   TRUE if the cache should be populated for the current request.
   */
  protected function allowCacheSet() {
    $base = $this->requestStack
      ->getCurrentRequest()
      ->getBaseUrl();

    return (
      // No tilde preview.
      strpos($base, '/~') !== 0 &&
      // No index.php.
      strpos($base, '/index.php') === FALSE
    );
  }

}
