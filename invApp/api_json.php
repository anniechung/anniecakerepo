<?php
error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging

$default_max_price = "900000000";
$default_min_price = "0";
$default_free_shipping = "true";

if (isset($_GET["target"]) && isset($_GET["searchClause"]))
{
  $clauseAry = explode("|", $_GET["searchClause"]);
  $query = count($clauseAry) > 0 ? $clauseAry[0] : "";
  if ($query == "") exit("Please enter a product");

  $min_price = count($clauseAry) > 1 ? ($clauseAry[1] != ""? $clauseAry[1]:$default_min_price) : $default_min_price;
  $max_price = count($clauseAry) > 2 ? ($clauseAry[2] != ""? $clauseAry[2]:$default_max_price) : $default_max_price;
  $free_shipping = count($clauseAry) > 3 ? $clauseAry[3] : $default_free_shipping;
  switch ($_GET["target"])
    {
      case "ebay":
        break;
      case "craigslist":
        break;
    }
}
//exit($max_price);

// API request variables
$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
$version = '1.0.0';  // API version supported by your application
$appid = 'Anniecer-911e-45d2-b88a-1084d527bf21';  // Replace with your own AppID
$globalid = 'EBAY-US';  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
//$query = 'michio kaku';  // You may want to supply your own query
$safequery = urlencode($query);  // Make the query URL-friendly
$i = '0';  // Initialize the item filter index to 0

// Create a PHP array of the item filters you want to use in your request
$filterarray =
  array(
    array(
    'name' => 'MaxPrice',
    'value' => $max_price,
    'paramName' => 'Currency',
    'paramValue' => 'USD'),
    array(
    'name' => 'MinPrice',
    'value' => $min_price,
    'paramName' => 'Currency',
    'paramValue' => 'USD'),
    array(
    'name' => 'FreeShippingOnly',
    'value' => $free_shipping,
    'paramName' => '',
    'paramValue' => ''),
    array(
    'name' => 'ListingType',
    'value' => array('AuctionWithBIN','FixedPrice','StoreInventory'),
    'paramName' => '',
    'paramValue' => ''),
  );

// Generates an indexed URL snippet from the array of item filters
function buildURLArray ($filterarray) {
  global $urlfilter;
  global $i;
  // Iterate through each filter in the array
  foreach($filterarray as $itemfilter) {
    // Iterate through each key in the filter
    foreach ($itemfilter as $key =>$value) {
      if(is_array($value)) {
        foreach($value as $j => $content) { // Index the key for each value
          $urlfilter .= "&itemFilter($i).$key($j)=$content";
        }
      }
      else {
        if($value != "") {
          $urlfilter .= "&itemFilter($i).$key=$value";
        }
      }
    }
    $i++;
  }
  return "$urlfilter";
} // End of buildURLArray function

// Build the indexed item filter URL snippet
buildURLArray($filterarray);

// Construct the findItemsByKeywords HTTP GET call
$apicall = "$endpoint?";
$apicall .= "OPERATION-NAME=findItemsByKeywords";
$apicall .= "&SERVICE-VERSION=$version";
$apicall .= "&SECURITY-APPNAME=$appid";
$apicall .= "&GLOBAL-ID=$globalid";
$apicall .= "&keywords=$safequery";
$apicall .= "&paginationInput.entriesPerPage=30";
$apicall .= "&sortOrder=PricePlusShippingLowest";
//$apicall .= "&responseencoding=JSON&callback=''";  //return in json format
$apicall .= "$urlfilter";

// Load the call and capture the document returned by eBay API

    $resp = file_get_contents($apicall);
    if($resp){
        $result = simplexml_load_string($resp);       
    }
    
    exit(json_encode($result));
?>