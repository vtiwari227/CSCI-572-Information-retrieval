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
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
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
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/NbcWsjIndexing/');
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
</head> <body >
<form accept-charset="utf-8" method="get">
    <label for="q">Search:</label><table>
    <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
    <input type="submit"/></table><table></table>
    <input type="radio" name="algo" value = "PageRank Algorithm" <?php if(!isset($_GET['algo']) || (isset($_GET['algo']) && $_GET['algo'] =="PageRank Algorithm")) echo 'checked="checked"';?>  id="pagerank"> PageRank Algorithm
    <input type="radio" name="algo" value ="Default Algorithm" <?php if(!isset($_GET['algo']) || (isset($_GET['algo']) && $_GET['algo'] =="Default Algorithm")) echo 'checked="checked"';?>  id="default"> Default Algorithm
</table></form> <?php
// display results
if ($results) {

      $total = (int) $results->response->numFound;
    $start = min(1, $total);
    $end = min($limit, $total); ?>
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

                <p><a href = "<?php if($id != '') {echo $id;} else {echo getLinkfromMap($MapFileIndex);}?>"> News Link </a> </br> Title : <?php if($title != ''){echo $title;} else{ echo "N/A";} ?> </br>
                    File Name: <?php echo $fileName;?></br>
                    Link Name : <?php if($id != '') {echo $id;} else { echo getLinkfromMap($MapFileIndex); }?></br>
                    Size : <?php echo $size; ?> KB</br>
                   <!-- Result : --><?php /*print_result(); */?>
                </p>


            </li> </table> <?php
        } ?>
    </ol>
<?php }
?>
</body> </html>
