BolkTagger
==========

This application tries to sort unordered directories of mp3s into Artist/Album/Title format. It does this by creating an acoustic fingerprint and identifying the record with [acoustid](http://www.acoustid.org). The record is then given the right id3 tags and moved in the right folder.

Installation (debian squeeze)
------------
    cd /opt
    git clone git://github.com/thexa4/bolktagger.git
    apt-get install php5-cli php5-curl php5-posix lltag normalize-audio
    apt-get -t testing install libchromaprint-tools
    adduser --system --group music
    echo '40 * * * * * music /opt/bolktagger/cron/mp3tagger' >> /etc/crontab
    mkdir /var/run/bolktagger
    touch /var/log/mp3.log
    chown music:music /var/run/bolktagger /var/log/mp3.log
    cp /opt/bolktagger/settings-sample.php /opt/bolktagger/settings.php

Edit the settings.php file to suit your needs.

Taggers
-------
The application consists of taggers that collect mp3s from certain folders. The mp3s are then tagged and copied to the mp3 pool.

Processors
----------
These scripts collect information about the records (album info, release info, record info etc) and creates relations between records, albums and releases

Collectors
----------
These scripts create symlinks from the pool to visible folders (Artists, Soundtracks, Compilations, etc)
