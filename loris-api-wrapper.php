<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class LorisApiWrapper {

  /**
  * A client object
  * @var GuzzleHttp\Client
  */
  private $client;

  /**
  * A key value array of options
  * @var Array
  */
  private $options;

  /**
  *
  * Constructor
  *
  * @param string $api_url
  * The url to the Loris api
  * e.g. http://192.168.1.244/api/v0.0.2/
  *
  */
  public function __construct($api_url,$options=NULL){
    $this->client = new Client([
      'base_uri' => $api_url,
      'timeout' => 2.0,
      'options' =>$options
    ]);
  }

  /**
  * A Login onto Loris
  * GET /login
  *
  * @param string $username
  * A Loris username
  *
  * @param string $password
  * The password
  *
  * @return string $token | NULL
  * A JWT (Json Web Token)
  */
  public function login($username, $password){
    $endpoint = "login";

    $data = array(
      'username' => $username,
      'password' => $password
    );

    $result = $this->makePostRequest(NULL, $endpoint, $data);
    if(!empty($result->token) && $result->token){
      return $result->token;
    } else{
      // Problem: Some other problem has occurred. Return exception of some sort
      return $result;
    }
  }

  /**
  * Get list of all projects in Loris
  * GET /projects
  *
  * @param string $token
  * A JWT token
  *
  * @return mixed $result
  * The list of projects as objects created from the decoded json result
  */
  public function getProjects($token){
    $endpoint = "projects";

    $params = array($token);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Get a specific project in Loris
  * GET /projects/$ProjectName
  *
  * @param string $token
  * A JWT token
  *
  * @param string $ProjectName
  * A Loris project name
  *
  * @return mixed $result
  * The project data as an object created from the decoded json result
  */
  public function getProject($token, $ProjectName){
    $endpoint = "projects/$ProjectName";

    $params = array($token,$ProjectName);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Get a list of all candidates in a specific project
  * GET /projects/$ProjectName/candidates/
  *
  * @param string $token
  * A JWT token
  *
  * @param string $ProjectName
  * A Loris project name
  *
  * @return mixed $result
  * The object with a Candidates member array created from the decoded json result
  */
  public function getProjectCandidates($token, $ProjectName=''){
    $endpoint = "projects/$ProjectName/candidates";

    $params = array($token,$ProjectName);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Get a specific candidate
  * GET /candidates/$CandID
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @return mixed $result
  * An object of all the images data information created from the decoded json result
  */
  public function getCandidate($token, $CandID){
    $endpoint = "candidates/$CandID";

    $params = array($token, $CandID);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Get a specific candidate
  * GET /candidates/$CandID
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @return mixed $result
  * An object of all the images data information created from the decoded json result
  */
  public function createCandidate($token, $data){
    $endpoint = "candidates/";

    $params = array($token);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    if(!isset($data['Candidate'],$data['Candidate']['Project'],$data['Candidate']['Gender'],$data['Candidate']['DoB'])){
      echo $data['Project'], $data['Gender'], $data['DoB'];
      return "Error: Invalid parametres. Verify that Project, Gender , DoB are set";
    }

    return $this->makePostRequest($token, $endpoint, $data);
  }

  /**
  * Get a specific candidate visit data
  * GET /candidates/$CandID/$VisitLabel
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @param string $VisitLabel
  * The visit label
  *
  * @return mixed $result
  * An object of all the images data information created from the decoded json result
  */
  public function getCandidateVisit($token, $CandID, $VisitLabel){
    $endpoint = "candidates/$CandID/$VisitLabel";

    $params = array($token, $CandID, $VisitLabel);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Create a Visit Label for a specific candidate
  * PUT /candidates/$CandID/$VisitLabel
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @param string $VisitLabel
  * The visit label
  *
  * @param string $Battery
  * The name of the sub-project
  *
  * @return mixed $result
  * An object of all the images data information created from the decoded json result
  */
  public function createCandidateVisit($token, $CandID, $VisitLabel, $Battery){
    $endpoint = "candidates/$CandID/$VisitLabel";

    $params = array($token, $CandID, $VisitLabel);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    $visit = array("CandID"=>$CandID, "Visit"=>$VisitLabel, "Battery"=>$Battery);
    $meta = array('Meta'=>$visit);

    return $this->makePutRequest($token, $endpoint, $meta);
  }

  /**
  * Get all the images which have been acquired for a specific visit for a specific candidate
  * GET /candidates/$CandID/$Visit/images
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @param string $Visit
  * The visit label
  *
  * @return mixed $result
  * An object of all the images data information created from the decoded json result
  */
  public function getCandidateImages($token, $CandID, $Visit){
    $endpoint = "candidates/$CandID/$Visit/images";

    $params = array($token,$CandID,$Visit);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Return a raw file of an image with the appropriate MimeType headers for each Filename
  * GET /candidates/$CandID/$VisitLabel/images/$Filename
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @param string $VisitLabel
  * The visit label
  *
  * @param string $Filename
  * The Filename to return
  *
  * @return mixed $body
  * A raw file of an image with the appropriate MimeType headers for each Filename
  */
  public function getImageData($token, $CandID, $VisitLabel, $Filename){
    if(($is_valid = $this->isValidParams(array($token))) !== TRUE){
      return $is_valid;
    }

    try{
      $endpoint = "candidates/$CandID/$VisitLabel/images/$Filename";
      $response = $this->client->get($endpoint,['headers'=>['Authorization'=>"Bearer $token"]]);
      return $response->getBody();
    }catch(RequestException $e){
      $error_message = Psr7\str($e->getResponse());
      return $error_message;
    };
  }

  /**
  * Get session level imaging QC data for a visit
  * GET /candidates/$CandID/$Visit/images
  * NOTE: API returns 404 and says requested url not found
  *
  * @param string $token
  * A JWT token
  *
  * @param string $CandID
  * The candidate ID
  *
  * @param string $Visit
  * The visit label
  *
  * @return mixed $result
  * An object of session level imaging QC data created from the decoded json result
  */
  public function getSessionImagingQc($token, $CandID, $Visit){
    $endpoint = "/candidates/$CandID/$Visit/qc/imaging";

    $params = array($token,$CandID,$Visit);
    if(($is_valid = $this->isValidParams($params)) !== TRUE){
      return $is_valid;
    }

    return $this->makeGetRequest($token, $endpoint);
  }

  /**
  * Make a request to the Loris api
  *
  * @param string $token
  * A JWT token
  *
  * @param array $endpoint
  * An api endpoint to send a request to
  *
  * @return mixed $result | Exception or Error
  * $result IFF the request succeeds. An Exception / Error otherwise
  */
  private function makeGetRequest($token, $endpoint){
    $request = "GET";
    return $this->makeRequest($request, $token, $endpoint);

  }

  /**
  * Make a request to the Loris api
  *
  * @param string $token
  * A JWT token
  *
  * @param array $endpoint
  * An api endpoint to send a request to
  *
  * @param array $data
  * The data to send in the post request
  *
  * @return mixed $result | Exception or Error
  * $result IFF the request succeeds. An Exception / Error otherwise
  */
  private function makePostRequest($token, $endpoint, $data){
    $request = "POST";
    return $this->makeRequest($request, $token, $endpoint, $data);

  }

  /**
  * Make a request to the Loris api
  *
  * @param string $token
  * A JWT token
  *
  * @param array $endpoint
  * An api endpoint to send a request to
  *
  * @param array $data
  * The data to send in the post request
  *
  * @return mixed $result | Exception or Error
  * $result IFF the request succeeds. An Exception / Error otherwise
  */
  private function makePutRequest($token, $endpoint, $data){
    $request = "PUT";
    return $this->makeRequest($request, $token, $endpoint, $data);

  }

  /**
  * Make a request to the Loris api
  *
  * @param string $request
  * The HTTP request verb (GET, POST, PUT, PATCH, DELETE)
  *
  * @param string $token
  * A JWT token
  *
  * @param array $endpoint
  * An api endpoint to send a request to
  *
  * @param array $data
  * The data to send in the post request
  *
  * @return mixed $result | Exception or Error
  * $result IFF the request succeeds. An Exception / Error otherwise
  */
  private function makeRequest($request, $token, $endpoint, $data=NULL){
    $options = [];
    if(!empty($data)){
      if($endpoint == "login"){
        //$options = array('json' => $data);
        $options['json'] = $data;
      }else{
        $data = json_encode($data);
        //$options = array('body' => $data);
        $options['body'] = $data;
      }
    }
    echo "request: $request\n";
    var_dump($options);

    try{
      if(!empty($token)){
        $options['headers'] = ['Authorization'=>"Bearer $token"];
      }
      switch ($request) {
        case 'GET':
          $response = $this->client->get($endpoint, $options);
          break;
        case 'POST':
            $response = $this->client->post($endpoint, $options);
            break;
        case 'PUT':
            $response = $this->client->put($endpoint, $options);
            break;
        case 'PATCH':
            $response = $this->client->patch($endpoint, $options);
            break;
        case 'DELETE':
            $response = $this->client->delete($endpoint, $options);
            break;
        default:
          # code...
          break;
      }
      $body = $response->getBody();
      if($body){
        $result = json_decode($body);
        return $result;
      }
    }catch(RequestException $e){
      $response = $e->getResponse();
      if(!empty($response)){
        $error_message = Psr7\str($response);
        return $error_message;
      }
      return $response;
    };
  }

  /**
  * A basic check or the parametres using isset() to see if they are set
  * This does not check if the parametre value is legitimate or not
  *
  * @param array $params
  * An array of parametres to check from the calling function
  *
  * @return mixed TRUE | An Error message
  * TRUE IFF all the parametres are set, otherwise an error message
  */
  public function isValidParams($params){
    foreach($params as $param){
      if(!isset($param)){
        $error_message = "Error: Some or all parametres to the method have invalid values";
        return $error_message;
      }
    }
    return TRUE;
  }
}

?>
