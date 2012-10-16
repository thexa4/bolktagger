BolkTagger
==========

This application tries to sort unordered directories of mp3s into Artist/Album/Title format. It does this by creating an acoustic fingerprint and identifying the record with [acoustid](http://www.acoustid.org). The record is then given the right id3 tags and moved in the right folder.

Installation
------------
 * Make the cron script run regularly. Example (crontab): 40 * * * * jukebox /opt/tagger/cron/mp3tagger
 * Create a folder /var/run/bolktagger/ owned by the service user

Taggers
-------
The application consists of taggers that collect mp3s from certain folders. The mp3s are then tagged and copied to the mp3 pool.

Processors
----------
These scripts collect information about the records (album info, release info, record info etc) and creates relations between records, albums and releases

Collectors
----------
These scripts create symlinks from the pool to visible folders (Artists, Soundtracks, Compilations, etc)
