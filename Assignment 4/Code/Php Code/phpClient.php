<style>
    form {
        margin-left: 550px;
        border:1px solid #ddd;
        width:400px;
        display: inline-block;
        margin-top:30px;
        padding-left: 10px;
        padding-right: 10px;
        background-color: #F3F3F3;
        padding-bottom: 24px;
    }

</style>
<?php
require_once('simple_html_dom.php');
error_reporting(-1);

ini_set('auto_detect_line_endings', TRUE);
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$query = strtoLower($query);
$hid = isset($_REQUEST['hid']) ? $_REQUEST['hid'] : false;
$results = false;
$row = 1;
$data1 =array();
ini_set('auto_detect_line_endings', TRUE);


function getLinkfromMap($input) {
    $csv = array_map('str_getcsv', file('map.csv'));
    foreach($csv as $value) {
        if ($value[0] == $input) {
            return $value[1];
        }
    }
}
if ($query)
{
    require_once('solr-php-client/Apache/Solr/Service.php');

// The Apache Solr Client library should be on the include path
// which is usually most easily accomplished by placing in the
// same directory as this script ( . or current directory is a default // php include path entry in the php.ini) require_once('Apache/Solr/Service.php');
// create a new solr service instance - host, port, and corename
// path (all defaults in this example)
    ini_set('memory_limit', '-1');
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/IRWSJNBC/');
    $correct = true;
    if($hid == "no") {

        include 'SpellCorrector.php';
        $queries = explode(" ", $query);
        $spelled = "";

        foreach($queries as $q) {

            $spell = strtoLower(SpellCorrector::correct($q));

            if (strcmp($q, $spell) != 0) {
                $correct = false;
                $spelled = $spelled . ' ' . $spell;
            }else {
                $spelled = $spelled . ' ' . $q;
            }
        }

        $temp_query = $query;
        $query = trim($spelled);

    }
    if ($hid == "yes") {
        $query = trim($query);
    }
// if magic quotes is enabled then stripslashes will be needed

    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query); }
// in production code you'll always want to use a try /catch for any // possible exceptions emitted by searching (i.e. connection
// problems or a query parsing error)
    try
    {
        $algorithm = isset($_GET['algo']) ? $_GET['algo'] : false;
        if ($algorithm == "Default Algorithm") {
            $results = $solr->search($query, 0, $limit);
        }
        else if($algorithm ==="PageRank Algorithm"){
            $results = $solr->search($query, 0, $limit,$arrayName = array('sort' => 'pageRankFile desc'));
        }

    }
    catch (Exception $e) {
// in production you'd probably log or email this error to an admin
// and then show a special message to the user but for this example
// we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?> <html>
<head>
    <title>Solr Search </title>
    <link href="http://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css" rel="stylesheet"></link>
</head> <body >
<form accept-charset="utf-8" method="get" id ="form">
    <label for="q">Search:</label><table>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
        <input id="hid" type="hidden" name="hid" value="no">
        <input type="submit"/></table><table></table>
    <input type="radio" name="algo" value = "PageRank Algorithm" <?php if(!isset($_GET['algo']) || (isset($_GET['algo']) && $_GET['algo'] =="PageRank Algorithm")) echo 'checked="checked"';?>  id="pagerank"> PageRank Algorithm
    <input type="radio" name="algo" value ="Default Algorithm" <?php if(!isset($_GET['algo']) || (isset($_GET['algo']) && $_GET['algo'] =="Default Algorithm")) echo 'checked="checked"';?>  id="default"> Default Algorithm
    </table></form> <?php
// display results
if ($results) {

    $total = (int) $results->response->numFound;
    $start = min(1, $total);
    $end = min($limit, $total);
    if (!$correct) {
        ?>
        <div> Showing results for <?php echo $spelled; ?> </div>
        <br />
        <div>
            Show results for <button onclick ="myfunc('<?php echo $temp_query; ?>');" style ="background:none;outline:none;border:none;font-size:14px;color:blue;border-bottom:1px solid"> <?php echo $temp_query?></button>
        </div>
        <br />
        <?php
    }
    ?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
        <?php
        // iterate result documents
        foreach ($results->response->docs as $doc)
        { ?>
            <?php
// iterate document fields / values
            $id = $doc -> og_url;

            $indexMap = substr($id, strpos($id,"/")+1);
            $title = $doc -> title;
            $fileName = $doc->resourcename;

            $MapFileIndex = substr($fileName, strrpos($fileName, '/') + 1);
            $size = ((int)$doc -> stream_size)/ 1000 ;
            ?>
            <table class ="jsonTable">
                <li>

                    <p><a href = "<?php if($id != '') {echo $id;} else {echo getLinkfromMap($MapFileIndex);}?>"> News Link </a> </br>
                        Title : <?php if($title != ''){echo $title;} else{ echo "N/A";} ?> </br>
                        File Name: <?php echo $fileName;?></br>
                        Link Name : <?php if($id != '') {echo $id;} else { echo getLinkfromMap($MapFileIndex); }?></br>

                        Snippet :<?php
                        $fileContent = file_get_html($fileName, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT)->plaintext;
                        $counter =0;

                        $sentenceArray = explode(".", $fileContent);
                        $flag = "false";
                        $length = count($sentenceArray);

                        for ($i = 0; $i < $length; $i++) {
                            
                            $value = $sentenceArray[$i];

                            
                            if(stripos($value,$query)!== false) {
                                $stopWordArray = array("advertisement", "Advertisement","share","Facebook","Share","Tweet","Google Plus","Facebook"
                                );
                                $stopWordLength = count($stopWordArray);
                                for($i=0; $i <$stopWordLength;$i++){
                                    if (stripos($value,$stopWordArray[$i]) !== false ) {
                                        //echo $stopWordArray[$i];

                                        $value = str_ireplace($stopWordArray[$i]," ",$value);
                                      

                                    }
                                }

                                echo  $value . "...";
                                $flag ="true";
                                break;

                            }
                            


                        }
                        if ( $flag == "false")  {
                            
                                echo "No snippet for given result";
                            
                        }

                        ?>
                        <!-- Result : --><?php /*print_result(); */?>
                    </p>


                </li> </table> <?php
        } ?>
    </ol>
<?php }
?>
<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script src="index.js"></script>
<script src="stemmer.js"></script>
</body> </html>
