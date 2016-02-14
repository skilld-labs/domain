<?php

/**
 * @file
 * Contains \Drupal\domain_access\Plugin\views\field\DomainAccessField.
 */

namespace Drupal\domain_access\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\Field;

/**
 * Field handler to present the link an entity on a domain.
 *
 * @ViewsField("domain_access_field")
 */
class DomainAccessField extends Field {

  /**
   * {@inheritdoc}
   */
  public function getItems(ResultRow $values) {
    // Override the default link generator, which wants to send us to the entity
    // page, not the node we are looking at.
    if ($this->options['render_as_link']) {
      $items = parent::getItems($values);
      foreach ($items as &$item) {
        $object = $item['raw'];
        $entity = $object->getEntity();
        $url = $entity->toUrl()->toString();
        $domain = $item['rendered']['#options']['entity'];
        $item['rendered']['#type'] = 'markup';
        $item['rendered']['#markup'] = '<a href="' . $domain->buildUrl($url) . '">' . $domain->label() . '</a>';
      }
      uasort($items, array($this, 'sort'));
    }

    return $items;
  }

  /**
   * Sort the domain list, if possible.
   */
  private function sort($a, $b) {
    $domainA = isset($a['rendered']['#options']['entity']) ? $a['rendered']['#options']['entity'] : 0;
    $domainB = isset($b['rendered']['#options']['entity']) ? $b['rendered']['#options']['entity'] : 0;
    if ($domainA !== 0) {
      return $domainA->getWeight() > $domainB->getWeight();
    }
    // We don't have a domain object so sort as best we can.
    return $a['rendered']['#plain_text'] > $b['rendered']['#plain_text'];
  }

}
