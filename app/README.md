Sandbox 244

Developer Setup

Clone Repository
Clone the code via Magento cloud.
From your sites directory

mgc get puv7iye5xtn7s millsltd
cd millsltd



Download staging Database
Magento cloud comes with a nice easy way to get the database.  Within the millsltd directory run the following command

mgc db:dump -e staging -f db.sql


-e is the environment -f is the filename
This will prompt you asking which database.  In theory they should all contain the same data. Try to use a slave DB if you can.
MAKE SURE NOT TO ADD TO GIT

Copy media from staging (optional)
Magento Cloud has a comment which essentially runs an rscyn

mgc mount:download --mount pub/media --target=pub/media -e staging



Link Environment file

cd app/etc
ln -s env.php.warden app/etc/env.php



Start Warden

warden env up



Import DB  and secure the domain
only required first time or if removed docker volumes
db.sql being the filename created when downloading DB in steps above.

warden db import < db.sql
warden sign-certificate millsltd.test



Setup Magento
from within warden shell

composer install
bin/magento app:config:import
bin/magento indexer:reindex



