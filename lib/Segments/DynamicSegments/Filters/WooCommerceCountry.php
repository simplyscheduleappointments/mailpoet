<?php

namespace MailPoet\Segments\DynamicSegments\Filters;

use MailPoet\Entities\DynamicSegmentFilterEntity;
use MailPoet\Entities\SubscriberEntity;
use MailPoetVendor\Doctrine\DBAL\Query\QueryBuilder;
use MailPoetVendor\Doctrine\ORM\EntityManager;

class WooCommerceCountry implements Filter {
  const ACTION_CUSTOMER_COUNTRY = 'customerInCountry';

  /** @var EntityManager */
  private $entityManager;

  public function __construct(EntityManager $entityManager) {
    $this->entityManager = $entityManager;
  }

  public function apply(QueryBuilder $queryBuilder, DynamicSegmentFilterEntity $filter): QueryBuilder {
    global $wpdb;
    $filterData = $filter->getFilterData();
    $countryCode = (string)$filterData->getParam('country_code');
    $countryFilterParam = 'countryCode' . $filter->getId();
    $collation = $this->getCollate();
    $subscribersTable = $this->entityManager->getClassMetadata(SubscriberEntity::class)->getTableName();
    return $queryBuilder->innerJoin(
      $subscribersTable,
      $wpdb->postmeta,
      'postmeta',
      "postmeta.meta_key = '_billing_email'
        AND $subscribersTable.email=postmeta.meta_value $collation
        AND $subscribersTable.is_woocommerce_user = 1
        AND postmeta.post_id NOT IN ( SELECT id FROM {$wpdb->posts} as p WHERE p.post_status IN ('wc-cancelled', 'wc-failed'))"
    )->innerJoin('postmeta',
      $wpdb->postmeta,
      'postmetaCountry',
      "postmeta.post_id = postmetaCountry.post_id AND postmetaCountry.meta_key = '_billing_country' AND postmetaCountry.meta_value = :$countryFilterParam"
    )->setParameter($countryFilterParam, $countryCode);
  }

  private function getCollate(): string {
    global $wpdb;
    $mailpoetEmailColumn = $wpdb->get_row(
      'SHOW FULL COLUMNS FROM ' . MP_SUBSCRIBERS_TABLE . ' WHERE Field = "email"'
    );
    $mailpoetEmailCollation = $mailpoetEmailColumn->Collation; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
    $wpPostmetaValueColumn = $wpdb->get_row(
      'SHOW FULL COLUMNS FROM ' . $wpdb->postmeta . ' WHERE Field = "meta_value"'
    );
    $wpPostmetaValueCollation = $wpPostmetaValueColumn->Collation; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.NotCamelCaps
    if ($mailpoetEmailCollation !== $wpPostmetaValueCollation) {
      return "COLLATE $mailpoetEmailCollation";
    }
    return '';
  }
}
