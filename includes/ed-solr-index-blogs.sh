#!/bin/zsh
cd /Library/WebServer/Documents/wordpress/wp-content/plugins/wp-solr/includes
if [ -e run_index_blogs.txt ]
then
	/usr/local/bin/wp index_blogs_cli wp_solr_index_blogs_cli;
	rm run_index_blogs.txt;
fi