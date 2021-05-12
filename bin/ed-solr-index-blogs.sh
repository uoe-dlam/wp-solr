#!/bin/bash
if [ -e run_index_blogs.txt ]
then
	/usr/local/bin/wp ../includes/index_blogs_cli wp_solr_index_blogs_cli;
	rm ../includes/run_index_blogs.txt;
fi