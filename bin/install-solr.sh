#!/usr/bin/env bash

SOLR_PORT=${SOLR_PORT:-8983}
SOLR_VERSION=${SOLR_VERSION:-8.0.0}
SOLR_COLLECTION=${SOLR_COLLECTION:-"WordPress"}
SOLR_USERNAME=solr
SOLR_PASSWORD=SolrRocks

download() {
    FILE="$2.tgz"

    if [ -f $FILE ]
    then
        echo "File $FILE exists."
        tar -zxf $FILE
    else
        echo "File does not exist. Downloading solr from $1..."
        curl -O $1
        tar -zxf $FILE
    fi
    echo "Downloaded!"
}

run_solr() {
    dir_name=$1
    solr_port=$2

    ./$dir_name/bin/solr -p $solr_port
    echo "Started"
}

create_collection() {
    dir_name=$1
    name=$2
    solr_port=$3
    ./$dir_name/bin/solr create -c $name -p $solr_port
    echo "Created collection $name"
}

create_schema() {
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"postId", "type":"plongs", "multiValued":false, "stored":true}}' http://localhost:8983/solr/WordPress/schema
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"blogId", "type":"plongs", "multiValued":false, "stored":true}}' http://localhost:8983/solr/WordPress/schema
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"easeOnly", "type":"plongs", "multiValued":false, "stored":true}}' http://localhost:8983/solr/WordPress/schema
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"postAuthor", "type":"plongs", "multiValued":false, "stored":true}}' http://localhost:8983/solr/WordPress/schema
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"postDate", "type":"pdates", "multiValued":false, "stored":true}}' http://localhost:8983/solr/WordPress/schema
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"postTitle", "type":"text_en", "multiValued":false, "stored":true, "indexed":true}}' http://localhost:8983/solr/WordPress/schema
   curl  -X POST -H 'Content-type:application/json' --data-binary '{"add-field": {"name":"postContent", "type":"text_en", "multiValued":false, "stored":true, "indexed":true}}' http://localhost:8983/solr/WordPress/schema
}

download_and_run() {
    version=$1

    case $1 in
        8.*)
            url="http://archive.apache.org/dist/lucene/solr/${version}/solr-${version}.tgz"
            dir_name="solr-${version}"
            ;;
        *)
            echo "Sorry $1 is not a support or not a valid version."
            exit 1
    esac

    download $url $dir_name

    run_solr $dir_name $SOLR_PORT
    create_collection $dir_name $SOLR_COLLECTION $SOLR_PORT

    create_schema
}

download_and_run $SOLR_VERSION

