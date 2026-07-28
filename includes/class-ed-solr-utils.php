<?php

use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Ed_Solr_Utils
{
	private Client $solr_client;

	public function __construct()
	{
		$this->solr_client = $this->get_solr_client();
	}

	/**
	 * Get solarium client
	 *
	 * @return Solarium\Client
	 */
	private function get_solr_client() {
		$client = new Solarium\Client(
			new Curl(),
			new EventDispatcher(),
			[
				'endpoint' => [
					'localhost' => [
						'host'     => get_site_option( 'solr-host' ),
						'port'     => get_site_option( 'solr-port' ),
						'path'     => get_site_option( 'solr-path' ),
						'core'     => get_site_option( 'solr-core' ),
						'username' => get_site_option( 'solr-username' ),
						'password' => get_site_option( 'solr-password' ),
					],
				],
			]
		);

		$client->getPlugin( 'postbigrequest' );

		return $client;
	}

	public function getUserPostsCount(string $user_id)
	{
		$query = $this->solr_client->createSelect();
		$query->setQuery('author:(' . $user_id . ')');
		$query->setRows(0);

		$resultSet = $this->solr_client->select($query);

		return $resultSet->getNumFound();
	}
}
