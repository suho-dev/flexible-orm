DESTDIR =

TARGETDIR = $(DESTDIR)/usr/share/php/flexible-orm/

TOPFILES  = README.txt 

install-flexible-orm:
	install -d -o www-data -m 755 $(TARGETDIR)/

	svn export --force https://flexible-orm.googlecode.com/svn/tags/0.18-beta/src/ $(TARGETDIR)/
	chown -Rf www-data:www-data $(TARGETDIR)/

	find $(TARGETDIR)/ -type f -exec chmod 644 {} \;
