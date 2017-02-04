/**
 * <Function desc>
 * <param 1>
 * ...
 * <return>
 */
function split(val) {
    return val.split( ' ' );
}

function extractLast(term) {
    var res = split( term ).pop();

    if(res !== '') {
        return res;
    }else {
        return term;
    }
}

function lastWord(str) {
    if(str.indexOf(' ') === -1) {
        return str;
    }

    return str.substring(str.lastIndexOf(' ') + 1, str.length);
}

function dropValue(str) {
    if(str.indexOf(' ') === -1) {
        return '';
    }
    return str.substring(0, str.lastIndexOf(' ')) + ' ';
}

function myfunc(query) {
    document.getElementById('q').value = query;
    document.getElementById('hid').value = "yes";
    document.getElementById('form').submit();
}

function dfunc(query) {
    document.getElementById('q').value = query;
    document.getElementById('form').submit();
}

var stopList = ['a','about','above','after','again','against','all',
    'am','an','and','any','are','arent','as','at','be','because','been',
    'before','being','below','between','both','but','by','cant','cannot',
    'could','couldnt','did','didnt','do','does','doesnt','doing','dont',
    'down','during','each','few','for','from','further','had','hadnt','has',
    'hasnt','have','havent','having','he','hed','hell','hes','her','here',
    'heres','hers','herself','him','himself','his','how','hows','i','id',
    'ill','im','ive','if','in','into','is','isnt','it','its','its','itself',
    'lets','me','more','most','mustnt','my','myself','no','nor','not','of',
    'off','on','once','only','or','other','ought','our','ours','ourselves',
    'out','over','own','same','shant','she','shed','shell','shes','should',
    'shouldnt','so','some','such','than','that','thats','the','their','theirs',
    'them','themselves','then','there','theres','these','they','theyd',
    'theyll','theyre','theyve','this','those','through','to','too','under',
    'until','up','very','was','wasnt','we','wed','well','were','weve','were',
    'werent','what','whats','when','whens','where','wheres','which','while',
    'who','whos','whom','why','whys','with','wont','would','wouldnt','you',
    'youd','youll','youre','youve','your','yours','yourself','yourselves'];

var URL_PREFIX = 'http://localhost:8983/solr/IRWSJNBC/suggest_phrase?spellcheck.q=';
var 	URL_SUFFIX = '&wt=json&indent=true';

$(function() {
    $( '#q' )
    // don't navigate away from the field on tab when selecting an item
        .bind( 'keydown', function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).autocomplete( 'instance' ).menu.active ) {
                event.preventDefault();
            }
        })

        .autocomplete({
            minLength: 0,
            source: function(request, response) {
                var prefix = "";
                if (request.term.split(" ").length   >1) {
                    for (i = 1; i < request.term.split(" ").length; i++) {
                        prefix  = prefix +" "+ request.term.split(" ")[i-1]
                    }
                }
                var URL = URL_PREFIX + extractLast( request.term.toLowerCase() ).trim()  + URL_SUFFIX;
                $.ajax({
                    url: URL,
                    success: function(data) {
                        console.log(data);
                        var unique_result = [];
                        var i = 0;


                        response($.map(data.spellcheck.suggestions[1].suggestion, function(value, key) {

                            if((stopList.indexOf(value.replace(/[^a-zA-Z0-9/./_ ]/g, '')) == -1 && $('#q').val().indexOf(' ') == -1) || ($('#q').val().indexOf(' ') != -1)) {

                                if(unique_result.indexOf(stemmer(value.replace(/[^a-zA-Z/./_ ]/g, ''))) == -1 && lastWord($('#q').val().toLowerCase()).localeCompare(value.replace(/[^a-zA-Z0-9/./_ ]/g, '')) != 0 && i < 10) {
                                    unique_result.push(stemmer(value.replace(/[^a-zA-Z0-9/./_ ]/g, '')));
                                    i++;

                                    return {

                                        label: prefix + " " + value.replace("'s", "")
                                    }
                                }}

                        }));
                    },
                    dataType : 'jsonp',
                    jsonp : 'json.wrf'
                });
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                // add placeholder to get the comma-and-space at the end
                this.value = ui.item.value;
                dfunc(this.value);
                return false;
            }
        })
});
