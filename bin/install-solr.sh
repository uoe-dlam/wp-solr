#!/usr/bin/env bash

SOLR_PORT=${SOLR_PORT:-8983}
SOLR_VERSION=${SOLR_VERSION:-8.0.0}
SOLR_COLLECTION=${SOLR_COLLECTION:-"WordPress"}

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

    ./$dir_name/bin/solr -p $solr_port -c
    echo "Started"
}

create_collection() {
    dir_name=$1
    name=$2
    solr_port=$3
    ./$dir_name/bin/solr create -c $name -shards 1 -replicationFactor 1 -p $solr_port
    echo "Created collection $name"
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

    cp schema.xml $dir_name/example/solr/conf

    run_solr $dir_name $SOLR_PORT
    create_collection $dir_name $SOLR_COLLECTION $SOLR_PORT
}

download_and_run $SOLR_VERSION