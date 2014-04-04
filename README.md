EPUBster
========

EPUBster is a web application to create and edit EPUBs, written in CakePHP. Content formatted  using Markdown is used as input to generate publications using the EPUB 3 file format.

##Prerequisites
This application uses the open-source development framework [CakePHP](http://cakephp.org) and a MySQL database. Application structure (Controllers, Models, Views, etc.) should be placed in the app directory of a fresh copy of Cake.

##Libraries
The application makes heavy use of the following PHP libraries:

- [PHPePub](https://github.com/Grandt/PHPePub), packaged as a Cake plugin called [CakeEPUB](https://github.com/DigitalPublishingToolkit/epubster/tree/master/Plugin/CakeEPUB)
- [Markdown CakePHP](https://github.com/maurymmarques/markdown-cakephp), modified to use endnotes

On the client-side:

- [Epub.js](https://github.com/futurepress/epub.js), an excellent alternative to the fledgling [Readium.js](http://readium.org/projects/readiumjs)
- [markItUp!](http://markitup.jaysalvat.com/)
- [MarkItDown](https://github.com/bambax/markitdown.medusis.com), to convert Rich Text to Markdown