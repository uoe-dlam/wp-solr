# wp-solr  
  
This plugin uses basic auth to secure solr.  
  
At the moment the basic auth username and password is set to:  
  
username: solr  
password: SolrRocks  
  
On production systems, you should change the default username and password. To do this, you will need to update the security.json file in the root directory.  
  
Change  
  

    "credentials":{"solr":"IV0EHq1OnNrj6gvRCwvFwTrZ1+z1oBbnQdiVC3otuq0= Ndd7LKvVBAaZIF0QAVi1ekCfAJXr1GGfLtRUXhgrF8c="},   

  
to  
  

    "credentials":{"<my_username>":"<my_password_hash>"},  

  
And copy the new security file to <solr_home>/server/solr  
  
Or alternatively, you can do this on the command line:  
  
Add new user:  
  
curl --user solr:SolrRocks http://localhost:8983/solr/admin/authentication -H 'Content-type:application/json' -d '{"set-user": {"<new_user>":"<new_password>"}}'  
  
Remove default user:  
  
curl --user solr:SolrRocks http://localhost:8983/solr/admin/authentication -H 'Content-type:application/json' -d  '{"delete-user": ["solr"]}'  
  
You should also update the solr.in.sh file (<solr_home>/bin/solr.in.sh)  
  
Add the following variables to the bottom of the file:  
  
SOLR_AUTH_TYPE="basic"  
SOLR_AUTHENTICATION_OPTS="-Dbasicauth=<my_username>:<my_password>"  
  
Once you have updated security.json and solr.in.sh you should restart your solr server:  
  
<solr_home>/bin/solr stop -p 8983  
<solr_home>/bin/solr -p 8983  
  
  
For more info on securing solr, please visit: [The Solr Website](https://lucene.apache.org/solr/guide/8_0/basic-authentication-plugin.html)