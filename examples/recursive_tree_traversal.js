   $.get(tocURL, function(toc) {
    function makeToc($xml) {
        // variable to accumulate markup
        var markup = "";
        // worker function local to makeToc
        function processXml() {
            markup += "<li><a href='" + $(this).attr("url") + "'>" + $(this).attr("title") + "</a>";
            if (this.nodeName == "BOOK") {
                markup += "<ul>";
                // recurse on book children
                $(this).find("page").each(processXml);
                markup += "</ul>";
            }
            markup += "</li>";
        }
        // call worker function on all children
        $xml.children().each(processXml);
        return markup;
    }
    var tocOutput = makeToc($(toc));
    $("#list").html(tocOutput);
});