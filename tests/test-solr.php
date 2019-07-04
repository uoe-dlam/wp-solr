<?php

class SolrTest extends WP_UnitTestCase {

    private $solr_client;

    public function setUp() {
        parent::setUp();

        $this->setSolrConfigValues();
        $this->solr_client = $this->getSolrClient();

        if ( ! $this->pingSolr() ) {
            $this->fail('Cannot connect to Solr');
        }

        reset_phpmailer_instance();
    }

    public function tearDown() {
        $solr_client = $this->getSolrClient();

        $update = $solr_client->createUpdate();

        $update->addDeleteQuery('*:*');
        $update->addCommit();

        $solr_client->update($update);

        reset_phpmailer_instance();

        parent::tearDown();
    }

    private function setSolrConfigValues() {
        add_site_option('solr-host', 'localhost');
        add_site_option('solr-port', 8983);
        add_site_option('solr-path', '/');
        add_site_option('solr-core', 'WordPress');
    }

    private function pingSolr() {
        // create a ping query
        $ping = $this->solr_client->createPing();

        try {
            $this->solr_client->ping($ping);

            return true;
        } catch (Solarium\Exception\HttpException $e) {
            return false;
        }
    }

    private function  getSolrClient() {
        return new Solarium\Client(
            [
                'endpoint' => [
                    'localhost' => [
                        'host' => get_site_option( 'solr-host' ),
                        'port' => get_site_option( 'solr-port' ),
                        'path' => get_site_option( 'solr-path' ),
                        'core' => get_site_option( 'solr-core' ),
                    ],
                ],
            ]
        );
    }

    public function test_index_post() {
        $this->factory->post->create( array( 'post_title' => 'Test Post Title', 'post_content' => 'Test Post Content' ) );

        $query = $this->solr_client->createSelect();

        $query->setQuery('postContent:"Test Post Content"');

        $result_set = $this->solr_client->select($query);

        $this->assertEquals(1, $result_set->getNumFound());
    }

    public function test_index_page() {
        $this->factory->post->create( array( 'post_title' => 'Test Page Title', 'post_content' => 'Test Page Content', 'post_type' => 'page' ) );

        $query = $this->solr_client->createSelect();

        $query->setQuery('postContent:"Test Page Content"');

        $result_set = $this->solr_client->select($query);

        $this->assertEquals(1, $result_set->getNumFound());
    }

    public function test_update_post() {
        $original_post_id = $this->factory->post->create( array ('post_title' => 'Test Post Update Title', 'post_content' => 'Test Post Update Content' ) );

        $query = $this->solr_client->createSelect();

        $query->setQuery('postContent:"Test Post Update Content"');

        $result_set = $this->solr_client->select($query);

        $original_solr_post_id = $result_set->getDocuments()[0]['id'];

        wp_update_post( array( 'ID' => $original_post_id, 'post_content' => 'Content Has Been Entirely Changed' ) );

        $update_query = $this->solr_client->createSelect();

        $update_query->setQuery('postContent:"Content Has Been Entirely Changed"');

        $update_result_set = $this->solr_client->select($update_query);

        $this->assertEquals(1, $update_result_set->getNumFound());
        $this->assertEquals($original_solr_post_id, $update_result_set->getDocuments()[0]['id']);
    }

    public function test_update_page() {
        $original_page_id = $this->factory->post->create( array ('post_title' => 'Test Page Update Title', 'post_content' => 'Test Page Update Content', 'post_type' => 'page' ) );

        $query = $this->solr_client->createSelect();

        $query->setQuery('postContent:"Test Page Update Content"');

        $result_set = $this->solr_client->select($query);

        $original_solr_page_id = $result_set->getDocuments()[0]['id'];

        wp_update_post( array( 'ID' => $original_page_id, 'post_content' => 'Page Content Has Been Entirely Changed' ) );

        $update_query = $this->solr_client->createSelect();

        $update_query->setQuery('postContent:"Page Content Has Been Entirely Changed"');

        $update_result_set = $this->solr_client->select($update_query);

        $this->assertEquals(1, $update_result_set->getNumFound());
        $this->assertEquals($original_solr_page_id, $update_result_set->getDocuments()[0]['id']);
    }

    /**
     * @group multisite
     */
    public function test_index_site_for_multisite() {
        for ($i = 0; $i < 3; $i++) {
            $blogId = $this->factory->blog->create();

            switch_to_blog($blogId);
            $this->factory->post->create_many(3);
        }

        $solrAdmin = new Ed_Solr_Admin('ed-solr', '1.0.0');

        $indexResult = $solrAdmin->index_all_blogs_in_solr();

        $query = $this->solr_client->createSelect();

        $query->setQuery('*:*');

        $resultSet = $this->solr_client->select($query);

        echo 'The result set is ' . print_r($resultSet, true);

        $this->assertEquals(9, $resultSet->getNumFound());
        $this->assertEquals(0, $indexResult);
    }

    /**
     * @group singlesite
     */
    public function test_index_site() {
        $this->factory->post->create_many(10);

        $solrAdmin = new Ed_Solr_Admin('ed-solr', '1.0.0');

        $indexResult = $solrAdmin->index_all_blogs_in_solr();

        $query = $this->solr_client->createSelect();

        $query->setQuery('*:*');

        $resultSet = $this->solr_client->select($query);

        $this->assertEquals(10, $resultSet->getNumFound());
        $this->assertEquals(0, $indexResult);
    }

    public function test_delete_post() {
        $postId = $this->factory->post->create(['post_title' => 'Test Post Title', 'post_content' => 'Test Post Content']);

        wp_delete_post($postId, true);

        $query = $this->solr_client->createSelect();

        $query->setQuery('postTitle:"Test Post Title"');

        $resultSet = $this->solr_client->select($query);

        $this->assertEquals(0, $resultSet->getNumFound());
    }

    public function test_solr_delete_error_sends_email() {
        $postId = $this->factory->post->create(['post_title'=> 'Test Post Title', 'post_content' => 'Test Post Content']);

        update_site_option('solr-host', 'bad-host');

        echo "Deleting the post $postId";
        wp_delete_post($postId, true);

        $mailer = tests_retrieve_phpmailer_instance();
        echo 'The class methods are '. print_r(get_class_methods($mailer), true);
        $email = $mailer->get_sent();

        $this->assertSame('Solr Deletion Error', $email->subject);
        $this->assertSame(get_site_option('solr-email'), $email->to);
        $this->assertSame("Error deleting post ID: $postId", $email->body);
    }
}