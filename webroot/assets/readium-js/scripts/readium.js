var pathsReadium = {
    jquery: 'jquery-1.9.1',
    underscore: 'underscore-1.4.4',
    backbone: 'backbone-0.9.10',
    Readium: './readium-js/Readium'
};

require.config({
    baseUrl: 'assets/',
    urlArgs: "bust=" + (new Date()).getTime(),
    shim: {
        underscore: {
            exports: '_'
        },
        backbone: {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        }
    },
    paths: pathsReadium
});

// TODO: eliminate this global
ReadiumApp = {};


require(['jquery', 'underscore', 'Readium'],
    function ($, _, Readium,  EventHandling) {

    ReadiumApp.setModuleContainerHeight = function () {
        $("#reader").css({ "height": $(window).height() * 0.85 + "px" });
    };


    ReadiumApp.addTOC = function (tocIframe) {

/*
         $(tocIframe).off("load");

         // On TOC load, add all the link handlers
         if (!ReadiumApp.epub.tocIsNcx()) {

             $(tocIframe).on("load", function () {
                 $(tocIframe).show();
                 ReadiumApp.applyViewerHandlers(ReadiumApp.epubViewer, $(tocIframe)[0].contentDocument);
             });
         }

         var tocUrl = ReadiumApp.epub.getTocURL();

         ReadiumApp.epub.generateTocListDOM(function (navList) {
             if (ReadiumApp.epub.tocIsNcx()) {
                 $(tocIframe).parent().append(navList);
                 $(tocIframe).hide();
                 ReadiumApp.applyViewerHandlers(ReadiumApp.epubViewer, $(tocIframe).parent()[0]);
             } else {
                 if (ReadiumApp.epubFetch.isPackageExploded()) {
                     // With exploded documents, can simply set the TOC IFRAME's src
                     $(tocIframe).attr("src", tocUrl);
                 } else {
                     var tocContentDocument = tocIframe.contentDocument;
                     tocContentDocument.replaceChild(navList.documentElement, tocContentDocument.documentElement);
                     // load event doesn't trigger when replacing on the DOM level - need to trigger it artificially:
                     $(tocIframe).trigger('load');
                 }
             }
         });
*/
    };

    // This function will retrieve a package document and load an EPUB
    ReadiumApp.loadAndRenderEpub = function (packageDocumentURL) {

        var that = this;

        // Clear the viewer, if it has been defined -> to load a new epub
        ReadiumApp.epubViewer = undefined;

        var jsLibDir = 'assets/';

        // Get the HTML element to bind the module reader to
        var elementToBindReaderTo = $("#reader")[0];
        $(elementToBindReaderTo).html("");

        ReadiumApp.readium = new Readium(elementToBindReaderTo, packageDocumentURL, jsLibDir, function (epubViewer) {
            ReadiumApp.epubViewer = epubViewer;
            ReadiumApp.epubViewer.openBook();
            ReadiumApp.addTOC($("#toc-iframe")[0]);
            ReadiumApp.applyToolbarHandlers();
            ReadiumApp.setModuleContainerHeight();
        });
    };

    loadInitialEpub($)
});

function loadInitialEpub($) {

    $(document).ready(function () {
        // Create an object of viewer preferences
        ReadiumApp.viewerPreferences = {
            fontSize: 12,
            syntheticLayout: true,
            tocVisible: true,
            libraryIsVisible: false,
            tocIsVisible: true
        };

        var epubPath = '';
        if (parent.epub !== null && parent.epub !== undefined) {
          var epubPath = parent.epub;
        }
        ReadiumApp.loadAndRenderEpub(epubPath);
    });

    // Note: the epubReadingSystem object may not be ready when directly using the
    // window.onload callback function (from within an (X)HTML5 EPUB3 content document's Javascript code)
    // To address this issue, the recommended code is:
    // -----
    function doSomething() {
        console.log(navigator.epubReadingSystem);
    };
    //
    // // With jQuery:
    // $(document).ready(function () { setTimeout(doSomething, 200); });
    //
    // // With the window "load" event:
    // window.addEventListener("load", function () { setTimeout(doSomething, 200); }, false);
    //
    // // With the modern document "DOMContentLoaded" event:
    document.addEventListener("DOMContentLoaded", function (e) {
        setTimeout(doSomething, 200);
    }, false);
    // -----
}

