<?php
$main_dir = dirname( dirname(__FILE__) );

require $main_dir.'/vendor/autoload.php';
require $main_dir.'/loris-api-wrapper.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

// My local CentOS
$ProjectName = "loris";
$CandID = "230796";
$api_url = 'http://192.168.1.244/api/v0.0.2/';
$username = "loris";
$password = "loris";

$Visit = "T1";

$loris = new LorisApiWrapper($api_url);
$token  = $loris->login($username,$password);
echo "token: $token\n";


/*
//Get all projects in Loris
$projects = $loris->getProjects($token);
var_dump($projects);
*/

/*
//Get project information
$project = $loris->getProject($token,$ProjectName);
var_dump($project);
*/

/*
//Get all candidates in the project
$project = $loris->getProjectCandidates($token,$ProjectName);
var_dump($project);
*/


/*
//Get session leve imaging QC data
$imagingQcInfo = $loris->getSessionImagingQc($token, $CandID, $Visit);
var_dump($imagingQcInfo);
*/


/*
//Get imaging information for a specific candidate for a specific visit
$imagesInfo = $loris->getCandidateImages($token, $CandID, $Visit);
//var_dump($imagesInfo);

$a_file = null;
$Filename = null;
$Files = !empty($imagesInfo->Files) ? $imagesInfo->Files : null;
if($Files){
  // Take the first element of the array for testing
  $a_file = $Files[0];
  $Filename = $a_file->Filename;
}

if($Filename){
  $VisitLabel = $Visit;
  //Get raw image file data for a specific candidate and visit and file
  $imageFile = $loris->getImageData($token, $CandID, $VisitLabel, $Filename);
  echo $imageFile;
}
*/

//Create a new candidate
$candidate = array(
  'Project' => 'loris',
  'DoB' => '2018-05-06',
  'Gender' => 'Female'
);
$data = array('Candidate' => $candidate);
$result = $loris->createCandidate($token, $data);
if($result && $result->Meta && $result->Meta->CandID){
  $CandID = $result->Meta->CandID;
  $Battery="Experimental";
  //Create Candidate Visit data for this new candidate
  //PUT /candidates/$CandID/$VisitLabel
  $result = $loris->createCandidateVisit($token, $CandID, $Visit, $Battery);

  //Get Candidate visit data
  //GET /candidates/$CandID/$VisitLabel
  $result = $loris->getCandidateVisit($token, $CandID, $Visit);
}

if(is_string($result)){
  echo "$result\n";
}else{
  var_dump($result);
}

?>
