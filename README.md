EPUBster
========

EPUBster is a web application to create and edit EPUBs, written in CakePHP. Content formatted  using Markdown is used as input to generate publications using the EPUB 3 file format.

##Prerequisites
This application uses the open-source development framework [CakePHP](http://cakephp.org) and a MySQL database. Application structure (Controllers, Models, Views, etc.) should be placed in the app directory of a fresh copy of Cake.

Rename `Config/database.php.default` to `Config/database.php` and fill in the necessary credentials. Change the `SITE_URL` in `/Config/bootstrap.php so it matches the path on your webserver.

##Libraries
The application makes heavy use of the following PHP libraries:

- [PHPePub](https://github.com/Grandt/PHPePub), packaged as a Cake plugin called [CakeEPUB](https://github.com/DigitalPublishingToolkit/epubster/tree/master/Plugin/CakeEPUB)
- [Markdown CakePHP](https://github.com/maurymmarques/markdown-cakephp), modified to parse endnotes

On the client-side:

- [Epub.js](https://github.com/futurepress/epub.js), an excellent alternative to the fledgling [Readium.js](http://readium.org/projects/readiumjs)
- [markItUp!](http://markitup.jaysalvat.com/)
- [MarkItDown](https://github.com/bambax/markitdown.medusis.com), to convert Rich Text to Markdown

##Screenshots
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-02-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-02.png)
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-03-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-03.png)
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-04-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-04.png)
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-05-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-05.png)
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-06-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-06.png)
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-07-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-07.png)
[![epubster](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-08-thumb.png)](https://raw.githubusercontent.com/DigitalPublishingToolkit/epubster/master/screens/epubster-08.png)