define(['jquery'], function ($) {
    ReadiumApp.applyToolbarHandlers = function () {

        // Library panel
        $("#toc-btn").off("click");
        $("#library-btn").on("click", function () {
            ReadiumApp.toggleLibraryPanel();
        });

        // TOC
        $("#toc-btn").off("click");
        $("#toc-btn").on("click", function () {
            ReadiumApp.toggleTOCPanel();
        });

        // Decrease font size
        (function () {
            var $decreaseFont = $("#decrease-font-btn");

            $decreaseFont.off("click");
            $decreaseFont.on("click", function () {
                // ReadiumSDK.Views.ReaderView doesn't expose a method to retrieve current settings,
                // so at the moment differential changes to fontSize cannot be implemented:
                //var settings = ReadiumApp.epubViewer.getViewerSettings()
                //ReadiumApp.epubViewer.setFontSize(settings.fontSize - 2);
                console.log('differential changes to fontSize not supported with the ReadiumSDK version used.');
            });
        })();

        // Increase font size
        (function () {
            var $increaseFont = $("#increase-font-btn");

            $increaseFont.off("click");
            $increaseFont.on("click", function () {
                // ReadiumSDK.Views.ReaderView doesn't expose a method to retrieve current settings,
                // so at the moment differential changes to fontSize cannot be implemented:
                //var settings = ReadiumApp.epubViewer.getViewerSettings()
                //ReadiumApp.epubViewer.setFontSize(settings.fontSize + 2);
                console.log('differential changes to fontSize not supported with the ReadiumSDK version used.');

            });
        })();

        // Prev
        // Remove any existing click handlers
        $("#previous-page-btn").off("click");
        $("#previous-page-btn").on("click", function (event) {
            ReadiumApp.epubViewer.openPageLeft();
            event.preventDefault();
        });

        // Next
        // Remove any existing click handlers
        $("#next-page-btn").off("click");
        $("#next-page-btn").on("click", function (event) {
            ReadiumApp.epubViewer.openPageRight();
            event.preventDefault();
        });

        // Layout
        $("#toggle-synthetic-btn").off("click");
        $("#toggle-synthetic-btn").on("click", function (event) {
            ReadiumApp.toggleLayout();
            event.preventDefault();
        });
    };

    ReadiumApp.epubLinkClicked = function (e) {

        var href;
        var splitHref;
        var spineIndex;
        e.preventDefault();

        // Check for both href and xlink:href attribute and get value
        if (e.currentTarget.attributes["xlink:href"]) {
            href = e.currentTarget.attributes["xlink:href"].value;
        }
        else {
            href = e.currentTarget.attributes["href"].value;
        }

        // It's a CFI
        if (href.match("epubcfi")) {

            href = href.trim();
            splitHref = href.split("#");

            // ReadiumApp.epubViewer.showPageByCFI(splitHref[1], function () {
            //     console.log("Showed the page using a CFI");
            // }, this);
        }
        // It's a regular id
        else {

            // Get the hash id if it exists
            href = href.trim();
            splitHref = href.split("#");

            spineIndex = ReadiumApp.epub.getSpineIndexByHref(href);
            if (splitHref[1] === undefined) {
                // ReadiumApp.epubViewer.showSpineItem(spineIndex, function () {
                //     console.log("Spine index shown: " + splitHref[0]);
                // });
            }
            else {
                // ReadiumApp.epubViewer.showPageByElementId(spineIndex, splitHref[1], function () {
                //     console.log("Page shown: href: " + splitHref[0] + " & hash id: " + splitHref[1]);
                // });
            }
        }
    };

    ReadiumApp.tocLinkClicked = function (e) {

        ReadiumApp.epubLinkClicked(e);
    };

    ReadiumApp.toggleLibraryPanel = function () {

        if (ReadiumApp.viewerPreferences.libraryIsVisible) {
            $("#library-panel").hide();
            ReadiumApp.viewerPreferences.libraryIsVisible = false;
        }
        else {
            $("#toc-panel").hide();
            $("#library-panel").show();
            ReadiumApp.viewerPreferences.tocIsVisible = false;
            ReadiumApp.viewerPreferences.libraryIsVisible = true;
        }
    };

    ReadiumApp.toggleTOCPanel = function () {

        if (ReadiumApp.viewerPreferences.tocIsVisible) {
            $("#toc-panel").hide();
            ReadiumApp.viewerPreferences.tocIsVisible = false;
        }
        else {
            $("#library-panel").hide();
            $("#toc-panel").show();
            ReadiumApp.viewerPreferences.tocIsVisible = true;
            ReadiumApp.viewerPreferences.libraryIsVisible = false;
        }
    };

    ReadiumApp.toggleLayout = function () {

        if (ReadiumApp.viewerPreferences.syntheticLayout) {
            ReadiumApp.epubViewer.updateSettings({ "isSyntheticSpread": false });
            ReadiumApp.viewerPreferences.syntheticLayout = false;
        }
        else {
            ReadiumApp.epubViewer.updateSettings({ "isSyntheticSpread": true });
            ReadiumApp.viewerPreferences.syntheticLayout = true;
        }
    };

    return ReadiumApp;
});