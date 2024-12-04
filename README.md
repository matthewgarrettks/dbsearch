# Mysql Database search (and replace) over all tables

Setup: If you want to run dbsearch as an executable, do:

    chmod 700 dbsearch

You could then go to anywhere in your path (like /usr/local/bin) and 
link to it with ln -s.  This will all you to run it from anywhere.

Alternatively you can also run it just using php, eg:

    php search.php -h 127.0.0.1 -u app -p app -n app -s "its" -P 33377

## Arguments

    -h host ip address or domain name
    -u mysql user name
    -p mysql password
    -P mysql port (default 3306)
    -n database name
    -s text or number to search for
    -r text to replace it with (optional)

## Examples:

Search Only:

    ./dbsearch -h 127.0.0.1 -u app -p app -n app -s "its" -P 33377

Search & Replace:

    ./dbsearch -h 127.0.0.1 -u app -p app -n app -s "its" -r "it's" -P 33377

