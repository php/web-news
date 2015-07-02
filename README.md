# PHP.net News Server Web Interface

You may run this project using PHP's [built-in web server][webserver]
for local development.

```
git clone https://git.php.net/repository/web/news.git web-news
cd web-news/
NNTP_HOST=news.php.net php -S localhost:8080 .router.php
```

-----

this is all very ugly. just proof-of-concept, really.

the biggest thing to do would be to do something smart with
mime-encoded messages. but keeping the current property of <b>not</b>
slurping the whole damn message into memory just to do so.

another thing to do would be to support posting. to avoid
completely anonymous posting, this could require confirming the
email address before allowing posts. to do this without actually
having to maintain a database of users, we could send an email
containing md5(md5("email:timestamp").$secret) (where $secret is
some value that is kept secret. duh.) and then let the user "log
in" by supplying their email address and this code, and storing
that in a cookie. depends on a secret for 'security', but like i
said, it avoids having to maintain any sort of state on the server
side. blocking email addresses for posting will be easy enough
if anyone ever abuses the system.

should also probably protect email addresses from harvesters.
then again, anyone who wanted to harvest email addresses could just
crawl the nntp server directly. or they can crawl any of the other
mail archives that don't protect the addresses.

keeping track of a .newsrc-like state for users would be cool,
too. too bad there's no Set::IntSpan for php.

perhaps chasing up the references: chain to display the
thread when displaying an article would be interesting. i
have a feeling that building some sort of index is going
to be desirable at some point. should use jwz's threading
algorithm. http://www.jwz.org/doc/threading.html

ftp://ftp.isi.edu/in-notes/rfc2047.txt explains how to decode encoded
header fields. handling utf-8 and iso-8859-1 should be pretty easy.
could use the gnu recode functions to do this in a general way,
i think.

oh, and this uses direct socket functions instead of the php imap
extension because nntp is a drop-dead-easy protocol, and i'm allergic
to the c-client code.

---
SC.2004.09.03:
Here are the appropriate Rewrite rules for apache:

    RewriteEngine on
    RewriteRule ^/(php.+)/start/([0-9]+) /group.php?group=$1&i=$2 [L]
    RewriteRule ^/(php.+)/([0-9]+)       /article.php?group=$1&article=$2 [L]
    RewriteRule ^/(php[^/]+)(/)?$        /group.php?group=$1 [L]


[webserver]: http://php.net/manual/en/features.commandline.webserver.php
