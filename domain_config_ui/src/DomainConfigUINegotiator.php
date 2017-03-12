<?php

namespace Drupal\domain_config_ui;

use Drupal\domain\DomainNegotiator;

/**
 * {@inheritdoc}
 */
class DomainConfigUINegotiator extends DomainNegotiator {

  /**
   * Semaphore to prevent negotiation infinite loop.
   *
   * @var bool
   */
  protected $negitiating = FALSE;

  /**
   * Set the current selected domain ID into session.
   *
   * @param string $domain_id
   *   The domain ID to store in session.
   */
  public function setSelectedDomain($domain_id) {
    if ($this->domainLoader->load($domain_id)) {
      $_SESSION['domain_config_ui']['config_save_domain'] = $domain_id;
    }
    else {
      $_SESSION['domain_config_ui']['config_save_domain'] = '';
    }
  }

  /**
   * Determine the active domain.
   *
   * @return \Drupal\domain\DomainInterface
   *   The domain entity.
   */
  protected function negotiateActiveDomain() {
    // Set http host to be that of the selected domain to configure.
    if ($selected_domain = $this->getSelectedDomain()) {
      $httpHost = $selected_domain->getHostname();
    }
    else {
      $httpHost = $this->negotiateActiveHostname();
    }
    $this->setRequestDomain($httpHost);
    return $this->domain;
  }

  /**
   * Get the selected domain.
   */
  public function getSelectedDomain() {
    $selected_domain_id = $this->getSelectedDomainId();
    if ($selected_domain_id && $selected_domain = $this->domainLoader->load($selected_domain_id)) {
      return $selected_domain;
    }
  }

  /**
   * Get the selected domain ID.
   *
   * @return string
   *   The selected domain ID from session or current domain.
   */
  public function getSelectedDomainId() {
    if ($this->negitiating) {
      // Current domain is unknown.
      return '';
    }
    if (!$this->domain) {
      $this->negitiating = TRUE;
      // Initialize current domain.
      parent::negotiateActiveDomain();
      $this->negitiating = FALSE;
    }
    $route = \Drupal::routeMatch()->getRouteObject();
    $isAdminRoute = \Drupal::service('router.admin_context')
      ->isAdminRoute($route);
    if (!$isAdminRoute) {
      // Return selected domain ID on admin paths only.
      return $this->getActiveId();
    }
    if (isset($_SESSION['domain_config_ui']['config_save_domain'])) {
      if (!empty($_SESSION['domain_config_ui']['config_save_domain'])) {
        // Override current domain with session stored one.
        $this->domain = $this->domainLoader->load($_SESSION['domain_config_ui']['config_save_domain']);
      }
      return $_SESSION['domain_config_ui']['config_save_domain'];
    }
    return $this->getActiveId();
  }

}
